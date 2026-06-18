<?php
namespace NFSe;

use NFSe\generico\NFSeGenerico;
use PQD\PQDUtil;

class NFSeJson {


    private $data;
    private $metodo;
    private $oGenerico;


	/**
	 * Cria o normalizador de retorno JSON para XML.
	 *
	 * Foi implementado para centralizar a adaptacao dos retornos REST em JSON
	 * para o XML que NFSeGenericoReturn ja sabe processar.
	 *
	 * @param string $data Retorno bruto recebido da prefeitura em JSON.
	 * @param string $metodo Metodo executado no NFSeGenerico, exemplo: gerarNfse.
	 * @param NFSeGenerico $oGenerico Instancia com as configuracoes do webservice.
	 * @return void
	 */
    public function __construct($data, $metodo, NFSeGenerico $oGenerico){
        $this->data = $data;
        $this->metodo = $metodo;
        $this->oGenerico = $oGenerico;
    }


	/**
	 * Converte o JSON recebido para o XML esperado pelo parser do metodo atual.
	 *
	 * Para gerarNfse, separa retorno de sucesso (nfseXmlGZipB64) de retorno de
	 * erro (lista de mensagens). Para metodos ainda nao especializados, retorna
	 * string vazia para sinalizar que nao ha normalizacao JSON definida.
	 *
	 * @return string XML normalizado para seguir o fluxo de NFSeGenericoReturn.
	 * @throws \Exception Quando o JSON recebido nao e valido.
	 */
	public function toXML() {
        $array = json_decode($this->data, true);

		if (json_last_error() != JSON_ERROR_NONE)
			throw new \Exception("Erro ao converter string JSON para array! String: " . $this->data);

		switch ($this->metodo) {
			case 'gerarNfse':
				return $this->retGerarNfseXML($array);
			break;
		}

		return '';
	}

	/**
	 * Normaliza especificamente o retorno JSON do metodo gerarNfse.
	 *
	 * Se existir a chave configurada em tagMap.return, trata como sucesso e
	 * descompacta o XML da NFS-e. Caso contrario, trata como erro e monta a
	 * estrutura de mensagens configurada em tagMensagensRetorno.
	 *
	 * @param array $array JSON ja decodificado para array associativo.
	 * @return string XML no formato GerarNfseResposta.
	 */
	private function retGerarNfseXML(array $array) {
		if($this->hasReturnPayload($array))
			return $this->retXMLSucessoGZipBase64($array);

		return $this->retXMLMensagens($array, $this->getTagResposta('GerarNfseResposta'));
	}

	/**
	 * Monta o XML de sucesso quando a prefeitura retorna a NFS-e em gzip/base64.
	 *
	 * Usa tagMap.return para achar a chave JSON do payload, remove o cabecalho
	 * XML interno e aplica returnWrap.before/returnWrap.after para encaixar a
	 * NFS-e dentro da estrutura que retListNFSe() espera.
	 *
	 * @param array $array JSON ja decodificado contendo a chave de retorno.
	 * @return string XML com GerarNfseResposta, ListaNfse e CompNfse.
	 * @throws \Exception Quando o conteudo gzip/base64 nao puder ser descompactado.
	 */
	private function retXMLSucessoGZipBase64(array $array) {
		$tagReturn = $this->getReturnTag();
		$xmlNFSe = gzdecode(base64_decode($array[$tagReturn]));

		if($xmlNFSe === false)
			throw new \Exception("Erro ao descompactar retorno JSON gzipbase64 da NFS-e!");

		$xmlNFSe = $this->removeXMLHeader(trim($xmlNFSe));
		$tagResposta = $this->getTagResposta('GerarNfseResposta');

		$aWrap = $this->getMetodoConfig('returnWrap', array());
		$before = PQDUtil::retDefault($aWrap, 'before', '<' . $tagResposta . '><ListaNfse><CompNfse>');
		$after = PQDUtil::retDefault($aWrap, 'after', '</CompNfse></ListaNfse></' . $tagResposta . '>');

		$before = $this->replaceTpl($before, array('{@tagResposta}' => $tagResposta));
		$after = $this->replaceTpl($after, array('{@tagResposta}' => $tagResposta));

		return '<?xml version="1.0" encoding="UTF-8"?>' . $before . $xmlNFSe . $after;
	}

	/**
	 * Monta o XML de mensagens de erro para o metodo informado.
	 *
	 * Usa tagMensagensRetorno.tagListaMensagens e tagMensagensRetorno.tagMensagem
	 * para manter os nomes das tags parametrizaveis por prefeitura/configuracao.
	 * Normaliza campos comuns como Descricao/message para a tag Mensagem.
	 *
	 * @param array $array JSON ja decodificado com dados de erro.
	 * @param string $tagResposta Tag raiz da resposta do metodo.
	 * @return string XML com a lista de mensagens no formato esperado pelo retorno.
	 */
	private function retXMLMensagens(array $array, $tagResposta) {
		$aTags = $this->oGenerico->getConfig('tagMensagensRetorno', array());
		$tagListaMensagem = PQDUtil::retDefault($aTags, 'tagListaMensagens', 'ListaMensagemRetorno');
		$tagMensagem = PQDUtil::retDefault($aTags, 'tagMensagem', 'MensagemRetorno');
		$aErros = $this->retErros($array);

		$xml = '<?xml version="1.0" encoding="UTF-8"?>';
		$xml .= '<' . $tagResposta . '>';
		$xml .= '<' . $tagListaMensagem . '>';

		foreach($aErros as $erro){
			$xml .= '<' . $tagMensagem . '>';
			$xml .= $this->retTagXML('Codigo', $this->retFirstValue($erro, array('Codigo', 'codigo', 'code')));
			$xml .= $this->retTagXML('Mensagem', $this->retFirstValue($erro, array('Mensagem', 'mensagem', 'Descricao', 'descricao', 'message')));
			$xml .= $this->retTagXML('Correcao', $this->retFirstValue($erro, array('Correcao', 'correcao', 'correction')));
			$xml .= $this->retTagXML('IdDPS', $this->retFirstValue($erro, array('IdDPS', 'idDPS', 'idDps'), $this->retFirstValue($array, array('IdDPS', 'idDPS', 'idDps'))));
			$xml .= '</' . $tagMensagem . '>';
		}

		$xml .= '</' . $tagListaMensagem . '>';
		$xml .= '</' . $tagResposta . '>';

		return $xml;
	}

	/**
	 * Localiza a lista de erros dentro do JSON de retorno.
	 *
	 * Foi implementada para evitar regra fixa na chave "erros"; a configuracao
	 * metodos.{metodo}.jsonReturn.errorKeys pode informar outros nomes aceitos.
	 *
	 * @param array $array JSON ja decodificado.
	 * @return array Lista de erros; quando nao acha uma lista, retorna o proprio array como um unico erro.
	 */
	private function retErros(array $array) {
		$aJsonReturn = $this->getMetodoConfig('jsonReturn', array());
		$aErrorKeys = PQDUtil::retDefault($aJsonReturn, 'errorKeys', array('erros', 'errors', 'Erro', 'Mensagens'));

		foreach($aErrorKeys as $key){
			if(isset($array[$key]) && is_array($array[$key]))
				return $this->isListArray($array[$key]) ? $array[$key] : array($array[$key]);
		}

		return array($array);
	}

	/**
	 * Verifica se o JSON possui a chave de retorno de sucesso configurada.
	 *
	 * A chave e lida de metodos.{metodo}.tagMap.return, por exemplo
	 * nfseXmlGZipB64 no padrao nacional REST.
	 *
	 * @param array $array JSON ja decodificado.
	 * @return bool True quando existe payload de sucesso para processar.
	 */
	private function hasReturnPayload(array $array) {
		$tagReturn = $this->getReturnTag();
		return !empty($tagReturn) && isset($array[$tagReturn]) && !empty($array[$tagReturn]);
	}

	/**
	 * Retorna a tag/chave JSON que contem o payload principal do metodo.
	 *
	 * @return string|null Valor configurado em metodos.{metodo}.tagMap.return.
	 */
	private function getReturnTag() {
		$aTagMap = $this->getMetodoConfig('tagMap', array());
		return PQDUtil::retDefault($aTagMap, 'return', null);
	}

	/**
	 * Retorna a tag raiz esperada para a resposta do metodo.
	 *
	 * @param string $default Tag usada quando tagMap.tagResposta nao estiver configurada.
	 * @return string Nome da tag de resposta.
	 */
	private function getTagResposta($default) {
		$aTagMap = $this->getMetodoConfig('tagMap', array());
		return PQDUtil::retDefault($aTagMap, 'tagResposta', $default);
	}

	/**
	 * Busca a configuracao do metodo atual ou uma chave especifica dela.
	 *
	 * Foi implementada para centralizar o acesso a metodos.{metodo} e evitar
	 * repeticao de PQDUtil::retDefault em cada normalizacao.
	 *
	 * @param string|null $key Chave desejada dentro da configuracao do metodo; null retorna tudo.
	 * @param mixed $default Valor retornado quando a chave nao existir.
	 * @return mixed Configuracao completa do metodo ou valor da chave solicitada.
	 */
	private function getMetodoConfig($key = null, $default = null) {
		$aMetodos = $this->oGenerico->getConfig('metodos', array());
		$aMetodo = PQDUtil::retDefault($aMetodos, $this->metodo, array());

		if(is_null($key))
			return $aMetodo;

		return PQDUtil::retDefault($aMetodo, $key, $default);
	}

	/**
	 * Cria uma tag XML simples quando o valor existe.
	 *
	 * Valores nulos ou vazios nao geram tag para evitar tags sem conteudo no
	 * retorno de mensagens.
	 *
	 * @param string $tag Nome da tag XML.
	 * @param mixed $value Valor que sera escapado e inserido na tag.
	 * @return string Tag XML completa ou string vazia.
	 */
	private function retTagXML($tag, $value) {
		if(is_null($value) || $value === '')
			return '';

		return '<' . $tag . '>' . $this->escapeXMLValue($value) . '</' . $tag . '>';
	}

	/**
	 * Retorna o primeiro valor existente entre varias chaves possiveis.
	 *
	 * Permite aceitar variacoes de nomes vindos de prefeituras diferentes, como
	 * Mensagem, Descricao ou message, sem espalhar condicionais pelo codigo.
	 *
	 * @param array $array Origem dos valores.
	 * @param array $keys Chaves testadas em ordem de prioridade.
	 * @param mixed $default Valor retornado se nenhuma chave existir.
	 * @return mixed Primeiro valor encontrado ou o default informado.
	 */
	private function retFirstValue(array $array, array $keys, $default = null) {
		foreach($keys as $key){
			if(isset($array[$key]) && $array[$key] !== '')
				return $array[$key];
		}

		return $default;
	}

	/**
	 * Remove a declaracao XML de um documento que sera embutido em outro XML.
	 *
	 * Isso evita gerar um XML invalido com duas declaracoes quando o XML da NFS-e
	 * descompactado e colocado dentro do wrapper GerarNfseResposta.
	 *
	 * @param string $xml XML que pode conter declaracao no inicio.
	 * @return string XML sem declaracao inicial.
	 */
	private function removeXMLHeader($xml) {
		return preg_replace('/^\s*<\?xml[^>]*\?>\s*/i', '', $xml);
	}

	/**
	 * Aplica substituicoes simples em templates de wrapper.
	 *
	 * Foi implementada para permitir placeholders como {@tagResposta} em
	 * returnWrap.before e returnWrap.after.
	 *
	 * @param string $text Texto/template original.
	 * @param array $replace Mapa no formato busca => substituicao.
	 * @return string Texto com as substituicoes aplicadas.
	 */
	private function replaceTpl($text, array $replace) {
		return str_replace(array_keys($replace), array_values($replace), $text);
	}

	/**
	 * Identifica se um array e uma lista numerica sequencial.
	 *
	 * Diferencia arrays de lista, como erros[0], de arrays associativos, como
	 * Codigo/Descricao, para montar a estrutura XML correta.
	 *
	 * @param array $array Array a ser avaliado.
	 * @return bool True quando as chaves sao 0..n em sequencia.
	 */
	private function isListArray(array $array) {
		return array_keys($array) === range(0, count($array) - 1);
	}

	/**
	 * Escapa um valor para uso seguro como texto de XML.
	 *
	 * Evita que caracteres como &, <, > e aspas quebrem o XML gerado.
	 *
	 * @param mixed $value Valor original.
	 * @return string Valor escapado conforme ENT_XML1 em UTF-8.
	 */
	private function escapeXMLValue($value) {
		return htmlspecialchars((string) $value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
	}
}

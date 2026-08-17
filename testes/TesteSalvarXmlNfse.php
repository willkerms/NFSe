<?php
/**
 * Testa a gravação do nfse-{numero}.xml, com o XML da nota sem envelope, nos dois transportes.
 * Cobre SOAP ABRASF, SOAP Nacional (tag NFSe), rest-json na raiz, rest-json em lote e retorno de erro.
 * Rodar: php -d zend.assertions=1 -d assert.exception=1 testes/TesteSalvarXmlNfse.php
 */
require_once __DIR__ . '/../../../autoload.php';//vendor/autoload.php do projeto que instalou a lib

use NFSe\generico\NFSeGenerico;

class TesteSalvarXmlNfse{

	public static function main(){

		self::testeSoapAbrasf();
		self::testeSoapNacional();
		self::testeRestRaiz();
		self::testeRestLote();
		self::testeRetornoErro();
		self::testeNotaComMensagem();
		self::testeCancelamento();

		echo "OK" . PHP_EOL;
	}

	//saveXML concatena o nome direto no caminho: a barra final é obrigatória
	private static function getDir(){
		return sys_get_temp_dir() . '/nfse-teste-salvar/';
	}

	private static function limpaDir(){

		if(!is_dir(self::getDir()))
			mkdir(self::getDir(), 0777, true);

		foreach(glob(self::getDir() . '*') as $file)
			unlink($file);
	}

	private static function retNFSe($metodoRest = null){

		$aConfig = array(
			'cpfCnpj' => '00000000000000',
			'insMunicipal' => '123456',
			'privKey' => '', 'pubKey' => '', 'certKey' => '',
			'pathSaveXMLs' => self::getDir(),
			'homologacao' => array('wsdl' => 'https://homologacao.teste/nfse?wsdl')
		);

		if(!is_null($metodoRest))
			$aConfig['metodos'] = array($metodoRest => array('typeCommunication' => 'rest-json'));

		return new NFSeGenerico($aConfig);
	}

	//procReturn é privado e é ele quem dispara a gravação: só é alcançável por Reflection
	private static function procReturn(NFSeGenerico $oNFSe, $return, $metodo){

		self::limpaDir();

		$oMetodo = new ReflectionMethod($oNFSe, 'procReturn');
		$oMetodo->setAccessible(true);

		return $oMetodo->invoke($oNFSe, $return, $metodo);
	}

	private static function assertXmlLimpo($xml, $inicio, $msg){

		assert(strpos(ltrim($xml), $inicio) === 0, $msg . ': XML começa com ' . $inicio);
		assert(strpos($xml, 'Envelope') === false, $msg . ': XML sem envelope SOAP');
		assert(strpos($xml, 'Body') === false, $msg . ': XML sem body SOAP');
		assert(substr(ltrim($xml), 0, 1) != '{', $msg . ': XML não é o wrapper JSON');
	}

	private static function envelopeGerarNfse($compNfse){

		return '<?xml version="1.0" encoding="UTF-8"?>'
			. '<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/"><soap:Body>'
			. '<gerarNfseResponse><GerarNfseResposta><ListaNfse>' . $compNfse . '</ListaNfse></GerarNfseResposta></gerarNfseResponse>'
			. '</soap:Body></soap:Envelope>';
	}

	private static function compNfseAbrasf(){

		return '<CompNfse><Nfse versao="2.03"><InfNfse Id="NFSE21360">'
			. '<Numero>21360</Numero><CodigoVerificacao>ABC123</CodigoVerificacao><DataEmissao>2026-08-17T10:00:00</DataEmissao>'
			. '<ValoresNfse><BaseCalculo>100.00</BaseCalculo><Aliquota>2.00</Aliquota><ValorIss>2.00</ValorIss><ValorLiquidoNfse>98.00</ValorLiquidoNfse></ValoresNfse>'
			. '<DeclaracaoPrestacaoServico><InfDeclaracaoPrestacaoServico>'
			. '<Rps><IdentificacaoRps><Numero>1</Numero><Serie>A</Serie><Tipo>1</Tipo></IdentificacaoRps><DataEmissao>2026-08-17</DataEmissao></Rps>'
			. '<Servico><Valores><ValorServicos>100.00</ValorServicos></Valores><ItemListaServico>17.02</ItemListaServico><Discriminacao>Servico de teste</Discriminacao><CodigoMunicipio>3550308</CodigoMunicipio></Servico>'
			. '<Prestador><Cnpj>00000000000000</Cnpj><InscricaoMunicipal>123456</InscricaoMunicipal></Prestador>'
			. '<Tomador><IdentificacaoTomador><Cnpj>11111111111111</Cnpj></IdentificacaoTomador><RazaoSocial>Tomador Teste</RazaoSocial>'
			. '<Endereco><Logradouro>Rua X</Logradouro><Numero>10</Numero></Endereco><Contato><Email>tomador@teste.com</Email></Contato></Tomador>'
			. '</InfDeclaracaoPrestacaoServico></DeclaracaoPrestacaoServico>'
			. '</InfNfse></Nfse></CompNfse>';
	}

	//Padrão Nacional: a tag da nota é NFSe (maiúscula) e o número vem do nNFSe
	private static function nfseNacional(){

		return '<NFSe versao="1.00"><infNFSe Id="NFS3550308000000000000001">'
			. '<xLocEmi>Sao Paulo</xLocEmi><xLocPrestacao>Sao Paulo</xLocPrestacao>'
			. '<nNFSe>777</nNFSe><cLocIncid>3550308</cLocIncid><dhProc>2026-08-17T10:00:00-03:00</dhProc><nDFSe>555</nDFSe>'
			. '<emit><CNPJ>00000000000000</CNPJ><IM>123456</IM><xNome>Prestador Teste</xNome>'
			. '<enderNac><xLgr>Rua X</xLgr><nro>10</nro><cMun>3550308</cMun><CEP>01001000</CEP></enderNac></emit>'
			. '<valores><vBC>100.00</vBC><pAliqAplic>2.00</pAliqAplic><vISSQN>2.00</vISSQN><vLiq>98.00</vLiq></valores>'
			. '</infNFSe></NFSe>';
	}

	private static function testeSoapAbrasf(){

		$aReturn = self::procReturn(self::retNFSe(), self::envelopeGerarNfse(self::compNfseAbrasf()), 'gerarNfse');

		assert($aReturn['Nfse']['InfNfse']['Numero'] == '21360', 'SOAP ABRASF: Numero da nota');
		self::assertXmlLimpo($aReturn['Nfse']['Xml'], '<Nfse', 'SOAP ABRASF');

		assert(file_exists(self::getDir() . 'nfse-21360.xml'), 'SOAP ABRASF: nfse-21360.xml gravado');
		self::assertXmlLimpo(file_get_contents(self::getDir() . 'nfse-21360.xml'), '<Nfse', 'SOAP ABRASF (arquivo)');
	}

	private static function testeSoapNacional(){

		$aReturn = self::procReturn(self::retNFSe(), self::envelopeGerarNfse('<CompNfse>' . self::nfseNacional() . '</CompNfse>'), 'gerarNfse');

		assert($aReturn['Nfse']['InfNfse']['Numero'] == '777', 'SOAP Nacional: Numero vem do nNFSe');
		self::assertXmlLimpo($aReturn['Nfse']['Xml'], '<NFSe', 'SOAP Nacional');

		assert(file_exists(self::getDir() . 'nfse-777.xml'), 'SOAP Nacional: nfse-777.xml gravado');
		self::assertXmlLimpo(file_get_contents(self::getDir() . 'nfse-777.xml'), '<NFSe', 'SOAP Nacional (arquivo)');
	}

	private static function testeRestRaiz(){

		$json = json_encode(array(
			'tipoAmbiente' => 2,
			'nfseXmlGZipB64' => base64_encode(gzencode(self::nfseNacional()))
		));

		self::assertRest($json, 'rest-json raiz');
	}

	private static function testeRestLote(){

		$json = json_encode(array(
			'processado' => true,
			'lote' => array(array(
				'statusProcessamento' => 'SUCESSO',
				'xmlGZipB64' => base64_encode(gzencode(self::nfseNacional()))
			))
		));

		self::assertRest($json, 'rest-json lote');
	}

	private static function assertRest($json, $msg){

		$aReturn = self::procReturn(self::retNFSe('gerarNfse'), $json, 'gerarNfse');

		assert($aReturn['Nfse']['InfNfse']['Numero'] == '777', $msg . ': Numero vem do nNFSe');
		self::assertXmlLimpo($aReturn['Nfse']['Xml'], '<NFSe', $msg);

		assert(file_exists(self::getDir() . 'nfse-777.xml'), $msg . ': nfse-777.xml gravado');
		self::assertXmlLimpo(file_get_contents(self::getDir() . 'nfse-777.xml'), '<NFSe', $msg . ' (arquivo)');
	}

	//Sem numero ou sem XML no retorno nada e gravado: a operacao da nota nao pode cair por I/O
	private static function testeRetornoErro(){

		$json = json_encode(array('erros' => array(array('codigo' => 'E01', 'descricao' => 'falhou'))));

		$aReturn = self::procReturn(self::retNFSe('gerarNfse'), $json, 'gerarNfse');

		assert(count($aReturn['ListaMensagemRetorno']) == 1, 'Erro rest-json: 1 mensagem de retorno');
		assert(count(glob(self::getDir() . 'nfse-*.xml')) == 0, 'Erro rest-json: nenhum nfse-*.xml gravado');

		$fault = '<?xml version="1.0" encoding="UTF-8"?>'
			. '<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/"><soap:Body>'
			. '<soap:Fault><faultcode>soap:Server</faultcode><faultstring>Erro interno</faultstring></soap:Fault>'
			. '</soap:Body></soap:Envelope>';

		$aReturn = self::procReturn(self::retNFSe(), $fault, 'gerarNfse');

		assert(count($aReturn['ListaMensagemRetorno']) == 1, 'Erro SOAP: 1 mensagem de retorno');
		assert(count(glob(self::getDir() . 'nfse-*.xml')) == 0, 'Erro SOAP: nenhum nfse-*.xml gravado');
	}

	//Prefeitura que devolve a nota E uma mensagem (Goiânia manda L000 no sucesso): a nota veio, então grava
	private static function testeNotaComMensagem(){

		$resposta = '<ListaMensagemRetorno><MensagemRetorno><Codigo>L000</Codigo><Mensagem>NORMAL</Mensagem></MensagemRetorno></ListaMensagemRetorno>'
			. '<ListaNfse>' . self::compNfseAbrasf() . '</ListaNfse>';

		$envelope = '<?xml version="1.0" encoding="UTF-8"?>'
			. '<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/"><soap:Body>'
			. '<gerarNfseResponse><GerarNfseResposta>' . $resposta . '</GerarNfseResposta></gerarNfseResponse>'
			. '</soap:Body></soap:Envelope>';

		$aReturn = self::procReturn(self::retNFSe(), $envelope, 'gerarNfse');

		assert($aReturn['Nfse']['InfNfse']['Numero'] == '21360', 'Nota com mensagem: o retorno traz a nota');
		assert(count($aReturn['ListaMensagemRetorno']) == 1, 'Nota com mensagem: 1 mensagem de retorno');
		assert(file_exists(self::getDir() . 'nfse-21360.xml'), 'Nota com mensagem: a nota veio, então grava mesmo assim');
	}

	//O cancelamento devolve o XML do EVENTO na chave Xml, e nao o da nota: nao pode gerar arquivo
	private static function testeCancelamento(){

		$evento = '<evento versao="1.01"><infEvento Id="EVT1"><nDFSe>555</nDFSe><dhProc>2026-08-17T10:00:00-03:00</dhProc></infEvento></evento>';
		$json = json_encode(array('eventoXmlGZipB64' => base64_encode(gzencode($evento))));

		$aReturn = self::procReturn(self::retNFSe('cancelarNFSeEnvio'), $json, 'cancelarNFSeEnvio');

		assert(count($aReturn['ListaMensagemRetorno']) == 0, 'Cancelamento: cancelado sem mensagens');
		assert(strpos($aReturn['Xml'], '<evento') !== false, 'Cancelamento: a chave Xml traz o evento decodificado');
		assert(count(glob(self::getDir() . 'nfse-*.xml')) == 0, 'Cancelamento: nenhum nfse-*.xml gravado');
	}
}

TesteSalvarXmlNfse::main();

<?php

namespace NFSe\generico;

use NFSe\NFSe;
use NFSe\NFSeDocument;
use PQD\PQDUtil;

class NFSeGenerico extends NFSe {

    const XMLNS_URI = "http://www.w3.org/2000/xmlns/";
 
    private $isHomologacao = true;
    private $aConfig;
    
    public function __construct(array $aConfig, $isHomologacao = true) {

        if (isset($aConfig['pfx']) && isset($aConfig['pwdPFX']))
            $this->createTempFiles($aConfig['pfx'], $aConfig['pwdPFX'], $aConfig['cnpj'], $aConfig);

		parent::__construct($aConfig['privKey'], $aConfig['pubKey'], $aConfig['certKey']);
		
		$aConfig = PQDUtil::setDefault($aConfig, array(
			/*
			'proxy' => array(
				'ip' => '::1',
				'port' => '80',
				'user' => 'user',
				'pass' => 'pass'
			),
			'curl' => array(
				'header' => array(
					'Content-Type: text/xml'
				), 
				'port' => 80
				//'port' => 443 //Quando porta 443 envia faz autenticação SSL na conexão
			),
			*/
			'cpfCnpj' => '',
			'insMunicipal' => '',
			'autenticacao' => array(
				'type' => 'none',
				//'type' => 'xml',
				//'type' => 'soap',

				'tagUsuario' => 'username',
				'tagPassword' => 'password',
				'tagChavePrivada' => 'chavePrivada'
			),
			'homologacao' => array(
				'url' => '',

				'usuario' => '',
				'senha' => '',
				'chavePrivada' => '',
			),

			'producao' => array(
				'url' => '',

				'usuario' => '',
				'senha' => '',
				'chavePrivada' => ''
			),
			'pathSaveXMLs' => realpath(dirname(__FILE__) . '/../xml') . '/',
			'templates' => array(
				'path' => realpath(dirname(__FILE__) . '/../templates/') . '/',
				'folder' => 'abrasf-v2.4',
				'rps' => 'Rps.xml',
				'deducao' => 'Deducao.xml',
				'gerarNfse' => 'GerarNfseEnvio.xml',
				'consultarNfseRps' => 'ConsultarNfseRpsEnvio.xml',
				'cancelarNfse' => 'CancelarNfseEnvio.xml',
				'soap' => 'Soap.xml'
			),

			'metodos' => array(
				'gerarNfse' => array(
					//'typeCommunication' => 'soap',
					//'typeCommunication' => 'curl',
					'action' => 'gerarNfse',
					//'returnType' => 'string',
					'nameSpace' => '',
					'tagSign' => 'InfDeclaracaoPrestacaoServico', 
					'tagAppend' => 'Rps',
					'tagMapReturn' => array(
						'return' => 'GerarNfseResposta'
					)
				),
				'consultarNFSePorRps' => array(
					'action' => 'consultarNfseRps',
					'nameSpace' => ''
				),
				'cancelarNfse' => array(
					'action' => 'cancelarNfse',
					'nameSpace' => '',
					'tagSign' => 'InfPedidoCancelamento', 
					'tagAppend' => 'Pedido'
				)
			)
		));

		if($isHomologacao && empty($aConfig['homologacao']['url']))
			throw new \Exception("URL do WebService de homologação não configurado!");

		if(!$isHomologacao && empty($aConfig['producao']['url']))
			throw new \Exception("URL do WebService de produção não configurado!");
        
        $this->aConfig = $aConfig;
		$this->isHomologacao = $isHomologacao;
    }

    public function cancelarNfse(NFSeGenericoCancelarNfseEnvio $oCancelar) {
		$fileName = $oCancelar->Numero . ".xml";
		$metodo = 'cancelarNfse';
		$cpfCnpj = is_null($oCancelar->CpfCnpj) ? : $this->aConfig['cpfCnpj'];
		//Gerar NFSe
		$tpl = $this->getTemplate($metodo);

		$aReplaces = $this->retReplaceUsuarios('xml');
		$aReplaces['replace']['{@Numero}'] = $oCancelar->Numero;
		$aReplaces['replace']['{@CodigoVerificacao}'] = $oCancelar->CodigoVerificacao;
		$aReplaces['replace']['{@CodigoMunicipio}'] = $oCancelar->CodigoMunicipio;
		$aReplaces['replace']['{@CodigoCancelamento}'] = $oCancelar->CodigoCancelamento;

		$aReplaces['replace']['{@CpfPrestador}'] = $cpfCnpj;
		$aReplaces['replace']['{@CnpjPrestador}'] = $cpfCnpj;
		$aReplaces['replace']['{@InscricaoMunicipal}'] = is_null($oCancelar->InscricaoMunicipal) ? : $this->aConfig['insMunicipal'];

		$aReplaces['ifs'][] = array('begin' => '{@ifCpfPrestador}', 'end' => '{@endifCpfPrestador}', 'bool' => strlen($cpfCnpj) == 11);
		$aReplaces['ifs'][] = array('begin' => '{@ifCnpjPrestador}', 'end' => '{@endifCnpjPrestador}', 'bool' => strlen($cpfCnpj) == 14);

		$xml = $this->retXML(PQDUtil::procTplText($tpl, $aReplaces['replace'], $aReplaces['ifs']));
		$xml = $this->signXML($xml, 
			$this->aConfig['metodos'][$metodo]['tagSign'], 
			$this->aConfig['metodos'][$metodo]['tagAppend'], 
			$this->aConfig['metodos'][$metodo]['nameSpace']
		);

		$this->saveXML($xml, $metodo . '-' . $fileName);

		return NFSeGenericoReturn::getReturn($this->makeSOAPRequest($metodo, $xml, $fileName), $metodo, $this->aConfig);
    }
	
	/**
	 * Consulta uma NFSe por RPS
	 *
	 * @param NFSeGenericoIdentificacaoRps $oIdentificacaoRps
	 * @return string
	 */
	public function consultarNFSePorRps(NFSeGenericoIdentificacaoRps $oIdentificacaoRps){

		$fileName = $oIdentificacaoRps->Numero . "-" . $oIdentificacaoRps->Serie . ".xml";
		$metodo = 'consultarNFSePorRps';

		//Gerar NFSe
		$tpl = $this->getTemplate($metodo);

		$aReplaces = $this->retReplaceUsuarios('xml');
		$aReplaces['replace']['{@Numero}'] = $oIdentificacaoRps->Numero;
		$aReplaces['replace']['{@Serie}'] = $oIdentificacaoRps->Serie;
		$aReplaces['replace']['{@Tipo}'] = $oIdentificacaoRps->Tipo;

		$aReplaces['replace']['{@CpfPrestador}'] = $this->aConfig['cpfCnpj'];
		$aReplaces['replace']['{@CnpjPrestador}'] = $this->aConfig['cpfCnpj'];
		$aReplaces['replace']['{@InscricaoMunicipal}'] = $this->aConfig['insMunicipal'];

		$aReplaces['ifs'][] = array('begin' => '{@ifCpfPrestador}', 'end' => '{@endifCpfPrestador}', 'bool' => strlen($this->aConfig['cpfCnpj']) == 11);
		$aReplaces['ifs'][] = array('begin' => '{@ifCnpjPrestador}', 'end' => '{@endifCnpjPrestador}', 'bool' => strlen($this->aConfig['cpfCnpj']) == 14);

		$xml = $this->retXML(PQDUtil::procTplText($tpl, $aReplaces['replace'], $aReplaces['ifs']));

		$this->saveXML($xml, $metodo . '-' . $fileName);

		return NFSeGenericoReturn::getReturn($this->makeSOAPRequest($metodo, $xml, $fileName), $metodo, $this->aConfig);
	}

	/**
	 * Gera uma NFSe a partir do end point 'gerarNfse' do servidor comunicado
	 * 
	 * @param NFSeGenericoInfRps $oRps
	 * @param string $id
	 * 
	 */
	public function gerarNfse(NFSeGenericoInfRps $oRps) {
		
		$fileName = $oRps->IdentificacaoRps->Numero . "-" . $oRps->IdentificacaoRps->Serie . ".xml";
		$metodo = 'gerarNfse';

		//RPS
		$xml = $this->retXMLRps($oRps);
		$xml = $this->signXML($xml, 
			$this->aConfig['metodos'][$metodo]['tagSign'], 
			$this->aConfig['metodos'][$metodo]['tagAppend'], 
			$this->aConfig['metodos'][$metodo]['nameSpace'],
			true
		);

		if($this->isHomologacao){
			$this->saveXML($xml, $metodo . '-rps-' . $fileName);
		}

		//Gerar NFSe
		$tpl = $this->getTemplate($metodo);

		$aReplaces = $this->retReplaceUsuarios('xml');
		$aReplaces['replace']['{@Rps}'] = $xml;

		$xml = $this->retXML(PQDUtil::procTplText($tpl, $aReplaces['replace'], $aReplaces['ifs']));
		$this->saveXML($xml, $metodo . '-' . $fileName);

		return NFSeGenericoReturn::getReturn($this->makeSOAPRequest($metodo, $xml, $fileName), $metodo, $this->aConfig);
	}

	/**
	 * Salva o XML em um arquivo, caso tenha sido configurado o caminho para ser salvo 'pathSaveXMLs'
	 * 
	 * @param string $xml
	 * @param string $name
	 * 
	 */
	private function saveXML($xml, $name){

		if(isset($this->aConfig['pathSaveXMLs']) && is_dir($this->aConfig['pathSaveXMLs']))
			file_put_contents($this->aConfig['pathSaveXMLs'] . $name, $xml);
	}

	/**
	 * Retorna a string de um template
	 * 
	 * @param string $template
	 * 
	 * @return string
	 */
	private function getTemplate($template){
		if(is_file($this->aConfig['templates']['path'] . $this->aConfig['templates']['folder'] . '/' . $this->aConfig['templates'][$template]))
			return file_get_contents($this->aConfig['templates']['path'] . $this->aConfig['templates']['folder'] . '/' . $this->aConfig['templates'][$template]);
		
		throw new \Exception("Template: " . $template . " não encontrado!");
	}

	/**
	 * Faz uma requisição SOAP
	 * 
	 * @return string
	 */
	private function makeSOAPRequest($metodo, $xml, $fileName){

		$url = $this->isHomologacao ? $this->aConfig['homologacao']['url'] : $this->aConfig['producao']['url'];
		$action = $this->aConfig['metodos'][$metodo]['action'];

		$xml = $this->retXMLSoap($xml, $action);
		if($this->isHomologacao)
			$this->saveXML($xml, $metodo . '-soap-' . $fileName);
		
		$soapReturn = $this->sendRequest($url, $xml, $metodo);
		if($this->isHomologacao)
			$this->saveXML($soapReturn, $metodo . '-soap-return-' . $fileName);
		
		return $soapReturn;
	}

	/**
	 * Envia uma requisição de acordo com o metodo e tipo de comunicação no metodo
	 * 
	 * @param string $url
	 * @param string $xml
	 * @param string $metodo
	 * 
	 * @return string
	 */
	private function sendRequest($url, $xml, $metodo){
		$action = $this->aConfig['metodos'][$metodo]['action'];

		$typeCommunication = PQDUtil::retDefault($this->aConfig['metodos'][$metodo], 'typeCommunication', 'soap');
		switch($typeCommunication){
			case 'curl':
				$curl = PQDUtil::retDefault($this->aConfig, 'curl', array());
				return $this->curl(
					$url, 
					$xml, 
					PQDUtil::retDefault($curl, 'header', null), 
					PQDUtil::retDefault($curl, 'port', 80), 
					PQDUtil::retDefault($this->aConfig, 'proxy', null)
				);
			break;
			case 'soap':
				return $this->soap($url, $url, $action, $xml);
			break;
		}
	}

	/**
	 * Retorna um array com os replaces de usuário, senha e chave privada
	 * 
	 * Caso o tipo de autenticação configurado ($this->aConfig) seja igual a váriavel $typeAutentication retorna os array preenchidos
	 * Caso a tipo de autenticação configurado seja diferente da váriavel retorna array vazio.
	 * 
	 * @param string $typeAutentication
	 * 
	 * @return array
	 */
	private function retReplaceUsuarios($typeAutentication){

		if($this->aConfig['autenticacao']['type'] == $typeAutentication){

			if($this->isHomologacao){
				$usuario = $this->aConfig['homologacao']['usuario'];
				$senha = $this->aConfig['homologacao']['senha'];
				$chavePrivada = $this->aConfig['homologacao']['chavePrivada'];
			}
			else{
				$usuario = $this->aConfig['producao']['usuario'];
				$senha = $this->aConfig['producao']['senha'];
				$chavePrivada = $this->aConfig['producao']['chavePrivada'];
			}
	
			$aReplace = array(
				'{@' . $this->aConfig['autenticacao']['tagUsuario'] . '}' => $usuario,
				'{@' . $this->aConfig['autenticacao']['tagPassword'] . '}' => $senha,
				'{@' . $this->aConfig['autenticacao']['tagChavePrivada'] . '}' => $chavePrivada
			);
	
			$aIfs = array(
				array(
					'begin' => '{@if' . ucwords($this->aConfig['autenticacao']['tagUsuario']) . '}',
					'end' => '{@endif' . ucwords($this->aConfig['autenticacao']['tagUsuario']) . '}', 
					'bool' => !empty($usuario)
				),
				array(
					'begin' => '{@if' . ucwords($this->aConfig['autenticacao']['tagPassword']) . '}',
					'end' => '{@endif' . ucwords($this->aConfig['autenticacao']['tagPassword']) . '}', 
					'bool' => !empty($senha)
				),
				array(
					'begin' => '{@if' . ucwords($this->aConfig['autenticacao']['tagChavePrivada']) . '}',
					'end' => '{@endif' . ucwords($this->aConfig['autenticacao']['tagChavePrivada']) . '}', 
					'bool' => !empty($chavePrivada)
				)
			);
	
			return array('replace' => $aReplace, 'ifs' => $aIfs);
		}

		return array( 'replace' => array(), 'ifs' => array() );
	}

	/**
	 * Remove os comentários de um documento
	 * 
	 * @param \DOMDocument $oDocument
	 * 
	 * @return void
	 */
	private function removeComments(\DOMDocument $oDocument){
		$xpath = new \DOMXPath($oDocument);
		foreach ($xpath->query('//comment()') as $comment)
			$comment->parentNode->removeChild($comment);
	}

	/**
	 * Retorna o XML sem espaços em branco, sem tags em branco e sem comentários
	 * 
	 * Caso o parâmetro $firstChild true retorna sem '<?xml version="1.0" encoding="utf-8"?>'
	 * Caso o parâmetro $firstChild false retorna com '<?xml version="1.0" encoding="utf-8"?>'
	 * 
	 * @param string $xml 
	 * @param bool $xml 
	 * 
	 * @return string
	 */
	private function retXML($xml, $firstChild = true){

		$document = new NFSeDocument();
		$document->loadXML($xml, LIBXML_NOBLANKS);

		$this->removeComments($document);

		return trim($document->saveXML($firstChild ? $document->firstChild : null, LIBXML_NOEMPTYTAG));
	}

	/**
	 * Retorna o XML do RPS de acordo com o template 'rps'
	 * 
	 * @param NFSeGenericoInfRps $oRps
	 * @param string $id
	 * 
	 * @return string
	 */
	private function retXMLRps(NFSeGenericoInfRps $oRps){

		PQDUtil::recursive($oRps, function($data){
			return htmlspecialchars($data);
		});

		$tplRps = $this->getTemplate('rps');;
		$tplDeducao = $this->getTemplate('deducao');

		$deducoes = '';
		foreach($oRps->aDeducoes as $deducao){
			/**
			 * @var NFSeGenericoDeducao $deducao
			 */
			$aReplace = array(
				'{@TipoDeducao}' => $deducao->TipoDeducao,
				'{@DescricaoDeducao}' => $deducao->DescricaoDeducao,
				'{@CodigoMunicipioGerador}' => $deducao->IdentificacaoDocumentoDeducao->CodigoMunicipioGerador,
				'{@NumeroNfse}' => $deducao->IdentificacaoDocumentoDeducao->NumeroNfse,
				'{@CodigoVerificacao}' => $deducao->IdentificacaoDocumentoDeducao->CodigoVerificacao,
				'{@NumeroNfe}' => $deducao->IdentificacaoDocumentoDeducao->NumeroNfe,
				'{@UfNfe}' => $deducao->IdentificacaoDocumentoDeducao->UfNfe,
				'{@ChaveAcessoNfe}' => $deducao->IdentificacaoDocumentoDeducao->ChaveAcessoNfe,
				'{@IdentificacaoDocumento}' => $deducao->IdentificacaoDocumentoDeducao->IdentificacaoDocumento,
				'{@Cpf}' => $deducao->DadosFornecedor->CpfCnpj,
				'{@Cnpj}' => $deducao->DadosFornecedor->CpfCnpj,
				'{@NifFornecedor}' => $deducao->DadosFornecedor->NifFornecedor,
				'{@CodigoPais}' => $deducao->DadosFornecedor->CodigoPais,
				'{@DataEmissao}' => $deducao->DataEmissao,
				'{@ValorDedutivel}' => $deducao->ValorDedutivel,
				'{@ValorUtilizadoDeducao}' => $deducao->ValorUtilizadoDeducao
			);

			$aIfs = array(
				array('begin' => '{@ifIdentificacaoNfse}', 'end' => '{@endifIdentificacaoNfse}', 'bool' => $deducao->IdentificacaoDocumentoDeducao->tpDocumento == 0),
				array('begin' => '{@ifIdentificacaoNfe}', 'end' => '{@endifIdentificacaoNfe}', 'bool' => $deducao->IdentificacaoDocumentoDeducao->tpDocumento == 1),
				array('begin' => '{@ifOutroDocumento}', 'end' => '{@endifOutroDocumento}', 'bool' => $deducao->IdentificacaoDocumentoDeducao->tpDocumento == 2),
				array('begin' => '{@ifIdentificacaoFornecedor}', 'end' => '{@endifIdentificacaoFornecedor}', 'bool' => is_null($deducao->DadosFornecedor->CodigoPais)),
				array('begin' => '{@ifCpf}', 'end' => '{@endifCpf}', 'bool' => strlen($deducao->DadosFornecedor->CpfCnpj) == 11),
				array('begin' => '{@ifCnpj}', 'end' => '{@endifCnpj}', 'bool' => strlen($deducao->DadosFornecedor->CpfCnpj) == 14),
				array('begin' => '{@ifFornecedorExterior}', 'end' => '{@endifFornecedorExterior}', 'bool' => !is_null($deducao->DadosFornecedor->CodigoPais)),
				array('begin' => '{@ifNifFornecedor}', 'end' => '{@endifNifFornecedor}', 'bool' => !is_null($deducao->DadosFornecedor->NifFornecedor))
			);

			$deducoes .= PQDUtil::procTplText($tplDeducao, $aReplace, $aIfs);
		}

		//RPS
		$aReplace = array(
			'{@idRps}' => $oRps->idRps,
			'{@idInfDeclaracaoPrestacaoServico}' => $oRps->idInfDeclaracaoPrestacaoServico,
			'{@NumeroRps}' => $oRps->IdentificacaoRps->Numero,
			'{@SerieRps}' => $oRps->IdentificacaoRps->Serie,
			'{@TipoRps}' => $oRps->IdentificacaoRps->Tipo,
			'{@DataEmissao}' => $oRps->DataEmissao,
			'{@Status}' => $oRps->Status,
			'{@NumeroRpsSubstituido}' => $oRps->RpsSubstituido->Numero,
			'{@SerieRpsSubstituido}' => $oRps->RpsSubstituido->Serie,
			'{@TipoRpsSubstituido}' => $oRps->RpsSubstituido->Tipo,
			'{@Competencia}' => $oRps->Competencia,
			'{@ValorServicos}' => $oRps->Servico->Valores->ValorServicos,
			'{@ValorDeducoes}' => $oRps->Servico->Valores->ValorDeducoes,
			'{@ValorPis}' => $oRps->Servico->Valores->ValorPis,
			'{@ValorCofins}' => $oRps->Servico->Valores->ValorCofins,
			'{@ValorInss}' => $oRps->Servico->Valores->ValorInss,
			'{@ValorIr}' => $oRps->Servico->Valores->ValorIr,
			'{@ValorCsll}' => $oRps->Servico->Valores->ValorCsll,
			'{@OutrasRetencoes}' => $oRps->Servico->Valores->OutrasRetencoes,
			'{@ValTotTributos}' => $oRps->Servico->Valores->ValTotTributos,
			'{@ValorIss}' => $oRps->Servico->Valores->ValorIss,
			'{@Aliquota}' => $oRps->Servico->Valores->Aliquota,
			'{@DescontoIncondicionado}' => $oRps->Servico->Valores->DescontoIncondicionado,
			'{@DescontoCondicionado}' => $oRps->Servico->Valores->DescontoCondicionado,
			'{@IssRetido}' => $oRps->Servico->IssRetido,
			'{@ResponsavelRetencao}' => $oRps->Servico->ResponsavelRetencao,
			'{@ItemListaServico}' => $oRps->Servico->ItemListaServico,
			'{@CodigoCnae}' => $oRps->Servico->CodigoCnae,
			'{@CodigoTributacaoMunicipio}' => $oRps->Servico->CodigoTributacaoMunicipio,
			'{@CodigoNbs}' => $oRps->Servico->CodigoNbs,
			'{@Discriminacao}' => $oRps->Servico->Discriminacao,
			'{@CodigoMunicipioServico}' => $oRps->Servico->CodigoMunicipio,
			'{@CodigoPaisServico}' => $oRps->Servico->CodigoPais,
			'{@ExigibilidadeISS}' => $oRps->Servico->ExigibilidadeISS,
			'{@IdentifNaoExigibilidade}' => $oRps->Servico->IdentifNaoExigibilidade,
			'{@MunicipioIncidencia}' => $oRps->Servico->MunicipioIncidencia,
			'{@NumeroProcesso}' => $oRps->Servico->NumeroProcesso,
			'{@CpfPrestador}' => $oRps->Prestador->CpfCnpj,
			'{@CnpjPrestador}' => $oRps->Prestador->CpfCnpj,
			'{@InscricaoMunicipalPrestador}' => $oRps->Prestador->InscricaoMunicipal,
			'{@CpfTomadorServico}' => $oRps->Tomador->IdentificacaoTomador->CpfCnpj,
			'{@CnpjTomadorServico}' => $oRps->Tomador->IdentificacaoTomador->CpfCnpj,
			'{@InscricaoMunicipalTomadorServico}' => $oRps->Tomador->IdentificacaoTomador->InscricaoMunicipal,
			'{@NifTomador}' => $oRps->Tomador->NifTomador,
			'{@RazaoSocialTomadorServico}' => $oRps->Tomador->RazaoSocial,
			'{@Endereco}' => $oRps->Tomador->Endereco->Endereco,
			'{@Numero}' => $oRps->Tomador->Endereco->Numero,
			'{@Complemento}' => $oRps->Tomador->Endereco->Complemento,
			'{@Bairro}' => $oRps->Tomador->Endereco->Bairro,
			'{@CodigoMunicipioTomadorServico}' => $oRps->Tomador->Endereco->CodigoMunicipio,
			'{@Uf}' => $oRps->Tomador->Endereco->Uf,
			'{@Cep}' => $oRps->Tomador->Endereco->Cep,
			'{@CodigoPaisEnderecoExterior}' => $oRps->Tomador->Endereco->CodigoPaisEstrangeiro,
			'{@EnderecoCompletoExterior}' => $oRps->Tomador->Endereco->EnderecoCompletoExterior,
			'{@Telefone}' => $oRps->Tomador->Contato->Telefone,
			'{@Email}' => $oRps->Tomador->Contato->Email,
			'{@CpfIntermediario}' => $oRps->IntermediarioServico->IdentificacaoIntermediario->CpfCnpj,
			'{@CnpjIntermediario}' => $oRps->IntermediarioServico->IdentificacaoIntermediario->CpfCnpj,
			'{@InscricaoMunicipalIntermediario}' => $oRps->IntermediarioServico->IdentificacaoIntermediario->InscricaoMunicipal,
			'{@RazaoSocialIntermediario}' => $oRps->IntermediarioServico->RazaoSocial,
			'{@CodigoMunicipioIntermediario}' => $oRps->IntermediarioServico->CodigoMunicipio,
			'{@CodigoObra}' => $oRps->ConstrucaoCivil->CodigoObra,
			'{@Art}' => $oRps->ConstrucaoCivil->Art,
			'{@RegimeEspecialTributacao}' => $oRps->RegimeEspecialTributacao,
			'{@OptanteSimplesNacional}' => $oRps->OptanteSimplesNacional,
			'{@IncentivoFiscal}' => $oRps->IncentivoFiscal,
			'{@IdentificacaoEvento}' => $oRps->Evento->IdentificacaoEvento,
			'{@DescricaoEvento}' => $oRps->Evento->DescricaoEvento,
			'{@InformacoesComplementares}' => $oRps->InformacoesComplementares,
			'{@Deducoes}' => $deducoes
		);

		$aIfs = array(
			array('begin' => '{@ifRpsSubstituido}', 'end' => '{@endifRpsSubstituido}', 'bool' => !is_null($oRps->RpsSubstituido->Numero) ),
			array('begin' => '{@ifResponsavelRetencao}', 'end' => '{@endifResponsavelRetencao}', 'bool' => !is_null($oRps->Servico->ResponsavelRetencao) ),
			array('begin' => '{@ifCodigoTributacaoMunicipio}', 'end' => '{@endifCodigoTributacaoMunicipio}', 'bool' => !is_null($oRps->Servico->CodigoTributacaoMunicipio) ),
			array('begin' => '{@ifCodigoNbs}', 'end' => '{@endifCodigoNbs}', 'bool' => !is_null($oRps->Servico->CodigoNbs) ),
			array('begin' => '{@ifCodigoPaisServico}', 'end' => '{@endifCodigoPaisServico}', 'bool' => !is_null($oRps->Servico->CodigoPais) ),
			array('begin' => '{@ifIdentifNaoExigibilidade}', 'end' => '{@endifIdentifNaoExigibilidade}', 'bool' => !is_null($oRps->Servico->IdentifNaoExigibilidade) ),
			array('begin' => '{@ifMunicipioIncidencia}', 'end' => '{@endifMunicipioIncidencia}', 'bool' => !is_null($oRps->Servico->MunicipioIncidencia) ),
			array('begin' => '{@ifNumeroProcesso}', 'end' => '{@endifNumeroProcesso}', 'bool' => !is_null($oRps->Servico->NumeroProcesso) ),
			array('begin' => '{@ifCpfPrestador}', 'end' => '{@endifCpfPrestador}', 'bool' => strlen($oRps->Prestador->CpfCnpj) == 11),
			array('begin' => '{@ifCnpjPrestador}', 'end' => '{@endifCnpjPrestador}', 'bool' => strlen($oRps->Prestador->CpfCnpj) == 14),
			array('begin' => '{@ifCpfTomadorServico}', 'end' => '{@endifCpfTomadorServico}', 'bool' => strlen($oRps->Tomador->IdentificacaoTomador->CpfCnpj) == 11),
			array('begin' => '{@ifCnpjTomadorServico}', 'end' => '{@endifCnpjTomadorServico}', 'bool' => strlen($oRps->Tomador->IdentificacaoTomador->CpfCnpj) == 14),
			array('begin' => '{@ifInscricaoMunicipalTomadorServico}', 'end' => '{@endifInscricaoMunicipalTomadorServico}', 'bool' => !empty($oRps->Tomador->IdentificacaoTomador->InscricaoMunicipal)),
			array('begin' => '{@ifNifTomador}', 'end' => '{@endifNifTomador}', 'bool' => !is_null($oRps->Tomador->NifTomador)),
			array('begin' => '{@ifEndereco}', 'end' => '{@endifEndereco}', 'bool' => is_null($oRps->Tomador->Endereco->CodigoPaisEstrangeiro) ),
			array('begin' => '{@ifEnderecoExterior}', 'end' => '{@endifEnderecoExterior}', 'bool' => !is_null($oRps->Tomador->Endereco->CodigoPaisEstrangeiro) ),
			array('begin' => '{@ifContato}', 'end' => '{@endifContato}', 'bool' => !is_null($oRps->Tomador->Contato->Email) || !is_null($oRps->Tomador->Contato->Telefone) ),
			array('begin' => '{@ifTelefone}', 'end' => '{@endifTelefone}', 'bool' => !is_null($oRps->Tomador->Contato->Telefone) ),
			array('begin' => '{@ifEmail}', 'end' => '{@endifEmail}', 'bool' => !is_null($oRps->Tomador->Contato->Email) ),
			array('begin' => '{@ifIntermediario}', 'end' => '{@endifIntermediario}', 'bool' => !is_null($oRps->IntermediarioServico->IdentificacaoIntermediario->CpfCnpj) ),
			array('begin' => '{@ifCpfIntermediario}}', 'end' => '{@endifCpfIntermediario}}', 'bool' => strlen($oRps->IntermediarioServico->IdentificacaoIntermediario->CpfCnpj) == 11),
			array('begin' => '{@ifCnpjIntermediario}', 'end' => '{@endifCnpjIntermediario}', 'bool' => strlen($oRps->IntermediarioServico->IdentificacaoIntermediario->CpfCnpj) == 14),
			array('begin' => '{@ifConstrucaoCivil}', 'end' => '{@endifConstrucaoCivil}', 'bool' => !is_null($oRps->ConstrucaoCivil->Art) || !is_null($oRps->ConstrucaoCivil->CodigoObra)),
			array('begin' => '{@ifCodigoObra}', 'end' => '{@endifCodigoObra}', 'bool' => !is_null($oRps->ConstrucaoCivil->CodigoObra)),
			array('begin' => '{@ifArt}', 'end' => '{@endifArt}', 'bool' => !is_null($oRps->ConstrucaoCivil->Art)),
			array('begin' => '{@ifRegimeEspecialTributacao}', 'end' => '{@endifRegimeEspecialTributacao}', 'bool' => !is_null($oRps->RegimeEspecialTributacao) ),
			array('begin' => '{@ifEvento}', 'end' => '{@endifEvento}', 'bool' => !is_null($oRps->Evento->DescricaoEvento) || !is_null($oRps->Evento->IdentificacaoEvento) ),
			array('begin' => '{@ifIdentificacaoEvento}', 'end' => '{@endifIdentificacaoEvento}', 'bool' => !is_null($oRps->Evento->IdentificacaoEvento) ),
			array('begin' => '{@ifDescricaoEvento}', 'end' => '{@endifDescricaoEvento}', 'bool' => !is_null($oRps->Evento->DescricaoEvento) ),
			array('begin' => '{@ifInformacoesComplementares}', 'end' => '{@endifInformacoesComplementares}', 'bool' => !is_null($oRps->InformacoesComplementares) ),
			
			array('begin' => '{@ifIdInfDeclaracaoPrestacaoServico}', 'end' => '{@endifIdInfDeclaracaoPrestacaoServico}', 'bool' =>  !is_null($oRps->idInfDeclaracaoPrestacaoServico)  ),
			array('begin' => '{@ifIdRps}', 'end' => '{@endifIdRps}', 'bool' => !is_null($oRps->idRps) ),
		);

		return $this->retXML(PQDUtil::procTplText($tplRps, $aReplace, $aIfs));
	}

	/**
	 * Retorna o XML da requisição SOAP de acordo com o template 'soap'
	 * 
	 * @return string
	 */
	private function retXMLSoap($xml, $action) {

		$tpl = $this->getTemplate('soap');


		$aReplaces = $this->retReplaceUsuarios('soap');

		$aReplaces['replace']['{@xml}'] = $xml;
		$aReplaces['replace']['{@action}'] = $action;

		return $this->retXML(PQDUtil::procTplText($tpl, $aReplaces['replace'], $aReplaces['ifs']), false);
    }    
}

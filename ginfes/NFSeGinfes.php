<?php
namespace NFSe\ginfes;

use NFSe\NFSe;
use NFSe\NFSeDocument;
use NFSe\NFSeElement;

/**
 *
 * @since 2016-02-10
 * @author Willker Moraes Silva
 */
class NFSeGinfes extends NFSe {

	const XMLNS_CABECALHO = "http://www.ginfes.com.br/cabecalho_v03.xsd";

	const XMLNS_TIPOS = "http://www.ginfes.com.br/tipos_v03.xsd";

	const XMLNS_ENV_LT_RPS_ENV = "http://www.ginfes.com.br/servico_enviar_lote_rps_envio_v03.xsd";
	const XMLNS_ENV_LT_RPS_RES = "http://www.ginfes.com.br/servico_enviar_lote_rps_resposta_v03.xsd";

	const XMLNS_CONS_NFSE_ENV = "http://www.ginfes.com.br/servico_consultar_nfse_envio_v03.xsd";
	const XMLNS_CONS_NFSE_RES = "http://www.ginfes.com.br/servico_consultar_nfse_resposta_v03.xsd";

	const XMLNS_CONS_SIT_LT_RPS_ENV = "http://www.ginfes.com.br/servico_consultar_situacao_lote_rps_envio_v03.xsd";
	const XMLNS_CONS_SIT_LT_RPS_RES = "http://www.ginfes.com.br/servico_consultar_situacao_lote_rps_resposta_v03.xsd";

	const XMLNS_CONS_NFSE_RPS_ENV = "http://www.ginfes.com.br/servico_consultar_nfse_rps_envio_v03.xsd";
	const XMLNS_CONS_NFSE_RPS_RES = "http://www.ginfes.com.br/servico_consultar_nfse_rps_resposta_v03.xsd";

	const XMLNS_CONS_LT_RPS_ENV = "http://www.ginfes.com.br/servico_consultar_lote_rps_envio_v03.xsd";
	const XMLNS_CONS_LT_RPS_RES = "http://www.ginfes.com.br/servico_consultar_lote_rps_resposta_v03.xsd";

	private $homologacao = "https://homologacao.ginfes.com.br/ServiceGinfesImpl?wsdl";

	private $producao = "https://producao.ginfes.com.br/ServiceGinfesImpl?wsdl";

	private $isHomologacao = true;

	private $aConfig;

	/**
	 * O parâmetro de configuração deve ser um array com pelo menos as posições:
	 * $aConfig['cnpj'] => CNPJ prestador
	 * $aConfig['inscMunicipal'] => Insc. Municipal do Prestador
	 * $aConfig['privKey'] => Caminho para a chave privada do prestador
	 * $aConfig['pubKey'] => Caminho para a chave publica do prestador
	 * $aConfig['certKey'] => Caminho para o arquivo que contem a chave publica e privada
	 *
	 * Configurações opcionais
	 * $aConfig['pathSaveLotesRps'] => Caminho para salvar os arquivos XML de lotes enviados
	 * $aConfig['descontoIncondicionado'] => (boolean), caso true o campo desconto incondicionado será informado no XML, essa configuração depende se a prefeitura permite descontos incondicionados padrão false
	 *
	 * @see NFSe
	 *
	 * @param array $aConfig
	 * @param boolean $isHomologacao
	 */
	public function __construct(array $aConfig, $isHomologacao = true){

		if (isset($aConfig['pfx']) && isset($aConfig['pwdPFX']))
			$this->createTempFiles($aConfig['pfx'], $aConfig['pwdPFX'], $aConfig['cnpj'], $aConfig);

		parent::__construct($aConfig['privKey'], $aConfig['pubKey'], $aConfig['certKey']);
		$this->aConfig = $aConfig;
		$this->isHomologacao = $isHomologacao;
	}

	/**
	 * Cancela uma Nota Fiscal Eletrônica
	 *
	 * @param string $NFSe
	 * @return string
	 */
	public function cancelaNFSe($NFSe){

		$document = new NFSeDocument();
		$CancelarNfseEnvio = $document->appendChild($document->createElement("co:CancelarNfseEnvio"));
		$CancelarNfseEnvio->setAttribute("xmlns:co", "http://www.ginfes.com.br/servico_cancelar_nfse_envio");
		$CancelarNfseEnvio->setAttribute("xmlns:ts", "http://www.ginfes.com.br/tipos");

		$Prestador = $CancelarNfseEnvio->appendChild($document->createElement("co:Prestador"));
		$Prestador->appendChild($document->createElement("ts:Cnpj", $this->aConfig['cnpj']));
		$Prestador->appendChild($document->createElement("ts:InscricaoMunicipal", $this->aConfig['inscMunicipal']));

		$CancelarNfseEnvio->appendChild($document->createElement("co:NumeroNfse", $NFSe));

		$XMLAssinado = $this->signXML($document->saveXML(), "CancelarNfseEnvio");
		if(isset($this->aConfig['pathCert'])){
			file_put_contents($this->aConfig['pathCert'] . '/cancela_nfse_' . $NFSe . ".xml", $XMLAssinado);
			$pathFile = $this->aConfig['pathCert'] . '/cancela_nfse_' . $NFSe . "_ret.xml";
		}
		$url = $this->isHomologacao ? $this->homologacao: $this->producao;
		$action = "CancelarNfse";

		return NFSeGinfesReturn::getReturn($this->curl($url, $this->retXMLSoap($XMLAssinado, $action, false), array('Content-Type: text/xml')), $action, $pathFile);
	}

	/**
	 * Consulta de notas fiscais eletrônicas
	 *
	 * @param NFSeGinfesConsultarNFSe $oConsultarNFSe
	 * @return array[NFSeGinfesInfNFSe]|array[NFSeGinfesMensagemRetorno]
	 */
	public function consultaNFSe(NFSeGinfesConsultarNFSe $oConsultarNFSe){

		$document = new NFSeDocument();
		$ConsultarNfseEnvio = $document->appendChild($document->createElement("co:ConsultarNfseEnvio"));
		$ConsultarNfseEnvio->setAttribute("xmlns:co", self::XMLNS_CONS_NFSE_ENV);
		$ConsultarNfseEnvio->setAttribute("xmlns:ts", self::XMLNS_TIPOS);

		$Prestador = $ConsultarNfseEnvio->appendChild($document->createElement("co:Prestador"));
		$Prestador->appendChild($document->createElement("ts:Cnpj", $this->aConfig['cnpj']));
		$Prestador->appendChild($document->createElement("ts:InscricaoMunicipal", $this->aConfig['inscMunicipal']));

		if(!empty($oConsultarNFSe->NumeroNfse))
			$ConsultarNfseEnvio->appendChild($document->createElement("co:NumeroNfse", $oConsultarNFSe->NumeroNfse));

		//Periodo Emissao
		if(!empty($oConsultarNFSe->PeriodoEmissao->DataInicial)){
			$PeriodoEmissao = $ConsultarNfseEnvio->appendChild($document->createElement("co:PeriodoEmissao"));
			$PeriodoEmissao->appendChild($document->createElement("ts:DataInicial", $oConsultarNFSe->PeriodoEmissao->DataInicial));
			$PeriodoEmissao->appendChild($document->createElement("ts:DataFinal", $oConsultarNFSe->PeriodoEmissao->DataFinal));
		}

		//Tomador
		if($oConsultarNFSe->Tomador->getCpfCnpj() != ""){

			$IdentificacaoTomador = $ConsultarNfseEnvio->appendChild($document->createElement("co:Tomador"))->appendChild($document->createElement("ts:IdentificacaoTomador"));

			$CpfCnpj = $IdentificacaoTomador->appendChild($document->createElement("ts:CpfCnpj"));
			if ($oConsultarNFSe->Tomador->getTpPessoa() == 1)
				$CpfCnpj->appendChild($document->createElement("ts:Cnpj", $oConsultarNFSe->Tomador->getCpfCnpj()));
			else
				$CpfCnpj->appendChild($document->createElement("ts:Cpf", $oConsultarNFSe->Tomador->getCpfCnpj()));

			//Inscrição Municipal só deve ser informada para pessoa jurídica
			if($oConsultarNFSe->Tomador->getTpPessoa() == 1 && !empty($oConsultarNFSe->Tomador->InscricaoMunicipal))
				$IdentificacaoTomador->appendChild($document->createElement("ts:InscricaoMunicipal", $oConsultarNFSe->Tomador->InscricaoMunicipal));
		}

		//Intermediario Servico
		if (!empty($oRps->IntermediarioServico->RazaoSocial)){

			$IntermediarioServico = $ConsultarNfseEnvio->appendChild($document->createElement("co:IntermediarioServico"));
			$IntermediarioServico->appendChild($document->createElement("ts:RazaoSocial"))->appendChild($document->createCDATASection($oConsultarNFSe->IntermediarioServico->RazaoSocial));

			$CpfCnpj = $IntermediarioServico->appendChild($document->createElement("ts:CpfCnpj"));
			if($oConsultarNFSe->IntermediarioServico->getTpPessoa() == 1)
				$CpfCnpj->appendChild($document->createElement("ts:Cnpj", $oConsultarNFSe->IntermediarioServico->getCpfCnpj()));
			else
				$CpfCnpj->appendChild($document->createElement("ts:Cpf", $oConsultarNFSe->IntermediarioServico->getCpfCnpj()));

			if(!empty($oConsultarNFSe->IntermediarioServico->InscricaoMunicipal))
				$IntermediarioServico->appendChild($document->createElement("ts:InscricaoMunicipal", $oConsultarNFSe->IntermediarioServico->InscricaoMunicipal));
		}
		//echo $document->saveXML(); exit();

		$XMLAssinado = $this->signXML($document->saveXML(), "ConsultarNfseEnvio");

		$url = $this->isHomologacao ? $this->homologacao: $this->producao;
		$action = "ConsultarNfseV3";

		$return = $this->curl($url, $this->retXMLSoap($XMLAssinado, $action), array('Content-Type: text/xml'));

		return NFSeGinfesReturn::getReturn($return, $action);
	}

	/**
	 * Consulta Nota fiscal eletônica por RPS
	 *
	 * @param NFSeGinfesIdentificacaoRps $oIdentificacaoRps
	 * @return string
	 */
	public function consultarNFSePorRps(NFSeGinfesIdentificacaoRps $oIdentificacaoRps){

		$document = new NFSeDocument();
		$ConsultarNfseRpsEnvio = $document->appendChild($document->createElement("co:ConsultarNfseRpsEnvio"));
		$ConsultarNfseRpsEnvio->setAttribute("xmlns:co", self::XMLNS_CONS_NFSE_RPS_ENV);
		$ConsultarNfseRpsEnvio->setAttribute("xmlns:ts", self::XMLNS_TIPOS);

		$IdentificacaoRps = $ConsultarNfseRpsEnvio->appendChild($document->createElement("co:IdentificacaoRps"));
		$IdentificacaoRps->appendChild($document->createElement("ts:Numero", $oIdentificacaoRps->Numero));
		$IdentificacaoRps->appendChild($document->createElement("ts:Serie", $oIdentificacaoRps->Serie));
		$IdentificacaoRps->appendChild($document->createElement("ts:Tipo", $oIdentificacaoRps->Tipo));

		$Prestador = $ConsultarNfseRpsEnvio->appendChild($document->createElement("co:Prestador"));
		$Prestador->appendChild($document->createElement("ts:Cnpj", $this->aConfig['cnpj']));
		$Prestador->appendChild($document->createElement("ts:InscricaoMunicipal", $this->aConfig['inscMunicipal']));

		$XMLAssinado = $this->signXML($document->saveXML(), "ConsultarNfseRpsEnvio");

		$url = $this->isHomologacao ? $this->homologacao: $this->producao;
		$action = "ConsultarNfsePorRpsV3";

		return NFSeGinfesReturn::getReturn($this->curl($url, $this->retXMLSoap($XMLAssinado, $action), array('Content-Type: text/xml')), $action);
	}

	/**
	 * Consulta a situação de um lote
	 *
	 * @param string $protocolo
	 * @return string
	 */
	public function consultarSituacaoLoteRps($protocolo){

		$document = new NFSeDocument();
		$ConsultarLoteRpsEnvio = $document->appendChild($document->createElement("co:ConsultarSituacaoLoteRpsEnvio"));
		$ConsultarLoteRpsEnvio->setAttribute("xmlns:co", "http://www.ginfes.com.br/servico_consultar_situacao_lote_rps_envio_v03.xsd");
		$ConsultarLoteRpsEnvio->setAttribute("xmlns:ts", "http://www.ginfes.com.br/tipos_v03.xsd");

		$Prestador = $ConsultarLoteRpsEnvio->appendChild($document->createElement("co:Prestador"));
		$Prestador->appendChild($document->createElement("ts:Cnpj", $this->aConfig['cnpj']));
		$Prestador->appendChild($document->createElement("ts:InscricaoMunicipal", $this->aConfig['inscMunicipal']));

		$Protocolo = $ConsultarLoteRpsEnvio->appendChild($document->createElement("co:Protocolo", $protocolo));

		$XMLAssinado = $this->signXML($document->saveXML(), "ConsultarSituacaoLoteRpsEnvio");

		$url = $this->isHomologacao ? $this->homologacao: $this->producao;
		$action = "ConsultarSituacaoLoteRpsV3";

		return NFSeGinfesReturn::getReturn($this->curl($url, $this->retXMLSoap($XMLAssinado, $action), array('Content-Type: text/xml')), $action);
	}

	/**
	 * Consulta um lote RPS
	 *
	 * @param string $protocolo
	 * @return array[NFSeGinfesInfNFSe]|array[NFSeGinfesMensagemRetorno]
	 */
	public function consultarLoteRps($protocolo){

		$document = new NFSeDocument();
		$ConsultarLoteRpsEnvio = $document->appendChild($document->createElement("co:ConsultarLoteRpsEnvio"));
		$ConsultarLoteRpsEnvio->setAttribute("xmlns:co", self::XMLNS_CONS_LT_RPS_ENV);
		$ConsultarLoteRpsEnvio->setAttribute("xmlns:ts", self::XMLNS_TIPOS);

		$Prestador = $ConsultarLoteRpsEnvio->appendChild($document->createElement("co:Prestador"));
		$Prestador->appendChild($document->createElement("ts:Cnpj", $this->aConfig['cnpj']));
		$Prestador->appendChild($document->createElement("ts:InscricaoMunicipal", $this->aConfig['inscMunicipal']));

		$Protocolo = $ConsultarLoteRpsEnvio->appendChild($document->createElement("co:Protocolo", $protocolo));
		$XMLAssinado = $this->signXML($document->saveXML(), "ConsultarLoteRpsEnvio");

		$url = $this->isHomologacao ? $this->homologacao: $this->producao;
		$action = "ConsultarLoteRpsV3";

		$pathFile = null;
		if(isset($this->aConfig['pathCert'])){
			file_put_contents($this->aConfig['pathCert'] . '/cs_lt_' . $protocolo . ".xml", $XMLAssinado);
			$pathFile = $this->aConfig['pathCert'] . '/cs_lt_' . $protocolo . "_ret.xml";
		}

		return NFSeGinfesReturn::getReturn($this->curl($url, $this->retXMLSoap($XMLAssinado, $action), array('Content-Type: text/xml')), $action, $pathFile);
	}

	/**
	 * Retorna o XML de uma requisição SOAP
	 *
	 * @param string $xml
	 * @param string $action
	 * @param boolean $head
	 * @return string
	 */
	private function retXMLSoap($xml, $action, $head = true){

		$reqNS = $this->isHomologacao ? parse_url($this->homologacao): parse_url($this->producao);

		$xml = trim(preg_replace('/^<\?xml.*\?>\R?/', "", $xml));

		$xmlSOAP = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
		$xmlSOAP .= '<soap:Envelope xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:soap="http://www.w3.org/2003/05/soap-envelope" xmlns:req="http://' . $reqNS['host'] . '">';
		$xmlSOAP .= '<soap:Header>';
		$xmlSOAP .= '</soap:Header>';
		$xmlSOAP .= '<soap:Body>';
		$xmlSOAP .= '<req:' . $action . '>';
		if($head){
			$xmlSOAP .= '<arg0>';
			$xmlSOAP .= '<cab:cabecalho xmlns:cab="' . self::XMLNS_CABECALHO . '" versao="3"><versaoDados>3</versaoDados></cab:cabecalho>';
			$xmlSOAP .= '</arg0>';

			$xmlSOAP .= '<arg1>';
			$xmlSOAP .= $xml;
			$xmlSOAP .= '</arg1>';
		}
		else{
			$xmlSOAP .= '<arg0>';
			$xmlSOAP .= $xml;
			$xmlSOAP .= '</arg0>';
		}
		$xmlSOAP .= '</req:' . $action . '>';
		$xmlSOAP .= '</soap:Body>';
		$xmlSOAP .= '</soap:Envelope>';

		return $xmlSOAP;
	}

	/**
	 * Envia um lote de RPS
	 *
	 * @param array[NFSeGinfesInfRps] $aRps
	 * @param NFSeGinfesLoteRps $oLote
	 * @return NFSeGinfesEnviarLoteRpsResposta
	 */
	public function enviarLoteRps(array $aListaRps, NFSeGinfesLoteRps $oLote){
		$url = $this->isHomologacao ? $this->homologacao: $this->producao;

		$document = new NFSeDocument();

		$EnviarLoteRpsEnvio = $document->appendChild($document->createElement("lt:EnviarLoteRpsEnvio"));
		$EnviarLoteRpsEnvio->setAttribute("xmlns:lt", self::XMLNS_ENV_LT_RPS_ENV);
		$EnviarLoteRpsEnvio->setAttribute("xmlns:ts", self::XMLNS_TIPOS);
		$LoteRps = $EnviarLoteRpsEnvio->appendChild($document->createElement("lt:LoteRps"));
		$LoteRps->setAttribute("Id", $oLote->NumeroLote);

		$LoteRps->appendChild($document->createElement("ts:NumeroLote", $oLote->NumeroLote));
		$LoteRps->appendChild($document->createElement("ts:Cnpj", $this->aConfig['cnpj']));
		$LoteRps->appendChild($document->createElement("ts:InscricaoMunicipal", $this->aConfig['inscMunicipal']));
		$LoteRps->appendChild($document->createElement("ts:QuantidadeRps", count($aListaRps)));
		$ListaRps = $LoteRps->appendChild($document->createElement("ts:ListaRps"));

		foreach ($aListaRps as $oRps)
			$this->addRps($document, $ListaRps, $oRps);

		$XMLAssinado = $this->signXML(trim($document->saveXML()), "LoteRps");
		$action = "RecepcionarLoteRpsV3";

		$pathFile = null;
		if(isset($this->aConfig['pathCert'])){
			file_put_contents($this->aConfig['pathCert'] . '/env_lt_' . $oLote->NumeroLote . ".xml", $XMLAssinado);
			$pathFile = $this->aConfig['pathCert'] . '/env_lt_' . $oLote->NumeroLote . "_ret.xml";
			file_put_contents($this->aConfig['pathCert'] . '/env_lt_soap_' . $oLote->NumeroLote . ".xml", $this->retXMLSoap($XMLAssinado, $action));
		}

		return NFSeGinfesReturn::getReturn($this->soap($url, $url, $action, $this->retXMLSoap($XMLAssinado, $action)), $action, $pathFile);
	}


	/**
	 * Adiciona uma RPS ao lote
	 *
	 * @param NFSeDocument $document
	 * @param NFSeElement $ListaRps
	 * @param NFSeGinfesInfRps $oRps
	 */
	private function addRps(NFSeDocument $document, NFSeElement $ListaRps, NFSeGinfesInfRps $oRps){
		$Rps = $ListaRps->appendChild($document->createElement("ts:Rps"));
		$InfRps = $Rps->appendChild($document->createElement("ts:InfRps"));

		$IdentificacaoRps = $InfRps->appendChild($document->createElement("ts:IdentificacaoRps"));
		$InfRps->appendChild($document->createElement("ts:DataEmissao", $oRps->DataEmissao));
		$InfRps->appendChild($document->createElement("ts:NaturezaOperacao", $oRps->NaturezaOperacao));
		$InfRps->appendChild($document->createElement("ts:RegimeEspecialTributacao", $oRps->RegimeEspecialTributacao));
		$InfRps->appendChild($document->createElement("ts:OptanteSimplesNacional", $oRps->OptanteSimplesNacional));
		$InfRps->appendChild($document->createElement("ts:IncentivadorCultural", $oRps->IncentivadorCultural));
		$InfRps->appendChild($document->createElement("ts:Status", $oRps->Status));

		if(!empty($oRps->RpsSubstituido->Numero)){
			$RpsSubstituido = $InfRps->appendChild($document->createElement("ts:RpsSubstituido"));
			$RpsSubstituido->appendChild($document->createElement("ts:Numero", $oRps->RpsSubstituido->Numero));
			$RpsSubstituido->appendChild($document->createElement("ts:Serie", $oRps->RpsSubstituido->Serie));
			$RpsSubstituido->appendChild($document->createElement("ts:Tipo", $oRps->RpsSubstituido->Tipo));
		}

		$Servico = $InfRps->appendChild($document->createElement("ts:Servico"));

		$IdentificacaoRps->appendChild($document->createElement("ts:Numero", $oRps->IdentificacaoRps->Numero));
		$IdentificacaoRps->appendChild($document->createElement("ts:Serie", $oRps->IdentificacaoRps->Serie));
		$IdentificacaoRps->appendChild($document->createElement("ts:Tipo", $oRps->IdentificacaoRps->Tipo));

		$Valores = $Servico->appendChild($document->createElement("ts:Valores"));
		$Servico->appendChild($document->createElement("ts:ItemListaServico", $oRps->Servico->ItemListaServico));
		//Gerando problemas no envio, de acordo com o Forum dá ginfes essa tag é opcional pois é um código federal
		//$Servico->appendChild($document->createElement("ts:CodigoCnae", $oRps->Servico->CodigoCnae));
		$Servico->appendChild($document->createElement("ts:CodigoTributacaoMunicipio", $oRps->Servico->CodigoTributacaoMunicipio));
		$Servico->appendChild($document->createElement("ts:Discriminacao"))->appendChild($document->createCDATASection($oRps->Servico->Discriminacao));
		$Servico->appendChild($document->createElement("ts:CodigoMunicipio", $oRps->Servico->CodigoMunicipio));

		$Valores->appendChild($document->createElement("ts:ValorServicos", number_format($oRps->Servico->Valores->ValorServicos, 2, '.', '')));
		$Valores->appendChild($document->createElement("ts:ValorDeducoes", number_format($oRps->Servico->Valores->ValorDeducoes, 2, '.', '')));
		$Valores->appendChild($document->createElement("ts:ValorPis", number_format($oRps->Servico->Valores->ValorPis, 2, '.', '')));
		$Valores->appendChild($document->createElement("ts:ValorCofins", number_format($oRps->Servico->Valores->ValorCofins, 2, '.', '')));
		$Valores->appendChild($document->createElement("ts:ValorInss", number_format($oRps->Servico->Valores->ValorInss, 2, '.', '')));
		$Valores->appendChild($document->createElement("ts:ValorIr", number_format($oRps->Servico->Valores->ValorIr, 2, '.', '')));
		$Valores->appendChild($document->createElement("ts:ValorCsll", number_format($oRps->Servico->Valores->ValorCsll, 2, '.', '')));
		$Valores->appendChild($document->createElement("ts:IssRetido", $oRps->Servico->Valores->IssRetido));
		$Valores->appendChild($document->createElement("ts:ValorIss", number_format($oRps->Servico->Valores->ValorIss, 2, '.', '')));
		$Valores->appendChild($document->createElement("ts:ValorIssRetido", number_format($oRps->Servico->Valores->ValorIssRetido, 2, '.', '')));
		$Valores->appendChild($document->createElement("ts:OutrasRetencoes", number_format($oRps->Servico->Valores->OutrasRetencoes, 2, '.', '')));
		$Valores->appendChild($document->createElement("ts:BaseCalculo", number_format($oRps->Servico->Valores->BaseCalculo, 2, '.', '')));
		$Valores->appendChild($document->createElement("ts:Aliquota", number_format($oRps->Servico->Valores->Aliquota, 4, '.', '')));

		if($oRps->Servico->Valores->ValorLiquidoNfse == 0){
			$valor = $oRps->Servico->Valores->ValorServicos;
			$valor -= $oRps->Servico->Valores->ValorPis;
			$valor -= $oRps->Servico->Valores->ValorCofins;
			$valor -= $oRps->Servico->Valores->ValorInss;
			$valor -= $oRps->Servico->Valores->ValorIr;
			$valor -= $oRps->Servico->Valores->ValorCsll;
			$valor -= $oRps->Servico->Valores->OutrasRetencoes;
			$valor -= $oRps->Servico->Valores->ValorIssRetido;
			$valor -= $oRps->Servico->Valores->DescontoCondicionado;
			$valor -= $oRps->Servico->Valores->DescontoIncondicionado;

			$Valores->appendChild($document->createElement("ts:ValorLiquidoNfse", number_format($valor, 2, '.', '')));
		}
		else
			$Valores->appendChild($document->createElement("ts:ValorLiquidoNfse", number_format($oRps->Servico->Valores->ValorLiquidoNfse, 2, '.', '')));

		if(isset($this->aConfig['descontoIncondicionado']) && $this->aConfig['descontoIncondicionado'] == true)
			$Valores->appendChild($document->createElement("ts:DescontoIncondicionado", number_format($oRps->Servico->Valores->DescontoIncondicionado, 2)));

		$Valores->appendChild($document->createElement("ts:DescontoCondicionado", $oRps->Servico->Valores->DescontoCondicionado));

		$Prestador = $InfRps->appendChild($document->createElement("ts:Prestador"));
		$Prestador->appendChild($document->createElement("ts:Cnpj", $this->aConfig['cnpj']));
		$Prestador->appendChild($document->createElement("ts:InscricaoMunicipal", $this->aConfig['inscMunicipal']));

		$Tomador = $InfRps->appendChild($document->createElement("ts:Tomador"));
		$IdentificacaoTomador = $Tomador->appendChild($document->createElement("ts:IdentificacaoTomador"));
		$CpfCnpj = $IdentificacaoTomador->appendChild($document->createElement("ts:CpfCnpj"));
		if ($oRps->Tomador->IdentificacaoTomador->getTpPessoa() == 1)
			$CpfCnpj->appendChild($document->createElement("ts:Cnpj", $oRps->Tomador->IdentificacaoTomador->getCpfCnpj()));
		else
			$CpfCnpj->appendChild($document->createElement("ts:Cpf", $oRps->Tomador->IdentificacaoTomador->getCpfCnpj()));

		//Inscrição Municipal só deve ser informada para pessoa jurídica
		if($oRps->Tomador->IdentificacaoTomador->getTpPessoa() == 1 && !empty($oRps->Tomador->IdentificacaoTomador->InscricaoMunicipal))
			$IdentificacaoTomador->appendChild($document->createElement("ts:InscricaoMunicipal", $oRps->Tomador->IdentificacaoTomador->InscricaoMunicipal));

		$Tomador->appendChild($document->createElement("ts:RazaoSocial"))->appendChild($document->createCDATASection($oRps->Tomador->RazaoSocial));
		$Endereco = $Tomador->appendChild($document->createElement("ts:Endereco"));
		$Endereco->appendChild($document->createElement("ts:Endereco"))->appendChild($document->createCDATASection($oRps->Tomador->Endereco->Endereco));
		$Endereco->appendChild($document->createElement("ts:Numero", $oRps->Tomador->Endereco->Numero));
		$Endereco->appendChild($document->createElement("ts:Complemento"))->appendChild($document->createCDATASection($oRps->Tomador->Endereco->Complemento));
		$Endereco->appendChild($document->createElement("ts:Bairro"))->appendChild($document->createCDATASection($oRps->Tomador->Endereco->Bairro));
		$Endereco->appendChild($document->createElement("ts:CodigoMunicipio", $oRps->Tomador->Endereco->CodigoMunicipio));
		$Endereco->appendChild($document->createElement("ts:Uf", $oRps->Tomador->Endereco->Uf));
		$Endereco->appendChild($document->createElement("ts:Cep", $oRps->Tomador->Endereco->Cep));

		if((!empty($oRps->Tomador->Contato->Telefone) && trim($oRps->Tomador->Contato->Telefone) != "") || (!empty($oRps->Tomador->Contato->Email) && trim($oRps->Tomador->Contato->Email) != "")){

			$Contato = $Tomador->appendChild($document->createElement("ts:Contato"));

			if (!empty($oRps->Tomador->Contato->Telefone) && trim($oRps->Tomador->Contato->Telefone) != "")
				$Contato->appendChild($document->createElement("ts:Telefone", $oRps->Tomador->Contato->Telefone));

			if (!empty($oRps->Tomador->Contato->Email) && trim($oRps->Tomador->Contato->Email) != "")
				$Contato->appendChild($document->createElement("ts:Email", $oRps->Tomador->Contato->Email));
		}

		if (!empty($oRps->IntermediarioServico->RazaoSocial)){
			$IntermediarioServico = $InfRps->appendChild($document->createElement("ts:IntermediarioServico"));
			$IntermediarioServico->appendChild($document->createElement("ts:RazaoSocial"))->appendChild($document->createCDATASection($oRps->IntermediarioServico->RazaoSocial));
			$CpfCnpj = $IntermediarioServico->appendChild($document->createElement("ts:CpfCnpj"));
			if($oRps->IntermediarioServico->getTpPessoa() == 1)
				$CpfCnpj->appendChild($document->createElement("ts:Cnpj", $oRps->IntermediarioServico->getCpfCnpj()));
			else
				$CpfCnpj->appendChild($document->createElement("ts:Cpf", $oRps->IntermediarioServico->getCpfCnpj()));

			if(!empty($oRps->IntermediarioServico->InscricaoMunicipal) && trim($oRps->IntermediarioServico->InscricaoMunicipal) != "")
				$IntermediarioServico->appendChild($document->createElement("ts:InscricaoMunicipal", $oRps->IntermediarioServico->InscricaoMunicipal));
		}

		if (!empty($oRps->ConstrucaoCivil->CodigoObra)){
			$ConstrucaoCivil = $InfRps->appendChild($document->createElement("ts:ConstrucaoCivil"));
			$ConstrucaoCivil->appendChild($document->createElement("ts:CodigoObra", $oRps->ConstrucaoCivil->CodigoObra));
			$ConstrucaoCivil->appendChild($document->createElement("ts:CodigoObra", $oRps->ConstrucaoCivil->Art));
		}
	}

	/**
	 * @return the $isHomologacao
	 */
	public function getIsHomologacao() {
		return $this->isHomologacao;
	}
}
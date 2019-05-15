<?php
namespace NFSe\sigep;

use NFSe\NFSe;
use NFSe\NFSeDocument;
use NFSe\NFSeElement;

/**
 * Prefeituras: Senador Canedo
 *
 * @since 2017-03-13
 * @author Willker Moraes Silva
 */
class NFSeSigep extends NFSe {

	const XMLNS = "http://www.abrasf.org.br/nfse.xsd";

	private $homologacao = "http://gestaopublica.canedo.bsit-br.com.br/integracao/services/nfseWS?wsdl";

	private $producao = "http://gestaopublica.canedo.bsit-br.com.br/integracao/services/nfseWS?wsdl";

	private $soap = "http://ws.integration.pm.bsit.com.br/";

	private $isHomologacao = true;

	private $aConfig;

	/**
	 * O parâmetro de configuração deve ser um array com pelo menos as posições:
	 * $aConfig['cnpj'] => CNPJ prestador
	 * $aConfig['inscMunicipal'] => Insc. Municipal do Prestador
	 * $aConfig['pfx'] => Caminho para o arquivo pfx
	 * $aConfig['pwdPFX'] => Senha do arquivo pfx
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
	 * @param NFSeSigepCancelarNfseEnvio $oCancelar
	 * @return string
	 */
	public function cancelaNFSe(NFSeSigepCancelarNfseEnvio $oCancelar){

		$url = $this->isHomologacao ? $this->homologacao: $this->producao;

		$document = new NFSeDocument();

		$CancelarNfseEnvio = $document->appendChild($document->createElement("CancelarNfseEnvio"));
		$CancelarNfseEnvio->setAttribute("xmlns", self::XMLNS);
		$credenciais = $CancelarNfseEnvio->appendChild($document->createElement("credenciais"));

		$credenciais->appendChild($document->createElement("usuario", $this->aConfig['sigep_usuario']));
		$credenciais->appendChild($document->createElement("senha", $this->aConfig['sigep_senha']));
		$credenciais->appendChild($document->createElement("chavePrivada", $this->aConfig['sigep_chavePrivada']));

		$Pedido = $CancelarNfseEnvio->appendChild($document->createElement("Pedido"));

		$InfPedidoCancelamento = $Pedido->appendChild($document->createElement("InfPedidoCancelamento"));

		$IdentificacaoNfse = $InfPedidoCancelamento->appendChild($document->createElement("IdentificacaoNfse"));
		$IdentificacaoNfse->appendChild($document->createElement("Numero", $oCancelar->Numero));
		$CpfCnpj = $IdentificacaoNfse->appendChild($document->createElement("CpfCnpj"));
		if ($oCancelar->getTpPessoa() == 0)
			$CpfCnpj->appendChild($document->createElement("Cpf", $oCancelar->getCpfCnpj()));
		else
			$CpfCnpj->appendChild($document->createElement("Cnpj", $oCancelar->getCpfCnpj()));

		if (!empty($oCancelar->InscricaoMunicipal))
			$IdentificacaoNfse->appendChild($document->createElement("InscricaoMunicipal", $oCancelar->InscricaoMunicipal));

		$IdentificacaoNfse->appendChild($document->createElement("CodigoVerificacao", $oCancelar->CodigoVerificacao));

		$InfPedidoCancelamento->appendChild($document->createElement("CodigoCancelamento", $oCancelar->CodigoCancelamento));
		$InfPedidoCancelamento->appendChild($document->createElement("DescricaoCancelamento", $oCancelar->DescricaoCancelamento));

		$XMLAssinado = $this->signXML(trim($document->saveXML()), "InfPedidoCancelamento", "Pedido");

		$url = $this->isHomologacao ? $this->homologacao: $this->producao;
		$action = "cancelarNfse";

		$pathSoapReturn = $pathFile = null;
		if(isset($this->aConfig['pathCert'])){
			file_put_contents($this->aConfig['pathCert'] . '/cancela_nfse_' . $oCancelar->Numero . ".xml", $XMLAssinado);
			$pathFile = $this->aConfig['pathCert'] . '/cancela_nfse_' . $oCancelar->Numero . "_ret.xml";
			file_put_contents($this->aConfig['pathCert'] . '/cancela_nfse_soap_' . $oCancelar->Numero . ".xml", $this->retXMLSoap($XMLAssinado, $action, false));

			$pathSoapReturn = $this->aConfig['pathCert'] . '/cancela_nfse_' . $oCancelar->Numero . "_ret_soap.xml";
		}

		$soapReturn = $this->soap($url, $url, $action, $this->retXMLSoap($XMLAssinado, $action, false));

		if (!is_null($pathSoapReturn))
			file_put_contents($pathSoapReturn, $soapReturn);

		return NFSeSigepReturn::getReturn($soapReturn, $action, $pathFile);
	}

	/**
	 * Consulta de notas fiscais eletrônicas
	 *
	 * @param NFSeSigepConsultarNFSe $oConsultarNFSe
	 * @return array[NFSeSigepInfNFSe]|array[NFSeSigepMensagemRetorno]
	 */
	public function consultaNFSe(NFSeSigepConsultarNFSe $oConsultarNFSe){
		//FIXME: fazer consulta com padrão sigep
		$document = new NFSeDocument();
		$ConsultarNfseEnvio = $document->appendChild($document->createElement("ConsultarNfseEnvio"));
		$ConsultarNfseEnvio->setAttribute("xmlns", self::XMLNS);

		$Prestador = $ConsultarNfseEnvio->appendChild($document->createElement("Prestador"));
		$Prestador->appendChild($document->createElement("Cnpj", $this->aConfig['cnpj']));
		$Prestador->appendChild($document->createElement("InscricaoMunicipal", $this->aConfig['inscMunicipal']));

		if(!empty($oConsultarNFSe->NumeroNfse))
			$ConsultarNfseEnvio->appendChild($document->createElement("NumeroNfse", $oConsultarNFSe->NumeroNfse));

		//Periodo Emissao
		if(!empty($oConsultarNFSe->PeriodoEmissao->DataInicial)){
			$PeriodoEmissao = $ConsultarNfseEnvio->appendChild($document->createElement("PeriodoEmissao"));
			$PeriodoEmissao->appendChild($document->createElement("DataInicial", $oConsultarNFSe->PeriodoEmissao->DataInicial));
			$PeriodoEmissao->appendChild($document->createElement("DataFinal", $oConsultarNFSe->PeriodoEmissao->DataFinal));
		}

		//Tomador
		if($oConsultarNFSe->Tomador->getCpfCnpj() != ""){

			$IdentificacaoTomador = $ConsultarNfseEnvio->appendChild($document->createElement("Tomador"))->appendChild($document->createElement("IdentificacaoTomador"));

			$CpfCnpj = $IdentificacaoTomador->appendChild($document->createElement("CpfCnpj"));
			if ($oConsultarNFSe->Tomador->getTpPessoa() == 1)
				$CpfCnpj->appendChild($document->createElement("Cnpj", $oConsultarNFSe->Tomador->getCpfCnpj()));
			else
				$CpfCnpj->appendChild($document->createElement("Cpf", $oConsultarNFSe->Tomador->getCpfCnpj()));

			//Inscrição Municipal só deve ser informada para pessoa jurídica
			if($oConsultarNFSe->Tomador->getTpPessoa() == 1 && !empty($oConsultarNFSe->Tomador->InscricaoMunicipal))
				$IdentificacaoTomador->appendChild($document->createElement("InscricaoMunicipal", $oConsultarNFSe->Tomador->InscricaoMunicipal));
		}

		//Intermediario Servico
		if (!empty($oRps->IntermediarioServico->RazaoSocial)){

			$IntermediarioServico = $ConsultarNfseEnvio->appendChild($document->createElement("IntermediarioServico"));
			$IntermediarioServico->appendChild($document->createElement("RazaoSocial"))->appendChild($document->createCDATASection($oConsultarNFSe->IntermediarioServico->RazaoSocial));

			$CpfCnpj = $IntermediarioServico->appendChild($document->createElement("CpfCnpj"));
			if($oConsultarNFSe->IntermediarioServico->getTpPessoa() == 1)
				$CpfCnpj->appendChild($document->createElement("Cnpj", $oConsultarNFSe->IntermediarioServico->getCpfCnpj()));
			else
				$CpfCnpj->appendChild($document->createElement("Cpf", $oConsultarNFSe->IntermediarioServico->getCpfCnpj()));

			if(!empty($oConsultarNFSe->IntermediarioServico->InscricaoMunicipal))
				$IntermediarioServico->appendChild($document->createElement("InscricaoMunicipal", $oConsultarNFSe->IntermediarioServico->InscricaoMunicipal));
		}
		//echo $document->saveXML(); exit();

		$XMLAssinado = $this->signXML($document->saveXML(), "ConsultarNfseEnvio");

		$url = $this->isHomologacao ? $this->homologacao: $this->producao;
		$action = "ConsultarNfseV3";

		$return = $this->curl($url, $this->retXMLSoap($XMLAssinado, $action), array('Content-Type: text/xml'));

		return NFSeSigepReturn::getReturn($return, $action);
	}

	/**
	 * Consulta Nota fiscal eletônica por RPS
	 *
	 * @param NFSeSigepIdentificacaoRps $oIdentificacaoRps
	 * @return string
	 */
	public function consultarNFSePorRps(NFSeSigepIdentificacaoRps $oIdentificacaoRps){

		$document = new NFSeDocument();
		$ConsultarNfseRpsEnvio = $document->appendChild($document->createElement("ConsultarNfseRpsEnvio"));
		$ConsultarNfseRpsEnvio->setAttribute("xmlns:co", self::XMLNS);

		$credenciais = $ConsultarNfseRpsEnvio->appendChild($document->createElement("credenciais"));
		$credenciais->appendChild($document->createElement("usuario", $this->aConfig['sigep_usuario']));
		$credenciais->appendChild($document->createElement("senha", $this->aConfig['sigep_senha']));
		$credenciais->appendChild($document->createElement("chavePrivada", $this->aConfig['sigep_chavePrivada']));

		$IdentificacaoRps = $ConsultarNfseRpsEnvio->appendChild($document->createElement("IdentificacaoRps"));
		$IdentificacaoRps->appendChild($document->createElement("Numero", $oIdentificacaoRps->Numero));
		$IdentificacaoRps->appendChild($document->createElement("Tipo", $oIdentificacaoRps->Tipo));

		$Prestador = $ConsultarNfseRpsEnvio->appendChild($document->createElement("Prestador"));
		$Prestador->appendChild( $document->createElement("CpfCnpj"))->appendChild($document->createElement("Cnpj", $this->aConfig['cnpj']));
		$Prestador->appendChild($document->createElement("InscricaoMunicipal", $this->aConfig['inscMunicipal']));

		$url = $this->isHomologacao ? $this->homologacao: $this->producao;
		$action = "consultarNfseRps";

		$pathSoapReturn = $pathFile = null;
		if(isset($this->aConfig['pathCert'])){
			file_put_contents($this->aConfig['pathCert'] . '/consultar_nfse_rps_' . $oIdentificacaoRps->Numero . ".xml", $document->saveXML());
			$pathFile = $this->aConfig['pathCert'] . '/consultar_nfse_rps_' . $oIdentificacaoRps->Numero . "_ret.xml";
			file_put_contents($this->aConfig['pathCert'] . '/consultar_nfse_rps_soap_' . $oIdentificacaoRps->Numero . ".xml", $this->retXMLSoap($document->saveXML(), $action));

			$pathSoapReturn = $this->aConfig['pathCert'] . '/consultar_nfse_rps_' . $oIdentificacaoRps->Numero . "_ret_soap.xml";
		}

		$soapReturn = $this->curl($url, $this->retXMLSoap($document->saveXML(), $action), array('Content-Type: text/xml'), 80);

		if (!is_null($pathSoapReturn))
			file_put_contents($pathSoapReturn, $soapReturn);

		return NFSeSigepReturn::getReturn($soapReturn, $action, $pathFile);
	}

	/**
	 * Consulta um lote RPS
	 *
	 * @param string $protocolo
	 * @return array[NFSeSigepInfNFSe]|array[NFSeSigepMensagemRetorno]
	 */
	public function consultarLoteRps($protocolo){

		//FIXME: Fazer metodo com o padrão ISSWEB
		$document = new NFSeDocument();
		$ConsultarLoteRpsEnvio = $document->appendChild($document->createElement("ConsultarLoteRpsEnvio"));
		$ConsultarLoteRpsEnvio->setAttribute("xmlns", self::XMLNS);

		$Prestador = $ConsultarLoteRpsEnvio->appendChild($document->createElement("Prestador"));
		$Prestador->appendChild($document->createElement("Cnpj", $this->aConfig['cnpj']));
		$Prestador->appendChild($document->createElement("InscricaoMunicipal", $this->aConfig['inscMunicipal']));

		$Protocolo = $ConsultarLoteRpsEnvio->appendChild($document->createElement("Protocolo", $protocolo));
		$XMLAssinado = $this->signXML($document->saveXML(), "ConsultarLoteRpsEnvio");

		$url = $this->isHomologacao ? $this->homologacao: $this->producao;
		$action = "ConsultarLoteRpsV3";

		$pathFile = null;
		if(isset($this->aConfig['pathCert'])){
			file_put_contents($this->aConfig['pathCert'] . '/cs_lt_' . $protocolo . ".xml", $XMLAssinado);
			$pathFile = $this->aConfig['pathCert'] . '/cs_lt_' . $protocolo . "_ret.xml";
		}

		return NFSeSigepReturn::getReturn($this->curl($url, $this->retXMLSoap($XMLAssinado, $action), array('Content-Type: text/xml')), $action, $pathFile);
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

		$oXMLSOAP = new NFSeDocument();
		$Envelope = $oXMLSOAP->appendChild($oXMLSOAP->createElement("soap:Envelope"));
		$Envelope->setAttribute("xmlns:soap", "http://schemas.xmlsoap.org/soap/envelope/");
		$Envelope->setAttribute("xmlns:req", $this->soap);


		$Body = $Envelope->appendChild($oXMLSOAP->createElement("soap:Body"));

		$req = $Body->appendChild($oXMLSOAP->createElement("req:" . $action));
		$GerarNfseEnvio = $req->appendChild($oXMLSOAP->createElement(ucwords($action) . "Envio"));
		//$GerarNfseEnvio->appendChild($oXMLSOAP->createTextNode($xml));


		$xmldoc = new NFSeDocument();
		$xmldoc->preservWhiteSpace = false; // elimina espaÃ§os em branco
		$xmldoc->formatOutput = false;
		$xmldoc->loadXML($xml, LIBXML_NOBLANKS | LIBXML_NOEMPTYTAG);

		$GerarNfseEnvio->appendChild($oXMLSOAP->createCDATASection( $xmldoc->getElementsByTagName(ucwords($action) . "Envio")->item(0)->C14N() ) );
		return $oXMLSOAP->saveXML();
	}

	/**
	 * Envia um lote de RPS
	 *
	 * @param array[NFSeSigepInfRps] $aRps
	 * @param NFSeSigepLoteRps $oLote
	 * @return NFSeSigepEnviarLoteRpsResposta
	 */
	public function enviarLoteRps(array $aListaRps, NFSeSigepLoteRps $oLote){

		//FIXME: Fazer envio de lote com o padrão ISSWEB

		$url = $this->isHomologacao ? $this->homologacao: $this->producao;

		$document = new NFSeDocument();

		$EnviarLoteRpsEnvio = $document->appendChild($document->createElement("EnviarLoteRpsSincronoEnvio"));
		$EnviarLoteRpsEnvio->setAttribute("xmlns", 'http://www.abrasf.org.br/nfse.xsd');
		$LoteRps = $EnviarLoteRpsEnvio->appendChild($document->createElement("LoteRps"));
		//$LoteRps->setAttribute("Id", $oLote->NumeroLote);
		$LoteRps->setAttribute("versao", "1.00");

		$LoteRps->appendChild($document->createElement("NumeroLote", $oLote->NumeroLote));

		$LoteRps->appendChild($document->createElement("CpfCnpj"))->appendChild($document->createElement("Cnpj", $this->aConfig['cnpj']));

		$LoteRps->appendChild($document->createElement("InscricaoMunicipal", $this->aConfig['inscMunicipal']));
		$LoteRps->appendChild($document->createElement("QuantidadeRps", count($aListaRps)));
		$ListaRps = $LoteRps->appendChild($document->createElement("ListaRps"));

		foreach ($aListaRps as $oRps){
			$this->addRps($document, $ListaRps, $oRps);
		}

		$XMLAssinado = $this->signXML(trim($document->saveXML()), "LoteRps");
		$action = "enviarLoteRpsSincrono";

		$pathFile = null;
		if(isset($this->aConfig['pathCert'])){
			file_put_contents($this->aConfig['pathCert'] . '/env_lt_' . $oLote->NumeroLote . ".xml", $XMLAssinado);
			$pathFile = $this->aConfig['pathCert'] . '/env_lt_' . $oLote->NumeroLote . "_ret.xml";
			file_put_contents($this->aConfig['pathCert'] . '/env_lt_soap_' . $oLote->NumeroLote . ".xml", $this->retXMLSoap($XMLAssinado, $action));
		}

		return NFSeSigepReturn::getReturn($this->soap($url, $url, $action, $this->retXMLSoap($XMLAssinado, $action)), $action, $pathFile);
	}

	public function gerarNfse(NFSeSigepInfRps $oRps){
		$url = $this->isHomologacao ? $this->homologacao: $this->producao;

		$document = new NFSeDocument();

		$GerarNfseEnvio = $document->appendChild($document->createElement("GerarNfseEnvio"));
		$GerarNfseEnvio->setAttribute("xmlns", self::XMLNS);
		$credenciais = $GerarNfseEnvio->appendChild($document->createElement("credenciais"));

		$credenciais->appendChild($document->createElement("usuario", $this->aConfig['sigep_usuario']));
		$credenciais->appendChild($document->createElement("senha", $this->aConfig['sigep_senha']));
		$credenciais->appendChild($document->createElement("chavePrivada", $this->aConfig['sigep_chavePrivada']));

		$this->addRps($document, $GerarNfseEnvio, $oRps);

		$XMLAssinado = $this->signXML(trim($document->saveXML()), "InfDeclaracaoPrestacaoServico", "Rps");
		$action = "gerarNfse";

		$pathSoapReturn = $pathFile = null;
		if(isset($this->aConfig['pathCert'])){
			file_put_contents($this->aConfig['pathCert'] . '/env_gerarNfse_' . $oRps->IdentificacaoRps->Numero . ".xml", $XMLAssinado);
			$pathFile = $this->aConfig['pathCert'] . '/env_gerarNfse_' . $oRps->IdentificacaoRps->Numero . "_ret.xml";
			file_put_contents($this->aConfig['pathCert'] . '/env_gerarNfse_soap_' . $oRps->IdentificacaoRps->Numero . ".xml", $this->retXMLSoap($XMLAssinado, $action));

			$pathSoapReturn = $this->aConfig['pathCert'] . '/env_gerarNfse_' . $oRps->IdentificacaoRps->Numero . "_ret_soap.xml";
		}

		$soapReturn = $this->soap($url, $url, $action, $this->retXMLSoap($XMLAssinado, $action));
		if(!is_null($pathSoapReturn))
			file_put_contents($pathSoapReturn, $soapReturn);

		return NFSeSigepReturn::getReturn($soapReturn, $action, $pathFile);
		//return NFSeSigepReturn::getReturn($this->curl($url, $this->retXMLSoap($XMLAssinado, $action), null, 80), $action, $pathFile);
	}


	/**
	 * Adiciona uma RPS ao lote
	 *
	 * @param NFSeDocument $document
	 * @param NFSeElement $ListaRps
	 * @param NFSeSigepInfRps $oRps
	 */
	private function addRps(NFSeDocument $document, NFSeElement $ListaRps, NFSeSigepInfRps $oRps){
		$Rps = $ListaRps->appendChild($document->createElement("Rps"));
		$InfRps = $Rps->appendChild($document->createElement("InfDeclaracaoPrestacaoServico"));

		$InfDeclPrestServRps = $InfRps->appendChild($document->createElement("Rps"));

		$IdentificacaoRps = $InfDeclPrestServRps->appendChild($document->createElement("IdentificacaoRps"));
		$IdentificacaoRps->appendChild($document->createElement("Numero", $oRps->IdentificacaoRps->Numero));
		$IdentificacaoRps->appendChild($document->createElement("Tipo", $oRps->IdentificacaoRps->Tipo));

		$InfDeclPrestServRps->appendChild($document->createElement("DataEmissao", $oRps->DataEmissao));
		$InfDeclPrestServRps->appendChild($document->createElement("Status", $oRps->Status));

		if(!empty($oRps->RpsSubstituido->Numero)){
			$RpsSubstituido = $InfDeclPrestServRps->appendChild($document->createElement("RpsSubstituido"));
			$RpsSubstituido->appendChild($document->createElement("Numero", $oRps->RpsSubstituido->Numero));
			$RpsSubstituido->appendChild($document->createElement("Tipo", $oRps->RpsSubstituido->Tipo));
		}

		$Servico = $InfRps->appendChild($document->createElement("Servico"));

		$Valores = $Servico->appendChild($document->createElement("Valores"));

		$Valores->appendChild($document->createElement("ValorServicos", number_format($oRps->Servico->Valores->ValorServicos, 2, '.', '')));

		//Quando informado o ISS e retido
		if(!empty($oRps->Servico->Valores->ValorIssRetido))
			$Valores->appendChild($document->createElement("ValorIssRetido", number_format($oRps->Servico->Valores->ValorIssRetido, 2, '.', '')));

		$Valores->appendChild($document->createElement("ValorDeducoes", number_format($oRps->Servico->Valores->ValorDeducoes, 2, '.', '')));
		$Valores->appendChild($document->createElement("ValorPis", number_format($oRps->Servico->Valores->ValorPis, 2, '.', '')));
		$Valores->appendChild($document->createElement("ValorCofins", number_format($oRps->Servico->Valores->ValorCofins, 2, '.', '')));
		$Valores->appendChild($document->createElement("ValorInss", number_format($oRps->Servico->Valores->ValorInss, 2, '.', '')));
		$Valores->appendChild($document->createElement("ValorIr", number_format($oRps->Servico->Valores->ValorIr, 2, '.', '')));
		$Valores->appendChild($document->createElement("ValorCsll", number_format($oRps->Servico->Valores->ValorCsll, 2, '.', '')));
		$Valores->appendChild($document->createElement("OutrasRetencoes", number_format($oRps->Servico->Valores->OutrasRetencoes, 2, '.', '')));
		$Valores->appendChild($document->createElement("Aliquota", number_format($oRps->Servico->Valores->Aliquota, 4, '.', '')));

		if(isset($this->aConfig['descontoIncondicionado']) && $this->aConfig['descontoIncondicionado'] == true)
			$Valores->appendChild($document->createElement("DescontoIncondicionado", number_format($oRps->Servico->Valores->DescontoIncondicionado, 2, '.', '')));

		$Valores->appendChild($document->createElement("DescontoCondicionado", number_format($oRps->Servico->Valores->DescontoCondicionado, 2, '.', '')));

		if(!empty($oRps->Servico->ItemListaServico))
			$Servico->appendChild($document->createElement("ItemListaServico", $oRps->Servico->ItemListaServico));
		$Servico->appendChild($document->createElement("CodigoCnae", $oRps->Servico->CodigoCnae));
		if(!empty($oRps->Servico->CodigoTributacaoMunicipio))
			$Servico->appendChild($document->createElement("CodigoTributacaoMunicipio", $oRps->Servico->CodigoTributacaoMunicipio));
		$Servico->appendChild($document->createElement("Discriminacao"))->appendChild($document->createCDATASection($oRps->Servico->Discriminacao));
		$Servico->appendChild($document->createElement("CodigoMunicipio", $oRps->Servico->CodigoMunicipio));
		$Servico->appendChild($document->createElement("ExigibilidadeISS", $oRps->Servico->ExigibilidadeISS));
		/*if(!empty($oRps->Servico->MunicipioIncidencia))
			$Servico->appendChild($document->createElement("MunicipioIncidencia", $oRps->Servico->MunicipioIncidencia));
		if(!empty($oRps->Servico->NumeroProcesso))
			$Servico->appendChild($document->createElement("NumeroProcesso", $oRps->Servico->NumeroProcesso));*/

		$Prestador = $InfRps->appendChild($document->createElement("Prestador"));
		$Prestador->appendChild( $document->createElement("CpfCnpj"))->appendChild($document->createElement("Cnpj", $this->aConfig['cnpj']));
		$Prestador->appendChild($document->createElement("InscricaoMunicipal", $this->aConfig['inscMunicipal']));

		$Tomador = $InfRps->appendChild($document->createElement("Tomador"));
		$IdentificacaoTomador = $Tomador->appendChild($document->createElement("IdentificacaoTomador"));
		$CpfCnpj = $IdentificacaoTomador->appendChild($document->createElement("CpfCnpj"));
		if ($oRps->Tomador->IdentificacaoTomador->getTpPessoa() == 1)
			$CpfCnpj->appendChild($document->createElement("Cnpj", $oRps->Tomador->IdentificacaoTomador->getCpfCnpj()));
		else
			$CpfCnpj->appendChild($document->createElement("Cpf", $oRps->Tomador->IdentificacaoTomador->getCpfCnpj()));

		//Inscrição Municipal só deve ser informada para pessoa jurídica
		if($oRps->Tomador->IdentificacaoTomador->getTpPessoa() == 1 && !empty($oRps->Tomador->IdentificacaoTomador->InscricaoMunicipal))
			$IdentificacaoTomador->appendChild($document->createElement("InscricaoMunicipal", $oRps->Tomador->IdentificacaoTomador->InscricaoMunicipal));

		//$Tomador->appendChild($document->createElement("NifTomador"))->appendChild($document->createCDATASection($oRps->Tomador->NifTomador));
		$Tomador->appendChild($document->createElement("RazaoSocial"))->appendChild($document->createCDATASection($oRps->Tomador->RazaoSocial));
		$Endereco = $Tomador->appendChild($document->createElement("Endereco"));

		if (!is_null($oRps->Tomador->Endereco->TipoLogradouro))
			$Endereco->appendChild($document->createElement("TipoLogradouro"))->appendChild($document->createCDATASection($oRps->Tomador->Endereco->TipoLogradouro));

		if (!is_null($oRps->Tomador->Endereco->Logradouro))
			$Endereco->appendChild($document->createElement("Logradouro"))->appendChild($document->createCDATASection($oRps->Tomador->Endereco->Logradouro));

		$Endereco->appendChild($document->createElement("Numero", $oRps->Tomador->Endereco->Numero));
		$Endereco->appendChild($document->createElement("Complemento"))->appendChild($document->createCDATASection($oRps->Tomador->Endereco->Complemento));
		$Endereco->appendChild($document->createElement("Bairro"))->appendChild($document->createCDATASection($oRps->Tomador->Endereco->Bairro));
		$Endereco->appendChild($document->createElement("CodigoMunicipio", $oRps->Tomador->Endereco->CodigoMunicipio));
		$Endereco->appendChild($document->createElement("Uf", $oRps->Tomador->Endereco->Uf));
		//if(!empty($oRps->Tomador->Endereco->CodigoPais))
		//	$Endereco->appendChild($document->createElement("CodigoPais", $oRps->Tomador->Endereco->CodigoPais));
		$Endereco->appendChild($document->createElement("Cep", $oRps->Tomador->Endereco->Cep));

		if((!empty($oRps->Tomador->Contato->Telefone) && trim($oRps->Tomador->Contato->Telefone) != "") || (!empty($oRps->Tomador->Contato->Email) && trim($oRps->Tomador->Contato->Email) != "")){

			$Contato = $Tomador->appendChild($document->createElement("Contato"));

			if (!empty($oRps->Tomador->Contato->Telefone) && trim($oRps->Tomador->Contato->Telefone) != ""){
				$Contato->appendChild($document->createElement("Telefone", $oRps->Tomador->Contato->Telefone));
				$Contato->appendChild($document->createElement("Ddd", $oRps->Tomador->Contato->Ddd));
				$Contato->appendChild($document->createElement("TipoTelefone", $oRps->Tomador->Contato->TipoTelefone));
			}

			if (!empty($oRps->Tomador->Contato->Email) && trim($oRps->Tomador->Contato->Email) != "")
				$Contato->appendChild($document->createElement("Email", $oRps->Tomador->Contato->Email));
		}

		if (!empty($oRps->IntermediarioServico->RazaoSocial)){
			$IntermediarioServico = $InfRps->appendChild($document->createElement("IntermediarioServico"));
			$CpfCnpj = $IntermediarioServico->appendChild($document->createElement("CpfCnpj"));
			if($oRps->IntermediarioServico->getTpPessoa() == 1)
				$CpfCnpj->appendChild($document->createElement("Cnpj", $oRps->IntermediarioServico->getCpfCnpj()));
			else
				$CpfCnpj->appendChild($document->createElement("Cpf", $oRps->IntermediarioServico->getCpfCnpj()));

			if(!empty($oRps->IntermediarioServico->InscricaoMunicipal) && trim($oRps->IntermediarioServico->InscricaoMunicipal) != "")
				$IntermediarioServico->appendChild($document->createElement("InscricaoMunicipal", $oRps->IntermediarioServico->InscricaoMunicipal));

			$IntermediarioServico->appendChild($document->createElement("RazaoSocial"))->appendChild($document->createCDATASection($oRps->IntermediarioServico->RazaoSocial));
			$IntermediarioServico->appendChild($document->createElement("CodigoMunicipio"))->appendChild($document->createCDATASection($oRps->IntermediarioServico->CodigoMunicipio));
		}

		if (!empty($oRps->ConstrucaoCivil->CodigoObra)){
			$ConstrucaoCivil = $InfRps->appendChild($document->createElement("ConstrucaoCivil"));
			$ConstrucaoCivil->appendChild($document->createElement("CodigoObra", $oRps->ConstrucaoCivil->CodigoObra));
			$ConstrucaoCivil->appendChild($document->createElement("CodigoObra", $oRps->ConstrucaoCivil->Art));
		}

		/*
		$InfRps->appendChild($document->createElement("RegimeEspecialTributacao", $oRps->RegimeEspecialTributacao));
		$InfRps->appendChild($document->createElement("OptanteSimplesNacional", $oRps->OptanteSimplesNacional));
		$InfRps->appendChild($document->createElement("IncentivoFiscal", $oRps->IncentivoFiscal));
		*/
	}

	/**
	 * Seta Url do servidor de produção: http://canedo.bsit-br.com.br/integracao/services/nfseWS?wsdl
	 *
	 * @param string $urlProducao
	 */
	public function setUrlWSProducao($urlProducao){
		$this->producao = $urlProducao;
	}

	/**
	 * Seta url do servidor de homologacao: http://homologacao.canedo.bsit-br.com.br/integracao/services/nfseWS?wsdl
	 *
	 * @param string $urlHomologacao
	 */
	public function setUrlWSHomologacao($urlHomologacao){
		$this->homologacao = $urlHomologacao;
	}

	public function getUrlWSProducao(){
		return $this->producao;
	}

	public function getUrlWSHomologacao(){
		return $this->homologacao;
	}

	/**
	 * Seta url do pacote SOAP: http://ws.integration.iss.bsit.com.br/
	 *
	 * @param string $urlSOAP
	 */
	public function setUrlSOAP($urlSOAP){
		$this->soap = $urlSOAP;
	}

	/**
	 * obtem a url do pacote SOAP: http://ws.integration.iss.bsit.com.br/
	 *
	 * @return string
	 */
	public function getUrlSOAP(){
		return $this->soap;
	}
}
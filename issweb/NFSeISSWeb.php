<?php
namespace NFSe\issweb;

use NFSe\NFSe;
use NFSe\NFSeDocument;
use NFSe\NFSeElement;

/**
 *
 * @since 2017-03-13
 * @author Willker Moraes Silva
 */
class NFSeISSWeb extends NFSe {

	const XMLNS = "http://www.abrasf.org.br/nfse.xsd";

	private $homologacao = "http://homologacao.canedo.bsit-br.com.br/integracao/services/nfseWS?wsdl";

	private $producao = "http://canedo.bsit-br.com.br/integracao/services/nfseWS?wsdl";

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

		parent::__construct($aConfig['privKey'], $aConfig['pubKey'], $aConfig['certKey']);
		$this->aConfig = $aConfig;
		$this->isHomologacao = $isHomologacao;
	}

	/**
	 * Cancela uma Nota Fiscal Eletrônica
	 *
	 * @param NFSeISSWebCancelarNfseEnvio $oCancelar
	 * @return string
	 */
	public function cancelaNFSe(NFSeISSWebCancelarNfseEnvio $oCancelar){

		$url = $this->isHomologacao ? $this->homologacao: $this->producao;

		$document = new NFSeDocument();

		$CancelarNfseEnvio = $document->appendChild($document->createElement("ca:CancelarNfseEnvio"));
		$CancelarNfseEnvio->setAttribute("xmlns:ds", 'http://www.w3.org/2000/09/xmldsig#');
		$CancelarNfseEnvio->setAttribute("xmlns:ca", self::XMLNS);
		$CancelarNfseEnvio->setAttribute("xmlns:xsi", 'http://www.w3.org/2001/XMLSchema-instance');
		$CancelarNfseEnvio->setAttribute("xsi:schemaLocation", 'http://www.abrasf.org.br/nfse.xsd nfse-v2.xsd ');
		$credenciais = $CancelarNfseEnvio->appendChild($document->createElement("ca:credenciais"));

		$credenciais->appendChild($document->createElement("ca:usuario", $this->aConfig['issweb_usuario']));
		$credenciais->appendChild($document->createElement("ca:senha", $this->aConfig['issweb_senha']));
		$credenciais->appendChild($document->createElement("ca:chavePrivada", $this->aConfig['issweb_chavePrivada']));

		$Pedido = $CancelarNfseEnvio->appendChild($document->createElement("ca:Pedido"));

		$InfPedidoCancelamento = $Pedido->appendChild($document->createElement("ca:InfPedidoCancelamento"));

		$IdentificacaoNfse = $InfPedidoCancelamento->appendChild($document->createElement("ca:IdentificacaoNfse"));
		$IdentificacaoNfse->appendChild($document->createElement("ca:Numero", $oCancelar->Numero));
		$CpfCnpj = $IdentificacaoNfse->appendChild($document->createElement("ca:CpfCnpj"));
		if ($oCancelar->getTpPessoa() == 0)
			$CpfCnpj->appendChild($document->createElement("ca:Cpf", $oCancelar->getCpfCnpj()));
		else
			$CpfCnpj->appendChild($document->createElement("ca:Cnpj", $oCancelar->getCpfCnpj()));

		if (!empty($oCancelar->InscricaoMunicipal))
			$IdentificacaoNfse->appendChild($document->createElement("ca:InscricaoMunicipal", $oCancelar->InscricaoMunicipal));

		$IdentificacaoNfse->appendChild($document->createElement("ca:CodigoVerificacao", $oCancelar->CodigoVerificacao));

		$InfPedidoCancelamento->appendChild($document->createElement("ca:CodigoCancelamento", $oCancelar->CodigoCancelamento));
		$InfPedidoCancelamento->appendChild($document->createElement("ca:DescricaoCancelamento", $oCancelar->DescricaoCancelamento));

		$XMLAssinado = $this->signXML(trim($document->saveXML()), "InfPedidoCancelamento", "Pedido", 'ds:');

		if(isset($this->aConfig['pathCert'])){
			file_put_contents($this->aConfig['pathCert'] . '/cancela_nfse_' . $oCancelar->Numero . ".xml", $XMLAssinado);
			$pathFile = $this->aConfig['pathCert'] . '/cancela_nfse_' . $oCancelar->Numero . "_ret.xml";
		}
		else
			$pathFile = null;

		$url = $this->isHomologacao ? $this->homologacao: $this->producao;
		$action = "cancelarNfse";

		return NFSeISSWebReturn::getReturn($this->soap($url, $url, $action, $this->retXMLSoap($XMLAssinado, $action, false)), $action, $pathFile);
	}

	/**
	 * Consulta de notas fiscais eletrônicas
	 *
	 * @param NFSeISSWebConsultarNFSe $oConsultarNFSe
	 * @return array[NFSeISSWebInfNFSe]|array[NFSeISSWebMensagemRetorno]
	 */
	public function consultaNFSe(NFSeISSWebConsultarNFSe $oConsultarNFSe){
		//FIXME: fazer consulta com padrão issweb
		$document = new NFSeDocument();
		$ConsultarNfseEnvio = $document->appendChild($document->createElement("co:ConsultarNfseEnvio"));
		$ConsultarNfseEnvio->setAttribute("xmlns:co", self::XMLNS);

		$Prestador = $ConsultarNfseEnvio->appendChild($document->createElement("co:Prestador"));
		$Prestador->appendChild($document->createElement("co:Cnpj", $this->aConfig['cnpj']));
		$Prestador->appendChild($document->createElement("co:InscricaoMunicipal", $this->aConfig['inscMunicipal']));

		if(!empty($oConsultarNFSe->NumeroNfse))
			$ConsultarNfseEnvio->appendChild($document->createElement("co:NumeroNfse", $oConsultarNFSe->NumeroNfse));

		//Periodo Emissao
		if(!empty($oConsultarNFSe->PeriodoEmissao->DataInicial)){
			$PeriodoEmissao = $ConsultarNfseEnvio->appendChild($document->createElement("co:PeriodoEmissao"));
			$PeriodoEmissao->appendChild($document->createElement("co:DataInicial", $oConsultarNFSe->PeriodoEmissao->DataInicial));
			$PeriodoEmissao->appendChild($document->createElement("co:DataFinal", $oConsultarNFSe->PeriodoEmissao->DataFinal));
		}

		//Tomador
		if($oConsultarNFSe->Tomador->getCpfCnpj() != ""){

			$IdentificacaoTomador = $ConsultarNfseEnvio->appendChild($document->createElement("co:Tomador"))->appendChild($document->createElement("co:IdentificacaoTomador"));

			$CpfCnpj = $IdentificacaoTomador->appendChild($document->createElement("co:CpfCnpj"));
			if ($oConsultarNFSe->Tomador->getTpPessoa() == 1)
				$CpfCnpj->appendChild($document->createElement("co:Cnpj", $oConsultarNFSe->Tomador->getCpfCnpj()));
			else
				$CpfCnpj->appendChild($document->createElement("co:Cpf", $oConsultarNFSe->Tomador->getCpfCnpj()));

			//Inscrição Municipal só deve ser informada para pessoa jurídica
			if($oConsultarNFSe->Tomador->getTpPessoa() == 1 && !empty($oConsultarNFSe->Tomador->InscricaoMunicipal))
				$IdentificacaoTomador->appendChild($document->createElement("co:InscricaoMunicipal", $oConsultarNFSe->Tomador->InscricaoMunicipal));
		}

		//Intermediario Servico
		if (!empty($oRps->IntermediarioServico->RazaoSocial)){

			$IntermediarioServico = $ConsultarNfseEnvio->appendChild($document->createElement("co:IntermediarioServico"));
			$IntermediarioServico->appendChild($document->createElement("co:RazaoSocial"))->appendChild($document->createCDATASection($oConsultarNFSe->IntermediarioServico->RazaoSocial));

			$CpfCnpj = $IntermediarioServico->appendChild($document->createElement("co:CpfCnpj"));
			if($oConsultarNFSe->IntermediarioServico->getTpPessoa() == 1)
				$CpfCnpj->appendChild($document->createElement("co:Cnpj", $oConsultarNFSe->IntermediarioServico->getCpfCnpj()));
			else
				$CpfCnpj->appendChild($document->createElement("co:Cpf", $oConsultarNFSe->IntermediarioServico->getCpfCnpj()));

			if(!empty($oConsultarNFSe->IntermediarioServico->InscricaoMunicipal))
				$IntermediarioServico->appendChild($document->createElement("co:InscricaoMunicipal", $oConsultarNFSe->IntermediarioServico->InscricaoMunicipal));
		}
		//echo $document->saveXML(); exit();

		$XMLAssinado = $this->signXML($document->saveXML(), "ConsultarNfseEnvio");

		$url = $this->isHomologacao ? $this->homologacao: $this->producao;
		$action = "ConsultarNfseV3";

		$return = $this->curl($url, $this->retXMLSoap($XMLAssinado, $action), array('Content-Type: text/xml'));

		return NFSeISSWebReturn::getReturn($return, $action);
	}

	/**
	 * Consulta Nota fiscal eletônica por RPS
	 *
	 * @param NFSeISSWebIdentificacaoRps $oIdentificacaoRps
	 * @return string
	 */
	public function consultarNFSePorRps(NFSeISSWebIdentificacaoRps $oIdentificacaoRps){

		$document = new NFSeDocument();
		$ConsultarNfseRpsEnvio = $document->appendChild($document->createElement("co:ConsultarNfseRpsEnvio"));
		$ConsultarNfseRpsEnvio->setAttribute("xmlns:ds", 'http://www.w3.org/2000/09/xmldsig#');
		$ConsultarNfseRpsEnvio->setAttribute("xmlns:xsi", 'http://www.w3.org/2001/XMLSchema-instance');
		$ConsultarNfseRpsEnvio->setAttribute("xsi:schemaLocation", 'http://www.abrasf.org.br/nfse.xsd nfse-v2.xsd ');
		$ConsultarNfseRpsEnvio->setAttribute("xmlns:co", self::XMLNS);

		$credenciais = $ConsultarNfseRpsEnvio->appendChild($document->createElement("co:credenciais"));
		$credenciais->appendChild($document->createElement("co:usuario", $this->aConfig['issweb_usuario']));
		$credenciais->appendChild($document->createElement("co:senha", $this->aConfig['issweb_senha']));
		$credenciais->appendChild($document->createElement("co:chavePrivada", $this->aConfig['issweb_chavePrivada']));

		$IdentificacaoRps = $ConsultarNfseRpsEnvio->appendChild($document->createElement("co:IdentificacaoRps"));
		$IdentificacaoRps->appendChild($document->createElement("co:Numero", $oIdentificacaoRps->Numero));
		$IdentificacaoRps->appendChild($document->createElement("co:Tipo", $oIdentificacaoRps->Tipo));

		$Prestador = $ConsultarNfseRpsEnvio->appendChild($document->createElement("co:Prestador"));
		$Prestador->appendChild( $document->createElement("co:CpfCnpj"))->appendChild($document->createElement("co:Cnpj", $this->aConfig['cnpj']));
		$Prestador->appendChild($document->createElement("co:InscricaoMunicipal", $this->aConfig['inscMunicipal']));

		$url = $this->isHomologacao ? $this->homologacao: $this->producao;
		$action = "consultarNfseRps";

		return NFSeISSWebReturn::getReturn($this->curl($url, $this->retXMLSoap($document->saveXML(), $action), array('Content-Type: text/xml'), 80), $action);
	}

	/**
	 * Consulta um lote RPS
	 *
	 * @param string $protocolo
	 * @return array[NFSeISSWebInfNFSe]|array[NFSeISSWebMensagemRetorno]
	 */
	public function consultarLoteRps($protocolo){

		//FIXME: Fazer metodo com o padrão ISSWEB
		$document = new NFSeDocument();
		$ConsultarLoteRpsEnvio = $document->appendChild($document->createElement("co:ConsultarLoteRpsEnvio"));
		$ConsultarLoteRpsEnvio->setAttribute("xmlns:co", self::XMLNS);

		$Prestador = $ConsultarLoteRpsEnvio->appendChild($document->createElement("co:Prestador"));
		$Prestador->appendChild($document->createElement("co:Cnpj", $this->aConfig['cnpj']));
		$Prestador->appendChild($document->createElement("co:InscricaoMunicipal", $this->aConfig['inscMunicipal']));

		$Protocolo = $ConsultarLoteRpsEnvio->appendChild($document->createElement("co:Protocolo", $protocolo));
		$XMLAssinado = $this->signXML($document->saveXML(), "ConsultarLoteRpsEnvio");

		$url = $this->isHomologacao ? $this->homologacao: $this->producao;
		$action = "ConsultarLoteRpsV3";

		$pathFile = null;
		if(isset($this->aConfig['pathCert'])){
			file_put_contents($this->aConfig['pathCert'] . '/cs_lt_' . $protocolo . ".xml", $XMLAssinado);
			$pathFile = $this->aConfig['pathCert'] . '/cs_lt_' . $protocolo . "_ret.xml";
		}

		return NFSeISSWebReturn::getReturn($this->curl($url, $this->retXMLSoap($XMLAssinado, $action), array('Content-Type: text/xml')), $action, $pathFile);
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
		$Envelope->setAttribute("xmlns:req", "http://ws.integration.iss.bsit.com.br/");


		$Body = $Envelope->appendChild($oXMLSOAP->createElement("soap:Body"));

		$req = $Body->appendChild($oXMLSOAP->createElement("req:" . $action));
		$GerarNfseEnvio = $req->appendChild($oXMLSOAP->createElement(ucwords($action) . "Envio"));
		$GerarNfseEnvio->appendChild($oXMLSOAP->createTextNode($xml));
		return $oXMLSOAP->saveXML();
	}

	/**
	 * Envia um lote de RPS
	 *
	 * @param array[NFSeISSWebInfRps] $aRps
	 * @param NFSeISSWebLoteRps $oLote
	 * @return NFSeISSWebEnviarLoteRpsResposta
	 */
	public function enviarLoteRps(array $aListaRps, NFSeISSWebLoteRps $oLote){

		//FIXME: Fazer envio de lote com o padrão ISSWEB

		$url = $this->isHomologacao ? $this->homologacao: $this->producao;

		$document = new NFSeDocument();

		$EnviarLoteRpsEnvio = $document->appendChild($document->createElement("lt:EnviarLoteRpsSincronoEnvio"));
		$EnviarLoteRpsEnvio->setAttribute("xmlns:lt", 'http://www.abrasf.org.br/nfse.xsd');
		$LoteRps = $EnviarLoteRpsEnvio->appendChild($document->createElement("lt:LoteRps"));
		//$LoteRps->setAttribute("Id", $oLote->NumeroLote);
		$LoteRps->setAttribute("versao", "1.00");

		$LoteRps->appendChild($document->createElement("lt:NumeroLote", $oLote->NumeroLote));

		$LoteRps->appendChild($document->createElement("lt:CpfCnpj"))->appendChild($document->createElement("lt:Cnpj", $this->aConfig['cnpj']));

		$LoteRps->appendChild($document->createElement("lt:InscricaoMunicipal", $this->aConfig['inscMunicipal']));
		$LoteRps->appendChild($document->createElement("lt:QuantidadeRps", count($aListaRps)));
		$ListaRps = $LoteRps->appendChild($document->createElement("lt:ListaRps"));

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

		return NFSeISSWebReturn::getReturn($this->soap($url, $url, $action, $this->retXMLSoap($XMLAssinado, $action)), $action, $pathFile);
	}

	public function gerarNfse(NFSeISSWebInfRps $oRps){
		$url = $this->isHomologacao ? $this->homologacao: $this->producao;

		$document = new NFSeDocument();

		$GerarNfseEnvio = $document->appendChild($document->createElement("lt:GerarNfseEnvio"));
		$GerarNfseEnvio->setAttribute("xmlns:ds", 'http://www.w3.org/2000/09/xmldsig#');
		$GerarNfseEnvio->setAttribute("xmlns:lt", 'http://www.abrasf.org.br/nfse.xsd');
		$GerarNfseEnvio->setAttribute("xmlns:xsi", 'http://www.w3.org/2001/XMLSchema-instance');
		$GerarNfseEnvio->setAttribute("xsi:schemaLocation", 'http://www.abrasf.org.br/nfse.xsd nfse-v2.xsd ');
		$credenciais = $GerarNfseEnvio->appendChild($document->createElement("lt:credenciais"));

		$credenciais->appendChild($document->createElement("lt:usuario", $this->aConfig['issweb_usuario']));
		$credenciais->appendChild($document->createElement("lt:senha", $this->aConfig['issweb_senha']));
		$credenciais->appendChild($document->createElement("lt:chavePrivada", $this->aConfig['issweb_chavePrivada']));

		$this->addRps($document, $GerarNfseEnvio, $oRps);

		$XMLAssinado = $this->signXML(trim($document->saveXML()), "InfDeclaracaoPrestacaoServico", "Rps", 'ds:');
		$action = "gerarNfse";

		$pathFile = null;
		if(isset($this->aConfig['pathCert'])){
			file_put_contents($this->aConfig['pathCert'] . '/env_gerarNfse_' . $oRps->IdentificacaoRps->Numero . ".xml", $XMLAssinado);
			$pathFile = $this->aConfig['pathCert'] . '/env_gerarNfse_' . $oRps->IdentificacaoRps->Numero . "_ret.xml";
			file_put_contents($this->aConfig['pathCert'] . '/env_gerarNfse_soap_' . $oRps->IdentificacaoRps->Numero . ".xml", $this->retXMLSoap($XMLAssinado, $action));
		}

		return NFSeISSWebReturn::getReturn($this->soap($url, $url, $action, $this->retXMLSoap($XMLAssinado, $action)), $action, $pathFile);
	}


	/**
	 * Adiciona uma RPS ao lote
	 *
	 * @param NFSeDocument $document
	 * @param NFSeElement $ListaRps
	 * @param NFSeISSWebInfRps $oRps
	 */
	private function addRps(NFSeDocument $document, NFSeElement $ListaRps, NFSeISSWebInfRps $oRps){
		$Rps = $ListaRps->appendChild($document->createElement("lt:Rps"));
		$InfRps = $Rps->appendChild($document->createElement("lt:InfDeclaracaoPrestacaoServico"));

		$InfDeclPrestServRps = $InfRps->appendChild($document->createElement("lt:Rps"));

		$IdentificacaoRps = $InfDeclPrestServRps->appendChild($document->createElement("lt:IdentificacaoRps"));
		$IdentificacaoRps->appendChild($document->createElement("lt:Numero", $oRps->IdentificacaoRps->Numero));
		$IdentificacaoRps->appendChild($document->createElement("lt:Tipo", $oRps->IdentificacaoRps->Tipo));

		$InfDeclPrestServRps->appendChild($document->createElement("lt:DataEmissao", $oRps->DataEmissao));
		$InfDeclPrestServRps->appendChild($document->createElement("lt:Status", $oRps->Status));

		if(!empty($oRps->RpsSubstituido->Numero)){
			$RpsSubstituido = $InfDeclPrestServRps->appendChild($document->createElement("lt:RpsSubstituido"));
			$RpsSubstituido->appendChild($document->createElement("lt:Numero", $oRps->RpsSubstituido->Numero));
			$RpsSubstituido->appendChild($document->createElement("lt:Tipo", $oRps->RpsSubstituido->Tipo));
		}

		$Servico = $InfRps->appendChild($document->createElement("lt:Servico"));

		$Valores = $Servico->appendChild($document->createElement("lt:Valores"));

		$Valores->appendChild($document->createElement("lt:ValorServicos", number_format($oRps->Servico->Valores->ValorServicos, 2, '.', '')));

		//Quando informado o ISS e retido
		if(!empty($oRps->Servico->Valores->ValorIssRetido))
			$Valores->appendChild($document->createElement("lt:ValorIssRetido", number_format($oRps->Servico->Valores->ValorIssRetido, 2, '.', '')));

		$Valores->appendChild($document->createElement("lt:ValorDeducoes", number_format($oRps->Servico->Valores->ValorDeducoes, 2, '.', '')));
		$Valores->appendChild($document->createElement("lt:ValorPis", number_format($oRps->Servico->Valores->ValorPis, 2, '.', '')));
		$Valores->appendChild($document->createElement("lt:ValorCofins", number_format($oRps->Servico->Valores->ValorCofins, 2, '.', '')));
		$Valores->appendChild($document->createElement("lt:ValorInss", number_format($oRps->Servico->Valores->ValorInss, 2, '.', '')));
		$Valores->appendChild($document->createElement("lt:ValorIr", number_format($oRps->Servico->Valores->ValorIr, 2, '.', '')));
		$Valores->appendChild($document->createElement("lt:ValorCsll", number_format($oRps->Servico->Valores->ValorCsll, 2, '.', '')));
		$Valores->appendChild($document->createElement("lt:OutrasRetencoes", number_format($oRps->Servico->Valores->OutrasRetencoes, 2, '.', '')));
		$Valores->appendChild($document->createElement("lt:Aliquota", number_format($oRps->Servico->Valores->Aliquota, 4, '.', '')));

		if(isset($this->aConfig['descontoIncondicionado']) && $this->aConfig['descontoIncondicionado'] == true)
			$Valores->appendChild($document->createElement("lt:DescontoIncondicionado", number_format($oRps->Servico->Valores->DescontoIncondicionado, 2, '.', '')));

		$Valores->appendChild($document->createElement("lt:DescontoCondicionado", number_format($oRps->Servico->Valores->DescontoCondicionado, 2, '.', '')));

		if(!empty($oRps->Servico->ItemListaServico))
			$Servico->appendChild($document->createElement("lt:ItemListaServico", $oRps->Servico->ItemListaServico));
		$Servico->appendChild($document->createElement("lt:CodigoCnae", $oRps->Servico->CodigoCnae));
		if(!empty($oRps->Servico->CodigoTributacaoMunicipio))
			$Servico->appendChild($document->createElement("lt:CodigoTributacaoMunicipio", $oRps->Servico->CodigoTributacaoMunicipio));
		$Servico->appendChild($document->createElement("lt:Discriminacao"))->appendChild($document->createCDATASection($oRps->Servico->Discriminacao));
		$Servico->appendChild($document->createElement("lt:CodigoMunicipio", $oRps->Servico->CodigoMunicipio));
		$Servico->appendChild($document->createElement("lt:ExigibilidadeISS", $oRps->Servico->ExigibilidadeISS));
		/*if(!empty($oRps->Servico->MunicipioIncidencia))
			$Servico->appendChild($document->createElement("lt:MunicipioIncidencia", $oRps->Servico->MunicipioIncidencia));
		if(!empty($oRps->Servico->NumeroProcesso))
			$Servico->appendChild($document->createElement("lt:NumeroProcesso", $oRps->Servico->NumeroProcesso));*/

		$Prestador = $InfRps->appendChild($document->createElement("lt:Prestador"));
		$Prestador->appendChild( $document->createElement("lt:CpfCnpj"))->appendChild($document->createElement("lt:Cnpj", $this->aConfig['cnpj']));
		$Prestador->appendChild($document->createElement("lt:InscricaoMunicipal", $this->aConfig['inscMunicipal']));

		$Tomador = $InfRps->appendChild($document->createElement("lt:Tomador"));
		$IdentificacaoTomador = $Tomador->appendChild($document->createElement("lt:IdentificacaoTomador"));
		$CpfCnpj = $IdentificacaoTomador->appendChild($document->createElement("lt:CpfCnpj"));
		if ($oRps->Tomador->IdentificacaoTomador->getTpPessoa() == 1)
			$CpfCnpj->appendChild($document->createElement("lt:Cnpj", $oRps->Tomador->IdentificacaoTomador->getCpfCnpj()));
		else
			$CpfCnpj->appendChild($document->createElement("lt:Cpf", $oRps->Tomador->IdentificacaoTomador->getCpfCnpj()));

		//Inscrição Municipal só deve ser informada para pessoa jurídica
		if($oRps->Tomador->IdentificacaoTomador->getTpPessoa() == 1 && !empty($oRps->Tomador->IdentificacaoTomador->InscricaoMunicipal))
			$IdentificacaoTomador->appendChild($document->createElement("lt:InscricaoMunicipal", $oRps->Tomador->IdentificacaoTomador->InscricaoMunicipal));

		//$Tomador->appendChild($document->createElement("lt:NifTomador"))->appendChild($document->createCDATASection($oRps->Tomador->NifTomador));
		$Tomador->appendChild($document->createElement("lt:RazaoSocial"))->appendChild($document->createCDATASection($oRps->Tomador->RazaoSocial));
		$Endereco = $Tomador->appendChild($document->createElement("lt:Endereco"));

		if (!is_null($oRps->Tomador->Endereco->TipoLogradouro))
			$Endereco->appendChild($document->createElement("lt:TipoLogradouro"))->appendChild($document->createCDATASection($oRps->Tomador->Endereco->TipoLogradouro));

		if (!is_null($oRps->Tomador->Endereco->Logradouro))
			$Endereco->appendChild($document->createElement("lt:Logradouro"))->appendChild($document->createCDATASection($oRps->Tomador->Endereco->Logradouro));

		$Endereco->appendChild($document->createElement("lt:Numero", $oRps->Tomador->Endereco->Numero));
		$Endereco->appendChild($document->createElement("lt:Complemento"))->appendChild($document->createCDATASection($oRps->Tomador->Endereco->Complemento));
		$Endereco->appendChild($document->createElement("lt:Bairro"))->appendChild($document->createCDATASection($oRps->Tomador->Endereco->Bairro));
		$Endereco->appendChild($document->createElement("lt:CodigoMunicipio", $oRps->Tomador->Endereco->CodigoMunicipio));
		$Endereco->appendChild($document->createElement("lt:Uf", $oRps->Tomador->Endereco->Uf));
		//if(!empty($oRps->Tomador->Endereco->CodigoPais))
		//	$Endereco->appendChild($document->createElement("lt:CodigoPais", $oRps->Tomador->Endereco->CodigoPais));
		$Endereco->appendChild($document->createElement("lt:Cep", $oRps->Tomador->Endereco->Cep));

		if((!empty($oRps->Tomador->Contato->Telefone) && trim($oRps->Tomador->Contato->Telefone) != "") || (!empty($oRps->Tomador->Contato->Email) && trim($oRps->Tomador->Contato->Email) != "")){

			$Contato = $Tomador->appendChild($document->createElement("lt:Contato"));

			if (!empty($oRps->Tomador->Contato->Telefone) && trim($oRps->Tomador->Contato->Telefone) != ""){
				$Contato->appendChild($document->createElement("lt:Telefone", $oRps->Tomador->Contato->Telefone));
				$Contato->appendChild($document->createElement("lt:Ddd", $oRps->Tomador->Contato->Ddd));
				$Contato->appendChild($document->createElement("lt:TipoTelefone", $oRps->Tomador->Contato->TipoTelefone));
			}

			if (!empty($oRps->Tomador->Contato->Email) && trim($oRps->Tomador->Contato->Email) != "")
				$Contato->appendChild($document->createElement("lt:Email", $oRps->Tomador->Contato->Email));
		}

		if (!empty($oRps->IntermediarioServico->RazaoSocial)){
			$IntermediarioServico = $InfRps->appendChild($document->createElement("lt:IntermediarioServico"));
			$CpfCnpj = $IntermediarioServico->appendChild($document->createElement("lt:CpfCnpj"));
			if($oRps->IntermediarioServico->getTpPessoa() == 1)
				$CpfCnpj->appendChild($document->createElement("lt:Cnpj", $oRps->IntermediarioServico->getCpfCnpj()));
			else
				$CpfCnpj->appendChild($document->createElement("lt:Cpf", $oRps->IntermediarioServico->getCpfCnpj()));

			if(!empty($oRps->IntermediarioServico->InscricaoMunicipal) && trim($oRps->IntermediarioServico->InscricaoMunicipal) != "")
				$IntermediarioServico->appendChild($document->createElement("lt:InscricaoMunicipal", $oRps->IntermediarioServico->InscricaoMunicipal));

			$IntermediarioServico->appendChild($document->createElement("lt:RazaoSocial"))->appendChild($document->createCDATASection($oRps->IntermediarioServico->RazaoSocial));
			$IntermediarioServico->appendChild($document->createElement("lt:CodigoMunicipio"))->appendChild($document->createCDATASection($oRps->IntermediarioServico->CodigoMunicipio));
		}

		if (!empty($oRps->ConstrucaoCivil->CodigoObra)){
			$ConstrucaoCivil = $InfRps->appendChild($document->createElement("lt:ConstrucaoCivil"));
			$ConstrucaoCivil->appendChild($document->createElement("lt:CodigoObra", $oRps->ConstrucaoCivil->CodigoObra));
			$ConstrucaoCivil->appendChild($document->createElement("lt:CodigoObra", $oRps->ConstrucaoCivil->Art));
		}

		/*
		$InfRps->appendChild($document->createElement("lt:RegimeEspecialTributacao", $oRps->RegimeEspecialTributacao));
		$InfRps->appendChild($document->createElement("lt:OptanteSimplesNacional", $oRps->OptanteSimplesNacional));
		$InfRps->appendChild($document->createElement("lt:IncentivoFiscal", $oRps->IncentivoFiscal));
		*/
	}
}
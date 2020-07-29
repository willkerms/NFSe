<?php

namespace NFSe\generico;

use NFSe\NFSe;
use NFSe\NFSeDocument;
use NFSe\NFSeElement;

class NFSeGenerico extends NFSe {

    const XMLNS_NFE = "http://www.abrasf.org.br/nfse.xsd";
    const XMLNS_WS = "http://ws.issweb.fiorilli.com.br/";
    const XMLNS_XD = "http://www.w3.org/2000/09/xmldsig";

    private $homologacao = "http://fi1.fiorilli.com.br:5663/IssWeb-ejb/IssWebWS/IssWebWS?wsdl";
    private $producao = "http://177.124.184.59:5660/IssWeb-ejb/IssWebWS/IssWebWS?wsdl";

    private $isHomologacao = true;
    private $aConfig;

    public function __construct(array $aConfig, $isHomologacao = true) {

        if (isset($aConfig['pfx']) && isset($aConfig['pwdPFX'])) {
            $this->createTempFiles($aConfig['pfx'], $aConfig['pwdPFX'], $aConfig['cnpj'], $aConfig);
        }

        parent::__construct($aConfig['privKey'], $aConfig['pubKey'], $aConfig['certKey']);
        
        $this->aConfig = $aConfig;
        $this->isHomologacao = $isHomologacao;

    }

    public function cancelarNfse(NFSeGenericoCancelarNfseEnvio $oCancelar) {

		$document = new NFSeDocument();

		$CancelarNfseEnvio = $document->appendChild($document->createElement("nfse:CancelarNfseEnvio"));
        $CancelarNfseEnvio->setAttribute("xmlns:nfse", self::XMLNS_NFE);
        $CancelarNfseEnvio->setAttribute("xmlns:xd", self::XMLNS_XD);

		$Pedido = $CancelarNfseEnvio->appendChild($document->createElement("nfse:Pedido"));

		$InfPedidoCancelamento = $Pedido->appendChild($document->createElement("nfse:InfPedidoCancelamento"));

		$IdentificacaoNfse = $InfPedidoCancelamento->appendChild($document->createElement("nfse:IdentificacaoNfse"));
		$IdentificacaoNfse->appendChild($document->createElement("nfse:Numero", $oCancelar->Numero));
		$CpfCnpj = $IdentificacaoNfse->appendChild($document->createElement("nfse:CpfCnpj"));
        
        if ($oCancelar->getTpPessoa() == 0) {
			$CpfCnpj->appendChild($document->createElement("nfse:Cpf", $oCancelar->getCpfCnpj()));
        } else {
            $CpfCnpj->appendChild($document->createElement("nfse:Cnpj", $oCancelar->getCpfCnpj()));
        }

		if (!empty($oCancelar->InscricaoMunicipal)) {
            $IdentificacaoNfse->appendChild($document->createElement("nfse:InscricaoMunicipal", $oCancelar->InscricaoMunicipal));
        }

		$IdentificacaoNfse->appendChild($document->createElement("nfse:CodigoVerificacao", $oCancelar->CodigoVerificacao));

		$InfPedidoCancelamento->appendChild($document->createElement("nfse:CodigoCancelamento", $oCancelar->CodigoCancelamento));
		$InfPedidoCancelamento->appendChild($document->createElement("nfse:DescricaoCancelamento", $oCancelar->DescricaoCancelamento));

		$XMLAssinado = $this->signXML(trim($document->saveXML()), "InfPedidoCancelamento", "Pedido", 'xd:');

		$url = $this->isHomologacao ? $this->homologacao: $this->producao;
		$action = "cancelarNfse";

        $pathSoapReturn = $pathFile = null;
        
		if(isset($this->aConfig['pathCert'])) {
            
            file_put_contents($this->aConfig['pathCert'] . '/cancela_nfse_' . $oCancelar->Numero . ".xml", $XMLAssinado);
			$pathFile = $this->aConfig['pathCert'] . '/cancela_nfse_' . $oCancelar->Numero . "_ret.xml";
            
            file_put_contents($this->aConfig['pathCert'] . '/cancela_nfse_soap_' . $oCancelar->Numero . ".xml", $this->retXMLSoap($XMLAssinado, $action, false));
            $pathSoapReturn = $this->aConfig['pathCert'] . '/cancela_nfse_' . $oCancelar->Numero . "_ret_soap.xml";
            
		}

        $url = $this->isHomologacao ? $this->homologacao: $this->producao;
		$soapReturn = $this->soap($url, $url, $action, $this->retXMLSoap($XMLAssinado, $action, true));

		if (!is_null($pathSoapReturn)) {
            file_put_contents($pathSoapReturn, $soapReturn);
        }

        return NFSeGenericoReturn::getReturn($soapReturn, $action, $pathFile);
        
    }

    public function consultarLoteRps($protocolo) {

        $document = new NFSeDocument();

        $ConsultarLoteRpsEnvio = $document->appendChild($document->createElement("nfse:ConsultarLoteRpsEnvio"));
        $ConsultarLoteRpsEnvio->setAttribute("xmlns:nfse", self::XMLNS_NFE);

        $Prestador = $ConsultarLoteRpsEnvio->appendChild($document->createElement("nfse:Prestador"));
        
        $CpfCnpj = $Prestador->appendChild($document->createElement("nfse:CpfCnpj"));
        $CpfCnpj->appendChild($document->createElement("nfse:Cnpj", $this->aConfig['cnpj']));
        $Prestador->appendChild($document->createElement("nfse:InscricaoMunicipal", $this->aConfig['inscMunicipal']));

        $ConsultarLoteRpsEnvio->appendChild($document->createElement("nfse:Protocolo", $protocolo));

        $url = $this->isHomologacao ? $this->homologacao: $this->producao;
        $action = "ConsultarLoteRps";

        $soap = $this->retXMLSoap($document->saveXML(), $action, true);
        $return = $this->curl($url, $soap, array('Content-Type: text/xml'), 80);

        return NFSeGenericoReturn::getReturn($return, $action);
     
    }

    public function consultarNfsePorFaixa($iNumeroNfseInicial, $iNumeroNfseFinal) {

        $document = new NFSeDocument();

        $ConsultarNfseFaixaEnvio = $document->appendChild($document->createElement("nfse:ConsultarNfseFaixaEnvio"));
        $ConsultarNfseFaixaEnvio->setAttribute("xmlns:nfse", self::XMLNS_NFE);

        $Prestador = $ConsultarNfseFaixaEnvio->appendChild($document->createElement("nfse:Prestador"));
        
        $CpfCnpj = $Prestador->appendChild($document->createElement("nfse:CpfCnpj"));
        $CpfCnpj->appendChild($document->createElement("nfse:Cnpj", $this->aConfig['cnpj']));
        $Prestador->appendChild($document->createElement("nfse:InscricaoMunicipal", $this->aConfig['inscMunicipal']));

        $Faixa = $ConsultarNfseFaixaEnvio->appendChild($document->createElement("nfse:Faixa"));

        $Faixa->appendChild($document->createElement("nfse:NumeroNfseInicial", $iNumeroNfseInicial));
        $Faixa->appendChild($document->createElement("nfse:NumeroNfseFinal", $iNumeroNfseFinal));

        $ConsultarNfseFaixaEnvio->appendChild($document->createElement("nfse:Pagina"), 1);

        $url = $this->isHomologacao ? $this->homologacao: $this->producao;
        $action = "ConsultarNfseFaixa";

        $soap = $this->retXMLSoap($document->saveXML(), $action, true);
        $return = $this->curl($url, $soap, array('Content-Type: text/xml'), 80);

        return NFSeGenericoReturn::getReturn($return, $action);
     
    }

    public function consultarNfseServicoPrestado(NFSeGenericoConsultarNFSe $oConsultarNFSe) {

        $document = new NFSeDocument();

        $ConsultarNfseEnvio = $document->appendChild($document->createElement("nfse:ConsultarNfseServicoPrestadoEnvio"));
        $ConsultarNfseEnvio->setAttribute("xmlns:nfse", self::XMLNS_NFE);

        $Prestador = $ConsultarNfseEnvio->appendChild($document->createElement("nfse:Prestador"));
        
        $CpfCnpj = $Prestador->appendChild($document->createElement("nfse:CpfCnpj"));
        $CpfCnpj->appendChild($document->createElement("nfse:Cnpj", $this->aConfig['cnpj']));
        $Prestador->appendChild($document->createElement("nfse:InscricaoMunicipal", $this->aConfig['inscMunicipal']));

        if(!empty($oConsultarNFSe->NumeroNfse)) {
            $ConsultarNfseEnvio->appendChild($document->createElement("nfse:NumeroNfse", $oConsultarNFSe->NumeroNfse));
        }

        //Periodo Emissao
        if(!empty($oConsultarNFSe->PeriodoEmissao->DataInicial)){
            $PeriodoEmissao = $ConsultarNfseEnvio->appendChild($document->createElement("nfse:PeriodoEmissao"));
            $PeriodoEmissao->appendChild($document->createElement("nfse:DataInicial", $oConsultarNFSe->PeriodoEmissao->DataInicial));
            $PeriodoEmissao->appendChild($document->createElement("nfse:DataFinal", $oConsultarNFSe->PeriodoEmissao->DataFinal));
        }

        //Tomador
        if($oConsultarNFSe->Tomador->getCpfCnpj() != ""){

            $IdentificacaoTomador = $ConsultarNfseEnvio->appendChild($document->createElement("nfse:Tomador"));

            $CpfCnpj = $IdentificacaoTomador->appendChild($document->createElement("nfse:CpfCnpj"));
            
            if ($oConsultarNFSe->Tomador->getTpPessoa() == 1) {
                $CpfCnpj->appendChild($document->createElement("nfse:Cnpj", $oConsultarNFSe->Tomador->getCpfCnpj()));
            } else {
                $CpfCnpj->appendChild($document->createElement("nfse:Cpf", $oConsultarNFSe->Tomador->getCpfCnpj()));
            }

            //Inscriçãoo Municipal só deve ser informada para pessoa jurídica
            if($oConsultarNFSe->Tomador->getTpPessoa() == 1 && !empty($oConsultarNFSe->Tomador->InscricaoMunicipal)) {
                $IdentificacaoTomador->appendChild($document->createElement("nfse:InscricaoMunicipal", $oConsultarNFSe->Tomador->InscricaoMunicipal));
            }

        }

        //Intermediario
        if($oConsultarNFSe->IntermediarioServico->getCpfCnpj() != "") {

            $IdentificacaoIntermediario = $ConsultarNfseEnvio->appendChild($document->createElement("nfse:Tomador"));

            $CpfCnpj = $IdentificacaoIntermediario->appendChild($document->createElement("nfse:CpfCnpj"));
            
            if ($oConsultarNFSe->Tomador->getTpPessoa() == 1) {
                $CpfCnpj->appendChild($document->createElement("nfse:Cnpj", $oConsultarNFSe->Tomador->getCpfCnpj()));
            } else {
                $CpfCnpj->appendChild($document->createElement("nfse:Cpf", $oConsultarNFSe->Tomador->getCpfCnpj()));
            }

            //Inscriçãoo Municipal só deve ser informada para pessoa jurídica
            if($oConsultarNFSe->Tomador->getTpPessoa() == 1 && !empty($oConsultarNFSe->Tomador->InscricaoMunicipal)) {
                $IdentificacaoIntermediario->appendChild($document->createElement("nfse:InscricaoMunicipal", $oConsultarNFSe->Tomador->InscricaoMunicipal));
            }

        }
        
        $ConsultarNfseEnvio->appendChild($document->createElement("nfse:Pagina", 1));

        $url = $this->isHomologacao ? $this->homologacao: $this->producao;
        $action = "consultarNfseServicoPrestado";

        $soap = $this->retXMLSoap($document->saveXML(), $action, true);
        $return = $this->curl($url, $soap, array('Content-Type: text/xml'), 80);

        return NFSeGenericoReturn::getReturn($return, $action);

    }

    public function gerarNfse(NFSeGenericoInfRps $oRps) {
        
        $document = new NFSeDocument();

        $GerarNfseEnvio = $document->appendChild($document->createElement("nfse:GerarNfseEnvio"));
        $GerarNfseEnvio->setAttribute("xmlns:nfse", self::XMLNS_NFE);
        $GerarNfseEnvio->setAttribute("xmlns:xd", self::XMLNS_XD);

        $Rps = $GerarNfseEnvio->appendChild($document->createElement("nfse:Rps"));
        $InfRps = $Rps->appendChild($document->createElement("nfse:InfDeclaracaoPrestacaoServico"));

        $InfDeclPrestServRps = $InfRps->appendChild($document->createElement("nfse:Rps"));

        $IdentificacaoRps = $InfDeclPrestServRps->appendChild($document->createElement("nfse:IdentificacaoRps"));
        $IdentificacaoRps->appendChild($document->createElement("nfse:Numero", $oRps->IdentificacaoRps->Numero));
        $IdentificacaoRps->appendChild($document->createElement("nfse:Tipo", $oRps->IdentificacaoRps->Tipo));

        $InfDeclPrestServRps->appendChild($document->createElement("nfse:DataEmissao", $oRps->DataEmissao));
        $InfDeclPrestServRps->appendChild($document->createElement("nfse:Status", $oRps->Status));

        if(!empty($oRps->RpsSubstituido->Numero)) {
            $RpsSubstituido = $InfDeclPrestServRps->appendChild($document->createElement("nfse:RpsSubstituido"));
            $RpsSubstituido->appendChild($document->createElement("nfse:Numero", $oRps->RpsSubstituido->Numero));
            $RpsSubstituido->appendChild($document->createElement("nfse:Tipo", $oRps->RpsSubstituido->Tipo));            
        }

        $Servico = $InfRps->appendChild($document->createElement("nfse:Servico"));

        $Valores = $Servico->appendChild($document->createElement("nfse:Valores"));
        $Valores->appendChild($document->createElement("nfse:ValorServicos", number_format($oRps->Servico->Valores->ValorServicos, 2, '.', '')));

        if(!empty($oRps->Servico->Valores->ValorIssRetido)) {
            $Valores->appendChild($document->createElement("nfse:ValorIssRetido", number_format($oRps->Servico->Valores->ValorIssRetido, 2, '.', '')));
        }

        $Valores->appendChild($document->createElement("nfse:ValorDeducoes", number_format($oRps->Servico->Valores->ValorDeducoes, 2, '.', '')));
        $Valores->appendChild($document->createElement("nfse:ValorPis", number_format($oRps->Servico->Valores->ValorPis, 2, '.', '')));
        $Valores->appendChild($document->createElement("nfse:ValorCofins", number_format($oRps->Servico->Valores->ValorCofins, 2, '.', '')));
        $Valores->appendChild($document->createElement("nfse:ValorInss", number_format($oRps->Servico->Valores->ValorInss, 2, '.', '')));
        $Valores->appendChild($document->createElement("nfse:ValorIr", number_format($oRps->Servico->Valores->ValorIr, 2, '.', '')));
        $Valores->appendChild($document->createElement("nfse:ValorCsll", number_format($oRps->Servico->Valores->ValorCsll, 2, '.', '')));
        $Valores->appendChild($document->createElement("nfse:OutrasRetencoes", number_format($oRps->Servico->Valores->OutrasRetencoes, 2, '.', '')));
        $Valores->appendChild($document->createElement("nfse:Aliquota", number_format($oRps->Servico->Valores->Aliquota, 4, '.', '')));

        if(isset($this->aConfig['descontoIncondicionado']) && $this->aConfig['descontoIncondicionado'] == true) {
            $Valores->appendChild($document->createElement("nfse:DescontoIncondicionado", number_format($oRps->Servico->Valores->DescontoIncondicionado, 2, '.', '')));
        }

        $Valores->appendChild($document->createElement("nfse:DescontoCondicionado", number_format($oRps->Servico->Valores->DescontoCondicionado, 2, '.', '')));

        if(!empty($oRps->Servico->ItemListaServico)){
            $Servico->appendChild($document->createElement("nfse:ItemListaServico", $oRps->Servico->ItemListaServico));
        }

        $Servico->appendChild($document->createElement("nfse:CodigoCnae", $oRps->Servico->CodigoCnae));
        
        if(!empty($oRps->Servico->CodigoTributacaoMunicipio)) {
            $Servico->appendChild($document->createElement("nfse:CodigoTributacaoMunicipio", $oRps->Servico->CodigoTributacaoMunicipio));
        }
        
        $Servico->appendChild($document->createElement("nfse:Discriminacao"))->appendChild($document->createCDATASection($oRps->Servico->Discriminacao));
        $Servico->appendChild($document->createElement("nfse:CodigoMunicipio", $oRps->Servico->CodigoMunicipio));
        $Servico->appendChild($document->createElement("nfse:ExigibilidadeISS", $oRps->Servico->ExigibilidadeISS));

        $Prestador = $InfRps->appendChild($document->createElement("nfse:Prestador"));
        $Prestador->appendChild( $document->createElement("nfse:CpfCnpj"))->appendChild($document->createElement("nfse:Cnpj", $this->aConfig['cnpj']));
        $Prestador->appendChild($document->createElement("nfse:InscricaoMunicipal", $this->aConfig['inscMunicipal']));

        $Tomador = $InfRps->appendChild($document->createElement("nfse:Tomador"));
        $IdentificacaoTomador = $Tomador->appendChild($document->createElement("nfse:IdentificacaoTomador"));
        $CpfCnpj = $IdentificacaoTomador->appendChild($document->createElement("nfse:CpfCnpj"));
        
        if ($oRps->Tomador->IdentificacaoTomador->getTpPessoa() == 1) {
            $CpfCnpj->appendChild($document->createElement("nfse:Cnpj", $oRps->Tomador->IdentificacaoTomador->getCpfCnpj()));
        } else {
            $CpfCnpj->appendChild($document->createElement("nfse:Cpf", $oRps->Tomador->IdentificacaoTomador->getCpfCnpj()));
        }

        if($oRps->Tomador->IdentificacaoTomador->getTpPessoa() == 1 
            && !empty($oRps->Tomador->IdentificacaoTomador->InscricaoMunicipal)) {

            $IdentificacaoTomador->appendChild($document->createElement("nfse:InscricaoMunicipal", $oRps->Tomador->IdentificacaoTomador->InscricaoMunicipal));
            
        }

        $Tomador->appendChild($document->createElement("nfse:RazaoSocial"))->appendChild($document->createCDATASection($oRps->Tomador->RazaoSocial));
        $Endereco = $Tomador->appendChild($document->createElement("nfse:Endereco"));

        if (!is_null($oRps->Tomador->Endereco->Logradouro)) {
            $Endereco->appendChild($document->createElement("nfse:Endereco"))->appendChild($document->createCDATASection($oRps->Tomador->Endereco->Logradouro));
        }

        $Endereco->appendChild($document->createElement("nfse:Numero", $oRps->Tomador->Endereco->Numero));
        $Endereco->appendChild($document->createElement("nfse:Complemento"))->appendChild($document->createCDATASection($oRps->Tomador->Endereco->Complemento));
        $Endereco->appendChild($document->createElement("nfse:Bairro"))->appendChild($document->createCDATASection($oRps->Tomador->Endereco->Bairro));
        $Endereco->appendChild($document->createElement("nfse:CodigoMunicipio", $oRps->Tomador->Endereco->CodigoMunicipio));
        $Endereco->appendChild($document->createElement("nfse:Uf", $oRps->Tomador->Endereco->Uf));
        $Endereco->appendChild($document->createElement("nfse:Cep", $oRps->Tomador->Endereco->Cep));

        if((!empty($oRps->Tomador->Contato->Telefone) 
            && trim($oRps->Tomador->Contato->Telefone) != "") 
            || (!empty($oRps->Tomador->Contato->Email) 
            && trim($oRps->Tomador->Contato->Email) != "")) {

            $Contato = $Tomador->appendChild($document->createElement("nfse:Contato"));

            if (!empty($oRps->Tomador->Contato->Telefone) && trim($oRps->Tomador->Contato->Telefone) != "") {
                $Contato->appendChild($document->createElement("nfse:Telefone", $oRps->Tomador->Contato->Ddd . $oRps->Tomador->Contato->Telefone));
            }

            if (!empty($oRps->Tomador->Contato->Email) && trim($oRps->Tomador->Contato->Email) != "") {
                $Contato->appendChild($document->createElement("nfse:Email", $oRps->Tomador->Contato->Email));
            }

        }

        if (!empty($oRps->IntermediarioServico->RazaoSocial)) {

            $IntermediarioServico = $InfRps->appendChild($document->createElement("nfse:IntermediarioServico"));
            $CpfCnpj = $IntermediarioServico->appendChild($document->createElement("nfse:CpfCnpj"));
            
            if($oRps->IntermediarioServico->getTpPessoa() == 1) {
                $CpfCnpj->appendChild($document->createElement("nfse:Cnpj", $oRps->IntermediarioServico->getCpfCnpj()));
            } else {
                $CpfCnpj->appendChild($document->createElement("nfse:Cpf", $oRps->IntermediarioServico->getCpfCnpj()));
            }

            if(!empty($oRps->IntermediarioServico->InscricaoMunicipal) && trim($oRps->IntermediarioServico->InscricaoMunicipal) != "") {
                $IntermediarioServico->appendChild($document->createElement("nfse:InscricaoMunicipal", $oRps->IntermediarioServico->InscricaoMunicipal));
            }

            $IntermediarioServico->appendChild($document->createElement("nfse:RazaoSocial"))->appendChild($document->createCDATASection($oRps->IntermediarioServico->RazaoSocial));
            $IntermediarioServico->appendChild($document->createElement("nfse:CodigoMunicipio"))->appendChild($document->createCDATASection($oRps->IntermediarioServico->CodigoMunicipio));
            
        }

        if (!empty($oRps->ConstrucaoCivil->CodigoObra)) {

            $ConstrucaoCivil = $InfRps->appendChild($document->createElement("nfse:ConstrucaoCivil"));
            $ConstrucaoCivil->appendChild($document->createElement("nfse:CodigoObra", $oRps->ConstrucaoCivil->CodigoObra));
            $ConstrucaoCivil->appendChild($document->createElement("nfse:CodigoObra", $oRps->ConstrucaoCivil->Art));
            
        }

        $XMLAssinado = $this->signXML(trim($document->saveXML()), "InfDeclaracaoPrestacaoServico", "Rps", 'xd:');

        $pathSoapReturn = $pathFile = null;
        
        $action = "gerarNfse";
        
        if(isset($this->aConfig['pathCert'])) {
            
            file_put_contents($this->aConfig['pathCert'] . '/env_gerarNfse_' . $oRps->IdentificacaoRps->Numero . ".xml", $XMLAssinado);
			$pathFile = $this->aConfig['pathCert'] . '/env_gerarNfse_' . $oRps->IdentificacaoRps->Numero . "_ret.xml";
            
            file_put_contents($this->aConfig['pathCert'] . '/env_gerarNfse_soap_' . $oRps->IdentificacaoRps->Numero . ".xml", $this->retXMLSoap($XMLAssinado, $action));
            $pathSoapReturn = $this->aConfig['pathCert'] . '/env_gerarNfse_' . $oRps->IdentificacaoRps->Numero . "_ret_soap.xml";
            
		}

        $url = $this->isHomologacao ? $this->homologacao: $this->producao;

		$soapReturn = $this->soap($url, $url, $action, $this->retXMLSoap($XMLAssinado, $action, true));
        
        if(!is_null($pathSoapReturn)) {
            file_put_contents($pathSoapReturn, $soapReturn);
        }

		return NFSeGenericoReturn::getReturn($soapReturn, $action, $pathFile);

    }

    private function addRps(NFSeDocument $document, NFSeElement $ListaRps, NFSeGenericoInfRps $oRps){
        
        $Rps = $ListaRps->appendChild($document->createElement("Rps"));
        $InfRps = $Rps->appendChild($document->createElement("InfDeclaracaoPrestacaoServico"));

        $InfDeclPrestServRps = $InfRps->appendChild($document->createElement("Rps"));

        $IdentificacaoRps = $InfDeclPrestServRps->appendChild($document->createElement("IdentificacaoRps"));
        $IdentificacaoRps->appendChild($document->createElement("Numero", $oRps->IdentificacaoRps->Numero));
        $IdentificacaoRps->appendChild($document->createElement("Tipo", $oRps->IdentificacaoRps->Tipo));
        $InfDeclPrestServRps->appendChild($document->createElement("DataEmissao", $oRps->DataEmissao));
        $InfDeclPrestServRps->appendChild($document->createElement("Status", $oRps->Status));

        if(!empty($oRps->RpsSubstituido->Numero)) {
            $RpsSubstituido = $InfDeclPrestServRps->appendChild($document->createElement("RpsSubstituido"));
            $RpsSubstituido->appendChild($document->createElement("Numero", $oRps->RpsSubstituido->Numero));
            $RpsSubstituido->appendChild($document->createElement("Tipo", $oRps->RpsSubstituido->Tipo));
        }

        $Servico = $InfRps->appendChild($document->createElement("Servico"));
        $Valores = $Servico->appendChild($document->createElement("Valores"));
        $Valores->appendChild($document->createElement("ValorServicos", number_format($oRps->Servico->Valores->ValorServicos, 2, '.', '')));

        //Quando informado o ISS e retido
        if(!empty($oRps->Servico->Valores->ValorIssRetido)) {
            $Valores->appendChild($document->createElement("ValorIssRetido", number_format($oRps->Servico->Valores->ValorIssRetido, 2, '.', '')));
        }

        $Valores->appendChild($document->createElement("ValorDeducoes", number_format($oRps->Servico->Valores->ValorDeducoes, 2, '.', '')));
        $Valores->appendChild($document->createElement("ValorPis", number_format($oRps->Servico->Valores->ValorPis, 2, '.', '')));
        $Valores->appendChild($document->createElement("ValorCofins", number_format($oRps->Servico->Valores->ValorCofins, 2, '.', '')));
        $Valores->appendChild($document->createElement("ValorInss", number_format($oRps->Servico->Valores->ValorInss, 2, '.', '')));
        $Valores->appendChild($document->createElement("ValorIr", number_format($oRps->Servico->Valores->ValorIr, 2, '.', '')));
        $Valores->appendChild($document->createElement("ValorCsll", number_format($oRps->Servico->Valores->ValorCsll, 2, '.', '')));
        $Valores->appendChild($document->createElement("OutrasRetencoes", number_format($oRps->Servico->Valores->OutrasRetencoes, 2, '.', '')));
        $Valores->appendChild($document->createElement("Aliquota", number_format($oRps->Servico->Valores->Aliquota, 4, '.', '')));

        if(isset($this->aConfig['descontoIncondicionado']) && $this->aConfig['descontoIncondicionado'] == true) {
            $Valores->appendChild($document->createElement("DescontoIncondicionado", number_format($oRps->Servico->Valores->DescontoIncondicionado, 2, '.', '')));
        }

        $Valores->appendChild($document->createElement("DescontoCondicionado", number_format($oRps->Servico->Valores->DescontoCondicionado, 2, '.', '')));

        if(!empty($oRps->Servico->ItemListaServico)) {
            $Servico->appendChild($document->createElement("ItemListaServico", $oRps->Servico->ItemListaServico));
        }

        $Servico->appendChild($document->createElement("CodigoCnae", $oRps->Servico->CodigoCnae));
        
        if(!empty($oRps->Servico->CodigoTributacaoMunicipio)) {
            $Servico->appendChild($document->createElement("CodigoTributacaoMunicipio", $oRps->Servico->CodigoTributacaoMunicipio));
        }

        $Servico->appendChild($document->createElement("Discriminacao"))->appendChild($document->createCDATASection($oRps->Servico->Discriminacao));
        $Servico->appendChild($document->createElement("CodigoMunicipio", $oRps->Servico->CodigoMunicipio));
        $Servico->appendChild($document->createElement("ExigibilidadeISS", $oRps->Servico->ExigibilidadeISS));

        $Prestador = $InfRps->appendChild($document->createElement("Prestador"));
        $Prestador->appendChild( $document->createElement("CpfCnpj"))->appendChild($document->createElement("Cnpj", $this->aConfig['cnpj']));
        $Prestador->appendChild($document->createElement("InscricaoMunicipal", $this->aConfig['inscMunicipal']));

        $Tomador = $InfRps->appendChild($document->createElement("Tomador"));
        $IdentificacaoTomador = $Tomador->appendChild($document->createElement("IdentificacaoTomador"));
        $CpfCnpj = $IdentificacaoTomador->appendChild($document->createElement("CpfCnpj"));
        
        if ($oRps->Tomador->IdentificacaoTomador->getTpPessoa() == 1) {
            $CpfCnpj->appendChild($document->createElement("Cnpj", $oRps->Tomador->IdentificacaoTomador->getCpfCnpj()));
        } else {
            $CpfCnpj->appendChild($document->createElement("Cpf", $oRps->Tomador->IdentificacaoTomador->getCpfCnpj()));
        }

        if($oRps->Tomador->IdentificacaoTomador->getTpPessoa() == 1 && !empty($oRps->Tomador->IdentificacaoTomador->InscricaoMunicipal)) {
            $IdentificacaoTomador->appendChild($document->createElement("InscricaoMunicipal", $oRps->Tomador->IdentificacaoTomador->InscricaoMunicipal));
        }

        $Tomador->appendChild($document->createElement("RazaoSocial"))->appendChild($document->createCDATASection($oRps->Tomador->RazaoSocial));
        $Endereco = $Tomador->appendChild($document->createElement("Endereco"));

        if (!is_null($oRps->Tomador->Endereco->TipoLogradouro)) {
            $Endereco->appendChild($document->createElement("TipoLogradouro"))->appendChild($document->createCDATASection($oRps->Tomador->Endereco->TipoLogradouro));
        }

        if (!is_null($oRps->Tomador->Endereco->Logradouro)) {
            $Endereco->appendChild($document->createElement("Logradouro"))->appendChild($document->createCDATASection($oRps->Tomador->Endereco->Logradouro));
        }

        $Endereco->appendChild($document->createElement("Numero", $oRps->Tomador->Endereco->Numero));
        $Endereco->appendChild($document->createElement("Complemento"))->appendChild($document->createCDATASection($oRps->Tomador->Endereco->Complemento));
        $Endereco->appendChild($document->createElement("Bairro"))->appendChild($document->createCDATASection($oRps->Tomador->Endereco->Bairro));
        $Endereco->appendChild($document->createElement("CodigoMunicipio", $oRps->Tomador->Endereco->CodigoMunicipio));
        $Endereco->appendChild($document->createElement("Uf", $oRps->Tomador->Endereco->Uf));
        $Endereco->appendChild($document->createElement("Cep", $oRps->Tomador->Endereco->Cep));

        if((!empty($oRps->Tomador->Contato->Telefone) && trim($oRps->Tomador->Contato->Telefone) != "") 
            || (!empty($oRps->Tomador->Contato->Email) && trim($oRps->Tomador->Contato->Email) != "")) {

            $Contato = $Tomador->appendChild($document->createElement("Contato"));

            if (!empty($oRps->Tomador->Contato->Telefone) && trim($oRps->Tomador->Contato->Telefone) != "") {
                $Contato->appendChild($document->createElement("Telefone", $oRps->Tomador->Contato->Telefone));
                $Contato->appendChild($document->createElement("Ddd", $oRps->Tomador->Contato->Ddd));
                $Contato->appendChild($document->createElement("TipoTelefone", $oRps->Tomador->Contato->TipoTelefone));
            }

            if (!empty($oRps->Tomador->Contato->Email) && trim($oRps->Tomador->Contato->Email) != "") {
                $Contato->appendChild($document->createElement("Email", $oRps->Tomador->Contato->Email));
            }

        }

        if (!empty($oRps->IntermediarioServico->RazaoSocial)) {

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

    }

    private function retXMLSoap($xml, $action, $appendUser = false) {

        $oXMLSOAP = new NFSeDocument();

        $Envelope = $oXMLSOAP->appendChild($oXMLSOAP->createElement("soapenv:Envelope"));
        $Envelope->setAttribute("xmlns:soapenv", "http://schemas.xmlsoap.org/soap/envelope/");
        $Envelope->setAttribute("xmlns:ws", self::XMLNS_WS);
        $Envelope->setAttribute("xmlns:nfse", self::XMLNS_NFE);

        $Envelope->appendChild($oXMLSOAP->createElement("soapenv:Header"));

        $Body = $Envelope->appendChild($oXMLSOAP->createElement("soapenv:Body"));

        $soapAction = $oXMLSOAP->createElement("ws:" . $action);

        $xmldoc = new NFSeDocument();
        $xmldoc->preservWhiteSpace = false; // elimina espaços em branco
        $xmldoc->formatOutput = false;
        $xmldoc->loadXML($xml, LIBXML_NOBLANKS | LIBXML_NOEMPTYTAG);

        $nodeDoc = $oXMLSOAP->importNode($xmldoc->getElementsByTagName(ucwords($action) . "Envio")->item(0), true);
        $soapAction->appendChild($nodeDoc);

        if($appendUser) {
            $soapAction->appendChild($oXMLSOAP->createElement("username", $this->aConfig['usuario']));
            $soapAction->appendChild($oXMLSOAP->createElement("password", $this->aConfig['senha']));
        }

        $Body->appendChild($soapAction);
        $Envelope->appendChild($Body);
        
        return $oXMLSOAP->saveXML();
        
    }
    
    public function setUrlWSProducao($urlProducao){
        $this->producao = $urlProducao;
    }

    public function setUrlWSHomologacao($urlHomologacao){
        $this->homologacao = $urlHomologacao;
    }

    public function getUrlWSProducao(){
        return $this->producao;
    }

    public function getUrlWSHomologacao(){
        return $this->homologacao;
    }

    public function setUrlSOAP($urlSOAP){
        $this->soap = $urlSOAP;
    }

    public function getUrlSOAP(){
        return $this->soap;
    }

}

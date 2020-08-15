<?php

namespace NFSe\generico;

use NFSe\NFSe;
use NFSe\NFSeDocument;

class NFSeGenerico extends NFSe {

    const XMLNS_URI = "http://www.w3.org/2000/xmlns/";
 
    private $sServiceNS = null;
    private $sServiceNSPrefix = null;
    private $sNfseNS = null;
    private $sNfseNsPrefix = null;

    private $bPrintXml = false;
    private $isHomologacao = true;
    private $aConfig;
    
    public function __construct(array $aConfig, $isHomologacao = true) {

        if (isset($aConfig['pfx']) && isset($aConfig['pwdPFX'])) {
            $this->createTempFiles($aConfig['pfx'], $aConfig['pwdPFX'], $aConfig['cnpj'], $aConfig);
        }

        parent::__construct($aConfig['privKey'], $aConfig['pubKey'], $aConfig['certKey']);
        
        $this->aConfig = $aConfig;
        $this->isHomologacao = $isHomologacao;

        if(isset($aConfig['serviceNS']) && isset($aConfig['serviceNSPrefix'])) {
            $this->sServiceNS = $aConfig['serviceNS'];
            $this->sServiceNSPrefix = $aConfig['serviceNSPrefix'];
        }

        if(isset($aConfig['nfseNS']) && isset($aConfig['nfseNsPrefix'])) {
            $this->sNfseNS = $aConfig['nfseNS'];
            $this->sNfseNsPrefix = $aConfig['nfseNsPrefix'];
        }

    }

    public function cancelarNfse(NFSeGenericoCancelarNfseEnvio $oCancelar) {

        $document = new NFSeDocument();

        $CancelarNfseEnvioElement = $document->createElementNS($this->sNfseNS, "CancelarNfseEnvio");
        $CancelarNfseEnvioElement->setAttributeNS(self::XMLNS_URI, "xmlns:xd", "http://www.w3.org/2000/09/xmldsig#");

        $CancelarNfseEnvio = $document->appendChild(
            $CancelarNfseEnvioElement
        );
 
        $Pedido = $CancelarNfseEnvio->appendChild(
            $document->createElementNS($this->sNfseNS, "Pedido")
        );
        
        $InfPedidoCancelamento = $Pedido->appendChild(
            $document->createElementNS($this->sNfseNS, "InfPedidoCancelamento")
        );
        
        $IdentificacaoNfse = $InfPedidoCancelamento->appendChild(
            $document->createElementNS($this->sNfseNS, "IdentificacaoNfse")
        );

        $IdentificacaoNfse->appendChild(
            $document->createElementNS($this->sNfseNS, "Numero", $oCancelar->Numero)
        );

        $CpfCnpj = $IdentificacaoNfse->appendChild(
            $document->createElementNS($this->sNfseNS, "CpfCnpj")
        );
        
        if ($oCancelar->getTpPessoa() == 0) {
            
            $CpfCnpj->appendChild(
                $document->createElementNS($this->sNfseNS, "Cpf", $oCancelar->getCpfCnpj())
            );

        } else {

            $CpfCnpj->appendChild(
                $document->createElementNS($this->sNfseNS, "Cnpj", $oCancelar->getCpfCnpj())
            );

        }
        
        if (!empty($oCancelar->InscricaoMunicipal)) {

            $IdentificacaoNfse->appendChild(
                $document->createElementNS($this->sNfseNS, "InscricaoMunicipal", $oCancelar->InscricaoMunicipal)
            );

        }
        
        $IdentificacaoNfse->appendChild(
            $document->createElementNS($this->sNfseNS, "CodigoVerificacao", $oCancelar->CodigoVerificacao)
        );
        
        $InfPedidoCancelamento->appendChild(
            $document->createElementNS($this->sNfseNS, "CodigoCancelamento", $oCancelar->CodigoCancelamento)
        );

        $InfPedidoCancelamento->appendChild(
            $document->createElementNS($this->sNfseNS, "DescricaoCancelamento", $oCancelar->DescricaoCancelamento)
        );
         
        $XMLAssinado = $this->signXML(trim($document->saveXML()), "InfPedidoCancelamento", "Pedido", 'xd:');

        $pathSoapReturn = $pathFile = null;
        
        $action = "cancelarNfse";

        if(isset($this->aConfig['pathCert'])) {
            
            file_put_contents($this->aConfig['pathCert'] . '/cancela_nfse_' . $oCancelar->Numero . ".xml", $XMLAssinado);
            $pathFile = $this->aConfig['pathCert'] . '/cancela_nfse_' . $oCancelar->Numero . "_ret.xml";
            
            file_put_contents($this->aConfig['pathCert'] . '/cancela_nfse_soap_' . $oCancelar->Numero . ".xml", $this->retXMLSoap($XMLAssinado, $action, false));
            $pathSoapReturn = $this->aConfig['pathCert'] . '/cancela_nfse_' . $oCancelar->Numero . "_ret_soap.xml";
            
        }
        
        if($this->bPrintXml) {

            echo $this->retXMLSoap($XMLAssinado, $action, true);

        } else {
            
            $url = $this->isHomologacao ? $this->aConfig['homologacao']: $this->aConfig['producao'];
            $soapReturn = $this->soap($url, $url, $action, $this->retXMLSoap($XMLAssinado, $action, true));
            
            if (!is_null($pathSoapReturn)) {
                file_put_contents($pathSoapReturn, $soapReturn);
            }
            
            return NFSeGenericoReturn::getReturn($soapReturn, $action, $pathFile);

        }
        
    }

    public function consultarLoteRps($protocolo) {

        $document = new NFSeDocument();

        $ConsultarLoteRpsEnvio = $document->appendChild(
            $document->createElementNS($this->sNfseNS, "ConsultarLoteRpsEnvio")
        );

        $Prestador = $ConsultarLoteRpsEnvio->appendChild(
            $document->createElementNS($this->sNfseNS, "Prestador")
        );
        
        $CpfCnpj = $Prestador->appendChild(
            $document->createElementNS($this->sNfseNS, "CpfCnpj")
        );

        $CpfCnpj->appendChild(
            $document->createElementNS($this->sNfseNS, "Cnpj", $this->aConfig['cnpj'])
        );

        $Prestador->appendChild(
            $document->createElementNS($this->sNfseNS, "InscricaoMunicipal", $this->aConfig['inscMunicipal'])
        );

        $ConsultarLoteRpsEnvio->appendChild(
            $document->createElementNS($this->sNfseNS, "Protocolo", $protocolo)
        );

        if($this->bPrintXml) {

            $document->formatOutput = true;
            echo "---------------------------------------------";
            echo $document->saveXML();
            
        } else {

            $url = $this->isHomologacao ? $this->aConfig['homologacao']: $this->aConfig['producao'];
            $action = "ConsultarLoteRps";

            $soap = $this->retXMLSoap($document->saveXML(), $action, true);
            $return = $this->curl($url, $soap, array('Content-Type: text/xml'), 80);

            return NFSeGenericoReturn::getReturn($return, $action);

        }
     
    }

    public function consultarNfsePorFaixa($iNumeroNfseInicial, $iNumeroNfseFinal, $iPagina) {

        $document = new NFSeDocument();

        $ConsultarNfseFaixaEnvio = $document->appendChild(
            $document->createElementNS($this->sNfseNS, "ConsultarNfseFaixaEnvio")
        );

        $Prestador = $ConsultarNfseFaixaEnvio->appendChild(
            $document->createElementNS($this->sNfseNS, "Prestador")
        );
        
        $CpfCnpj = $Prestador->appendChild(
            $document->createElementNS($this->sNfseNS, "CpfCnpj")
        );

        $CpfCnpj->appendChild(
            $document->createElementNS($this->sNfseNS, "Cnpj", $this->aConfig['cnpj'])
        );

        $Prestador->appendChild(
            $document->createElementNS($this->sNfseNS, "InscricaoMunicipal", $this->aConfig['inscMunicipal'])
        );

        $Faixa = $ConsultarNfseFaixaEnvio->appendChild(
            $document->createElementNS($this->sNfseNS, "Faixa")
        );

        $Faixa->appendChild(
            $document->createElementNS($this->sNfseNS, "NumeroNfseInicial", $iNumeroNfseInicial)
        );

        $Faixa->appendChild(
            $document->createElementNS($this->sNfseNS, "NumeroNfseFinal", $iNumeroNfseFinal)
        );

        $ConsultarNfseFaixaEnvio->appendChild(
            $document->createElementNS($this->sNfseNS, "Pagina", $iPagina)
        );

        if($this->bPrintXml) {

            $document->formatOutput = true;
            echo "---------------------------------------------";
            echo $document->saveXML();
            
        } else {

            $url = $this->isHomologacao ? $this->aConfig['homologacao']: $this->aConfig['producao'];
            $action = "ConsultarNfseFaixa";

            $soap = $this->retXMLSoap($document->saveXML(), $action, true);
            $return = $this->curl($url, $soap, array('Content-Type: text/xml'), 80);

            return NFSeGenericoReturn::getReturn($return, $action);
     
        }

    }

    public function consultarNfseServicoPrestado(NFSeGenericoConsultarNFSe $oConsultarNFSe) {

        $document = new NFSeDocument();

        $ConsultarNfseEnvio = $document->appendChild(
            $document->createElementNS($this->sNfseNS, "ConsultarNfseServicoPrestadoEnvio")
        );
        
        $Prestador = $ConsultarNfseEnvio->appendChild(
            $document->createElementNS($this->sNfseNS, "Prestador")
        );
        
        $CpfCnpj = $Prestador->appendChild(
            $document->createElementNS($this->sNfseNS, "CpfCnpj")
        );

        $CpfCnpj->appendChild(
            $document->createElementNS($this->sNfseNS, "Cnpj", $this->aConfig['cnpj'])
        );

        $Prestador->appendChild(
            $document->createElementNS($this->sNfseNS, "InscricaoMunicipal", $this->aConfig['inscMunicipal'])
        );

        if(!empty($oConsultarNFSe->NumeroNfse)) {
            
            $ConsultarNfseEnvio->appendChild(
                $document->createElementNS($this->sNfseNS, "NumeroNfse", $oConsultarNFSe->NumeroNfse)
            );

        }

        //Periodo Emissao
        if(!empty($oConsultarNFSe->PeriodoEmissao->DataInicial)){
            
            $PeriodoEmissao = $ConsultarNfseEnvio->appendChild(
                $document->createElementNS($this->sNfseNS, "PeriodoEmissao")
            );

            $PeriodoEmissao->appendChild(
                $document->createElementNS($this->sNfseNS, "DataInicial", $oConsultarNFSe->PeriodoEmissao->DataInicial)
            );

            $PeriodoEmissao->appendChild(
                $document->createElementNS($this->sNfseNS, "DataFinal", $oConsultarNFSe->PeriodoEmissao->DataFinal)
            );
            
        }

        //Tomador
        if($oConsultarNFSe->Tomador->getCpfCnpj() != ""){

            $IdentificacaoTomador = $ConsultarNfseEnvio->appendChild(
                $document->createElementNS($this->sNfseNS, "Tomador")
            );

            $CpfCnpj = $IdentificacaoTomador->appendChild(
                $document->createElementNS($this->sNfseNS, "CpfCnpj")
            );
            
            if ($oConsultarNFSe->Tomador->getTpPessoa() == 1) {
                
                $CpfCnpj->appendChild(
                    $document->createElementNS($this->sNfseNS, "Cnpj", $oConsultarNFSe->Tomador->getCpfCnpj())
                );

            } else {

                $CpfCnpj->appendChild(
                    $document->createElementNS($this->sNfseNS, "Cpf", $oConsultarNFSe->Tomador->getCpfCnpj())
                );

            }

            //Inscriçãoo Municipal só deve ser informada para pessoa jurídica
            if($oConsultarNFSe->Tomador->getTpPessoa() == 1 && !empty($oConsultarNFSe->Tomador->InscricaoMunicipal)) {
                
                $IdentificacaoTomador->appendChild(
                    $document->createElementNS($this->sNfseNS, "InscricaoMunicipal", $oConsultarNFSe->Tomador->InscricaoMunicipal)
                );

            }

        }

        //Intermediario
        if($oConsultarNFSe->IntermediarioServico->getCpfCnpj() != "") {

            $IdentificacaoIntermediario = $ConsultarNfseEnvio->appendChild(
                $document->createElementNS($this->sNfseNS, "Tomador")
            );

            $CpfCnpj = $IdentificacaoIntermediario->appendChild(
                $document->createElementNS($this->sNfseNS, "CpfCnpj")
            );
            
            if ($oConsultarNFSe->Tomador->getTpPessoa() == 1) {
                
                $CpfCnpj->appendChild(
                    $document->createElementNS($this->sNfseNS, "Cnpj", $oConsultarNFSe->Tomador->getCpfCnpj())
                );

            } else {

                $CpfCnpj->appendChild(
                    $document->createElementNS($this->sNfseNS, "Cpf", $oConsultarNFSe->Tomador->getCpfCnpj())
                );

            }

            //Inscriçãoo Municipal só deve ser informada para pessoa jurídica
            if($oConsultarNFSe->Tomador->getTpPessoa() == 1 && !empty($oConsultarNFSe->Tomador->InscricaoMunicipal)) {

                $IdentificacaoIntermediario->appendChild(
                    $document->createElementNS($this->sNfseNS, "InscricaoMunicipal", $oConsultarNFSe->Tomador->InscricaoMunicipal)
                );

            }

        }
        
        $ConsultarNfseEnvio->appendChild(
            $document->createElementNS($this->sNfseNS, "Pagina", 1)
        );

        if($this->bPrintXml) {

            $document->formatOutput = true;
            echo "---------------------------------------------";
            echo $document->saveXML();
            
        } else {

            $url = $this->isHomologacao ? $this->aConfig['homologacao']: $this->aConfig['producao'];
            $action = "consultarNfseServicoPrestado";

            $soap = $this->retXMLSoap($document->saveXML(), $action, true);
            $return = $this->curl($url, $soap, array('Content-Type: text/xml'), 80);

            return NFSeGenericoReturn::getReturn($return, $action);

        }

    }

    public function gerarNfse(NFSeGenericoInfRps $oRps) {
        
        $document = new NFSeDocument();

        $GerarNfseEnvioElement = $document->createElementNS($this->sNfseNS, "GerarNfseEnvio");
        $GerarNfseEnvioElement->setAttributeNS(self::XMLNS_URI, "xmlns:xd", "http://www.w3.org/2000/09/xmldsig#");

        $GerarNfseEnvio = $document->appendChild($GerarNfseEnvioElement);

        $Rps = $GerarNfseEnvio->appendChild($document->createElementNS($this->sNfseNS, "Rps"));
        $InfRps = $Rps->appendChild($document->createElementNS($this->sNfseNS, "InfDeclaracaoPrestacaoServico"));
        
        $InfDeclPrestServRps = $InfRps->appendChild($document->createElementNS($this->sNfseNS, "Rps"));
        
        $IdentificacaoRps = $InfDeclPrestServRps->appendChild($document->createElementNS($this->sNfseNS, "IdentificacaoRps"));
        $IdentificacaoRps->appendChild($document->createElementNS($this->sNfseNS, "Numero", $oRps->IdentificacaoRps->Numero));
        $IdentificacaoRps->appendChild($document->createElementNS($this->sNfseNS, "Tipo", $oRps->IdentificacaoRps->Tipo));
        $IdentificacaoRps->appendChild($document->createElementNS($this->sNfseNS, "Serie", $oRps->IdentificacaoRps->Serie));
        
        $InfDeclPrestServRps->appendChild($document->createElementNS($this->sNfseNS, "DataEmissao", $oRps->DataEmissao));
        $InfDeclPrestServRps->appendChild($document->createElementNS($this->sNfseNS, "Status", $oRps->Status));
        
        if(!empty($oRps->RpsSubstituido->Numero)) {
            $RpsSubstituido = $InfDeclPrestServRps->appendChild($document->createElementNS($this->sNfseNS, "RpsSubstituido"));
            $RpsSubstituido->appendChild($document->createElementNS($this->sNfseNS, "Numero", $oRps->RpsSubstituido->Numero));
            $RpsSubstituido->appendChild($document->createElementNS($this->sNfseNS, "Tipo", $oRps->RpsSubstituido->Tipo));            
        }
        
        $Servico = $InfRps->appendChild($document->createElementNS($this->sNfseNS, "Servico"));
        
        $Valores = $Servico->appendChild($document->createElementNS($this->sNfseNS, "Valores"));
        $Valores->appendChild($document->createElementNS($this->sNfseNS, "ValorServicos", number_format($oRps->Servico->Valores->ValorServicos, 2, '.', '')));
        
        if(!empty($oRps->Servico->Valores->ValorIssRetido)) {
            $Valores->appendChild($document->createElementNS($this->sNfseNS, "ValorIssRetido", number_format($oRps->Servico->Valores->ValorIssRetido, 2, '.', '')));
        }
        
        $Valores->appendChild($document->createElementNS($this->sNfseNS, "ValorDeducoes", number_format($oRps->Servico->Valores->ValorDeducoes, 2, '.', '')));
        $Valores->appendChild($document->createElementNS($this->sNfseNS, "ValorPis", number_format($oRps->Servico->Valores->ValorPis, 2, '.', '')));
        $Valores->appendChild($document->createElementNS($this->sNfseNS, "ValorCofins", number_format($oRps->Servico->Valores->ValorCofins, 2, '.', '')));
        $Valores->appendChild($document->createElementNS($this->sNfseNS, "ValorInss", number_format($oRps->Servico->Valores->ValorInss, 2, '.', '')));
        $Valores->appendChild($document->createElementNS($this->sNfseNS, "ValorIr", number_format($oRps->Servico->Valores->ValorIr, 2, '.', '')));
        $Valores->appendChild($document->createElementNS($this->sNfseNS, "ValorCsll", number_format($oRps->Servico->Valores->ValorCsll, 2, '.', '')));
        $Valores->appendChild($document->createElementNS($this->sNfseNS, "OutrasRetencoes", number_format($oRps->Servico->Valores->OutrasRetencoes, 2, '.', '')));
        $Valores->appendChild($document->createElementNS($this->sNfseNS, "Aliquota", number_format($oRps->Servico->Valores->Aliquota, 4, '.', '')));
        
        if(isset($this->aConfig['descontoIncondicionado']) && $this->aConfig['descontoIncondicionado'] == true) {
            $Valores->appendChild($document->createElementNS($this->sNfseNS, "DescontoIncondicionado", number_format($oRps->Servico->Valores->DescontoIncondicionado, 2, '.', '')));
        }
        
        $Valores->appendChild($document->createElementNS($this->sNfseNS, "DescontoCondicionado", number_format($oRps->Servico->Valores->DescontoCondicionado, 2, '.', '')));
        
        if(!empty($oRps->Servico->ItemListaServico)){
            $Servico->appendChild($document->createElementNS($this->sNfseNS, "ItemListaServico", $oRps->Servico->ItemListaServico));
        }
        
        $Servico->appendChild($document->createElementNS($this->sNfseNS, "CodigoCnae", $oRps->Servico->CodigoCnae));
        
        if(!empty($oRps->Servico->CodigoTributacaoMunicipio)) {
            $Servico->appendChild($document->createElementNS($this->sNfseNS, "CodigoTributacaoMunicipio", $oRps->Servico->CodigoTributacaoMunicipio));
        }
        
        $Servico->appendChild($document->createElementNS($this->sNfseNS, "Discriminacao"))->appendChild($document->createCDATASection($oRps->Servico->Discriminacao));
        $Servico->appendChild($document->createElementNS($this->sNfseNS, "CodigoMunicipio", $oRps->Servico->CodigoMunicipio));
        $Servico->appendChild($document->createElementNS($this->sNfseNS, "ExigibilidadeISS", $oRps->Servico->ExigibilidadeISS));
        
        $Prestador = $InfRps->appendChild($document->createElementNS($this->sNfseNS, "Prestador"));
        $Prestador->appendChild( $document->createElementNS($this->sNfseNS, "CpfCnpj"))->appendChild($document->createElementNS($this->sNfseNS, "Cnpj", $this->aConfig['cnpj']));
        $Prestador->appendChild($document->createElementNS($this->sNfseNS, "InscricaoMunicipal", $this->aConfig['inscMunicipal']));
        
        $Tomador = $InfRps->appendChild($document->createElementNS($this->sNfseNS, "Tomador"));
        $IdentificacaoTomador = $Tomador->appendChild($document->createElementNS($this->sNfseNS, "IdentificacaoTomador"));
        $CpfCnpj = $IdentificacaoTomador->appendChild($document->createElementNS($this->sNfseNS, "CpfCnpj"));
        
        if ($oRps->Tomador->IdentificacaoTomador->getTpPessoa() == 1) {
            $CpfCnpj->appendChild($document->createElementNS($this->sNfseNS, "Cnpj", $oRps->Tomador->IdentificacaoTomador->getCpfCnpj()));
        } else {
            $CpfCnpj->appendChild($document->createElementNS($this->sNfseNS, "Cpf", $oRps->Tomador->IdentificacaoTomador->getCpfCnpj()));
        }
        
        if($oRps->Tomador->IdentificacaoTomador->getTpPessoa() == 1 
            && !empty($oRps->Tomador->IdentificacaoTomador->InscricaoMunicipal)) {
        
            $IdentificacaoTomador->appendChild($document->createElementNS($this->sNfseNS, "InscricaoMunicipal", $oRps->Tomador->IdentificacaoTomador->InscricaoMunicipal));
            
        }
        
        $Tomador->appendChild($document->createElementNS($this->sNfseNS, "RazaoSocial"))->appendChild($document->createCDATASection($oRps->Tomador->RazaoSocial));
        $Endereco = $Tomador->appendChild($document->createElementNS($this->sNfseNS, "Endereco"));
        
        if (!is_null($oRps->Tomador->Endereco->Logradouro)) {
            $Endereco->appendChild($document->createElementNS($this->sNfseNS, "Endereco"))->appendChild($document->createCDATASection($oRps->Tomador->Endereco->Logradouro));
        }
        
        $Endereco->appendChild($document->createElementNS($this->sNfseNS, "Numero", $oRps->Tomador->Endereco->Numero));
        $Endereco->appendChild($document->createElementNS($this->sNfseNS, "Complemento"))->appendChild($document->createCDATASection($oRps->Tomador->Endereco->Complemento));
        $Endereco->appendChild($document->createElementNS($this->sNfseNS, "Bairro"))->appendChild($document->createCDATASection($oRps->Tomador->Endereco->Bairro));
        $Endereco->appendChild($document->createElementNS($this->sNfseNS, "CodigoMunicipio", $oRps->Tomador->Endereco->CodigoMunicipio));
        $Endereco->appendChild($document->createElementNS($this->sNfseNS, "Uf", $oRps->Tomador->Endereco->Uf));
        $Endereco->appendChild($document->createElementNS($this->sNfseNS, "Cep", $oRps->Tomador->Endereco->Cep));
        
        if((!empty($oRps->Tomador->Contato->Telefone) 
            && trim($oRps->Tomador->Contato->Telefone) != "") 
            || (!empty($oRps->Tomador->Contato->Email) 
            && trim($oRps->Tomador->Contato->Email) != "")) {
        
            $Contato = $Tomador->appendChild($document->createElementNS($this->sNfseNS, "Contato"));
        
            if (!empty($oRps->Tomador->Contato->Telefone) && trim($oRps->Tomador->Contato->Telefone) != "") {
                $Contato->appendChild($document->createElementNS($this->sNfseNS, "Telefone", $oRps->Tomador->Contato->Ddd . $oRps->Tomador->Contato->Telefone));
            }
        
            if (!empty($oRps->Tomador->Contato->Email) && trim($oRps->Tomador->Contato->Email) != "") {
                $Contato->appendChild($document->createElementNS($this->sNfseNS, "Email", $oRps->Tomador->Contato->Email));
            }
        
        }
        
        if (!empty($oRps->IntermediarioServico->RazaoSocial)) {
        
            $IntermediarioServico = $InfRps->appendChild($document->createElementNS($this->sNfseNS, "IntermediarioServico"));
            $CpfCnpj = $IntermediarioServico->appendChild($document->createElementNS($this->sNfseNS, "CpfCnpj"));
            
            if($oRps->IntermediarioServico->getTpPessoa() == 1) {
                $CpfCnpj->appendChild($document->createElementNS($this->sNfseNS, "Cnpj", $oRps->IntermediarioServico->getCpfCnpj()));
            } else {
                $CpfCnpj->appendChild($document->createElementNS($this->sNfseNS, "Cpf", $oRps->IntermediarioServico->getCpfCnpj()));
            }
        
            if(!empty($oRps->IntermediarioServico->InscricaoMunicipal) && trim($oRps->IntermediarioServico->InscricaoMunicipal) != "") {
                $IntermediarioServico->appendChild($document->createElementNS($this->sNfseNS, "InscricaoMunicipal", $oRps->IntermediarioServico->InscricaoMunicipal));
            }
        
            $IntermediarioServico->appendChild($document->createElementNS($this->sNfseNS, "RazaoSocial"))->appendChild($document->createCDATASection($oRps->IntermediarioServico->RazaoSocial));
            $IntermediarioServico->appendChild($document->createElementNS($this->sNfseNS, "CodigoMunicipio"))->appendChild($document->createCDATASection($oRps->IntermediarioServico->CodigoMunicipio));
            
        }
        
        if (!empty($oRps->ConstrucaoCivil->CodigoObra)) {
        
            $ConstrucaoCivil = $InfRps->appendChild($document->createElementNS($this->sNfseNS, "ConstrucaoCivil"));
            $ConstrucaoCivil->appendChild($document->createElementNS($this->sNfseNS, "CodigoObra", $oRps->ConstrucaoCivil->CodigoObra));
            $ConstrucaoCivil->appendChild($document->createElementNS($this->sNfseNS, "CodigoObra", $oRps->ConstrucaoCivil->Art));
            
        }

        if($this->bPrintXml) {

            $document->formatOutput = true;
            echo "---------------------------------------------";
            echo $document->saveXML();
            
        } else {

            $XMLAssinado = $this->signXML(trim($document->saveXML()), "InfDeclaracaoPrestacaoServico", "Rps", 'xd:');
            $pathSoapReturn = $pathFile = null;
            
            $action = "gerarNfse";
            
            if(isset($this->aConfig['pathCert'])) {
                
                file_put_contents($this->aConfig['pathCert'] . '/env_gerarNfse_' . $oRps->IdentificacaoRps->Numero . ".xml", $XMLAssinado);
                $pathFile = $this->aConfig['pathCert'] . '/env_gerarNfse_' . $oRps->IdentificacaoRps->Numero . "_ret.xml";
                
                file_put_contents($this->aConfig['pathCert'] . '/env_gerarNfse_soap_' . $oRps->IdentificacaoRps->Numero . ".xml", $this->retXMLSoap($XMLAssinado, $action));
                $pathSoapReturn = $this->aConfig['pathCert'] . '/env_gerarNfse_' . $oRps->IdentificacaoRps->Numero . "_ret_soap.xml";
                
            }


            $url = $this->isHomologacao ? $this->aConfig['homologacao']: $this->aConfig['producao'];

            $soapReturn = $this->soap($url, $url, $action, $this->retXMLSoap($XMLAssinado, $action, true));
            
            if(!is_null($pathSoapReturn)) {
                file_put_contents($pathSoapReturn, $soapReturn);
            }

            return NFSeGenericoReturn::getReturn($soapReturn, $action, $pathFile);
        
        }

    }

    private function retXMLSoap($xml, $action, $appendUser = false) {
        
        $sSoapNS = "http://schemas.xmlsoap.org/soap/envelope/";
        $sSoapPrefix = "soapenv:";

        $oXMLSOAP = new NFSeDocument();

        $EnvelopeElement =  $oXMLSOAP->createElementNS($sSoapNS, $sSoapPrefix . "Envelope");
        $EnvelopeElement->setAttributeNS(self::XMLNS_URI, "xmlns:" . $this->sServiceNSPrefix, $this->sServiceNS);
        $EnvelopeElement->setAttributeNS(self::XMLNS_URI, "xmlns:" . $this->sNfseNsPrefix, $this->sNfseNS);
        
        $Envelope = $oXMLSOAP->appendChild($EnvelopeElement);
               
        $Envelope->appendChild(
            $oXMLSOAP->createElementNS($sSoapNS, "Header")
        );

        $Body = $Envelope->appendChild(
            $oXMLSOAP->createElementNS($sSoapNS, "Body")
        );

        $soapAction = $oXMLSOAP->createElementNS($this->sServiceNS, $action);

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

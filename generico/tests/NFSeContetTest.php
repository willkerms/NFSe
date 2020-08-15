<?php

use NFSe\generico\NFSeGenericoCancelarNfseEnvio;
use NFSe\generico\NFSeGenericoConsultarNFSe;
use NFSe\generico\NFSeGenericoInfRps;
use NFSe\generico\NFSeGenericoPeriodoEmissao;

class NFSeContentTest {


    public static function getCancelarNfse() {

        $json = self::getJson();
        $oCancelar = new NFSeGenericoCancelarNfseEnvio();

        $oCancelar->setCpfCnpj($json->cnpjPrestador);
        $oCancelar->InscricaoMunicipal = $json->inscMunicipalPrestador;
        $oCancelar->Numero = $json->idNfse;

        return $oCancelar;

    }

    public static function getConsultarNfseSericoPrestado() {
        
        $objPeriodo = new NFSeGenericoPeriodoEmissao();
        $objPeriodo->DataInicial = preg_replace('/\..*/', "", str_replace(' ', 'T', date('Y-m-d H:i:s', mktime(0, 0, 0, 7, 1, 2020))));
        $objPeriodo->DataFinal = preg_replace('/\..*/', "", str_replace(' ', 'T', date('Y-m-d H:i:s')));

        $obj = new NFSeGenericoConsultarNFSe();
        $obj->NumeroNfse = 30485;
        $obj->PeriodoEmissao =$objPeriodo;

        return $obj;

    }

    public static function getGerarNFSe() {

        $oRps = new NFSeGenericoInfRps();

        $oRps->DataEmissao = preg_replace('/\..*/', "", str_replace(' ', 'T', date('Y-m-d H:i:s')));
        
        $oRps->IdentificacaoRps->Numero = 00000000030603;
        $oRps->IdentificacaoRps->Tipo = 1;

        $oRps->Servico->Valores->ValorServicos = 30.00;
        $oRps->Servico->Valores->ValorDeducoes = 0.00;
        $oRps->Servico->Valores->ValorPis = 0.00;
        $oRps->Servico->Valores->ValorCofins = 0.00;
        $oRps->Servico->Valores->ValorInss = 0.00;
        $oRps->Servico->Valores->ValorIr = 0.00;
        $oRps->Servico->Valores->ValorCsll = 0.00;
        $oRps->Servico->Valores->OutrasRetencoes = 0.00;
        $oRps->Servico->Valores->Aliquota = 2.0;
        $oRps->Servico->Valores->DescontoCondicionado = 0.00;
        $oRps->Servico->Discriminacao = "Lavacao Completa";
        $oRps->Servico->CodigoMunicipio = 3504800;
        $oRps->Servico->ExigibilidadeISS = 1;

        $oRps->Tomador->IdentificacaoTomador->tpPessoa = 2;
        $oRps->Tomador->IdentificacaoTomador->cpfCnpj = "77904071711";
        $oRps->Tomador->RazaoSocial = "Anderson Cliente";
        $oRps->Tomador->Endereco->Logradouro = "Rua Fiorelo Zandona";
        $oRps->Tomador->Endereco->Numero = 159;
        $oRps->Tomador->Endereco->Bairro = "Santa Terezinha";
        $oRps->Tomador->Endereco->CodigoMunicipio = 4118501;
        $oRps->Tomador->Endereco->Uf = "PR";
        $oRps->Tomador->Endereco->Cep = 85506010;
        $oRps->Tomador->Contato->Ddd = 46;
        $oRps->Tomador->Contato->Telefone = 32251234;
        
        return $oRps;

    }

    public static function getJson() {

        $path = pathinfo(__FILE__, PATHINFO_DIRNAME);
        $oNFSe = json_decode(file_get_contents($path . '/nfse.json'));
        return $oNFSe;
        
    }

}

?>

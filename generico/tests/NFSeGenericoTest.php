<?php

use NFSe\generico\NFSeGenerico;
use PHPUnit\Framework\TestCase;

use function PHPUnit\Framework\assertTrue;

final class NFSeGenericoTest extends TestCase {
    
    /**
     * @dataProvider additionProvider
     */
    public function testCancelarNfse($aConfig, $isHomologacao) {

        $nfseGenerico = new NFSeGenerico($aConfig, $isHomologacao);
        $result = $nfseGenerico->cancelarNfse(NFSeContentTest::getCancelarNfse());
        assertTrue(true);

    }

    /**
     * @dataProvider additionProvider
     */
    public function testconsultarLoteRps($aConfig, $isHomologacao) {
    
        $nfseGenerico = new NFSeGenerico($aConfig, $isHomologacao);
        $result = $nfseGenerico->consultarLoteRps("protocolo123");
        assertTrue(true);

    }

    /**
     * @dataProvider additionProvider
     */
    public function testconsultarNfsePorFaixa($aConfig, $isHomologacao) {
    
        $nfseGenerico = new NFSeGenerico($aConfig, $isHomologacao);
        $result = $nfseGenerico->consultarNfsePorFaixa(1, 10, 1);
        assertTrue(true);

    }

    /**
     * @dataProvider additionProvider
     */
    public function testConsultarNfseServicoPrestado($aConfig, $isHomologacao) {

        $nfseGenerico = new NFSeGenerico($aConfig, $isHomologacao);
        $result = $nfseGenerico->consultarNfseServicoPrestado(NFSeContentTest::getConsultarNfseSericoPrestado());
        assertTrue(true);

    }

    /**
     * @dataProvider additionProvider
     */
    public function testGerarNfse($aConfig, $isHomologacao) {

         $nfseGenerico = new NFSeGenerico($aConfig, $isHomologacao);
         $result = $nfseGenerico->gerarNfse(NFSeContentTest::getGerarNFSe());
         assertTrue(true);

     }

    public function additionProvider() {
        
        $aConfig = array(
            'homologacao' => 'http://fi1.fiorilli.com.br:5663/IssWeb-ejb/IssWebWS/IssWebWS?wsdl',
            'producao' => 'http://177.124.184.59:5660/IssWeb-ejb/IssWebWS/IssWebWS?wsdl',
            'pfx' => '/Users/mayconsilva/Documents/NFSe/issnet-ji-parana-ba-mxp-arpuro/certificadoA1Safeweb MXP USINA 2021 (34213520).pfx',
            'pwdPFX' => '34213520',
            'usuario' => '01001001000113',
            'senha' => '123456',
            'cnpj' => '01001001000113',
            'inscMunicipal' => '15000',
            'serviceNS' => "http://ws.issweb.fiorilli.com.br/",
            'serviceNSPrefix' => "ws",
            'nfseNS' => "http://www.abrasf.org.br/nfse.xsd",
            'nfseNsPrefix' => "nfse"
        );

        // $aConfig = array(
        //     'homologacao' => 'http://www.issnetonline.com.br/webserviceabrasf/aparecidadegoiania/servicos.asmx',
        //     'producao' => '',
        //     'pfx' => '/Users/mayconsilva/Documents/NFSe/issnet-ji-parana-ba-mxp-arpuro/certificadoA1Safeweb MXP USINA 2021 (34213520).pfx',
        //     'pwdPFX' => '34213520',
        //     'usuario' => '01001001000113',
        //     'senha' => '123456',
        //     'cnpj' => '01001001000113',
        //     'inscMunicipal' => '15000',
        //     'serviceNS' => "http://www.issnetonline.com.br/webservice/nfd",
        //     'serviceNSPrefix' => "nfd",
        //     'nfseNS' => "http://www.abrasf.org.br/nfse.xsd",
        //     'nfseNsPrefix' => "nfse"
        // );

        return array(
            array($aConfig , true)
        );

    }

}

?>
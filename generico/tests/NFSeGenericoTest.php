<?php

use NFSe\generico\NFSeGenerico;
use PHPUnit\Framework\TestCase;

final class NFSeGenericoTest extends TestCase {

    /**
     * @dataProvider additionProvider
     */
    // public function testConsultarNfseServicoPrestado($aConfig, $isHomologacao) {

    //     $nfseGenerico = new NFSeGenerico($aConfig, $isHomologacao);
    //     $result = $nfseGenerico->consultarNfseServicoPrestado(NFSeContentTest::getConsultarNfseSericoPrestado());
    //     $this->assertIsArray($result);

    // }

    /**
     * @dataProvider additionProvider
     */
    public function testGerarNfse($aConfig, $isHomologacao) {

        $nfseGenerico = new NFSeGenerico($aConfig, $isHomologacao);
        $result = $nfseGenerico->gerarNfse(NFSeContentTest::getGerarNFSe());

    }

    public function additionProvider() {
        
        $aConfig = array(
            'pfx' => '/Users/mayconsilva/Documents/NFSe/issnet-ji-parana-ba-mxp-arpuro/certificadoA1Safeweb MXP USINA 2021 (34213520).pfx',
            'pwdPFX' => '34213520',
            'usuario' => '01001001000113',
            'senha' => '123456',
            'cnpj' => '01001001000113',
            'inscMunicipal' => '15000',
        );

        return array(
            array($aConfig , true)
        );

    }

}

?>
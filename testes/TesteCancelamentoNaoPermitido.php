<?php
/**
 * Testa o 'allowCancel' => false: nenhum XML é transmitido e o retorno vem simulado como cancelado.
 * Rodar: php testes/TesteCancelamentoNaoPermitido.php
 */
require_once __DIR__ . '/../../../autoload.php';//vendor/autoload.php do projeto que instalou a lib

use NFSe\generico\NFSeGenerico;
use NFSe\generico\NFSeGenericoCancelarNfseEnvio;

class TesteCancelamentoNaoPermitido{

	public static function main(){

		foreach(array('cancelarNfse', 'cancelarNFSeEnvio') as $metodo)
			self::testeAllowCancelFalse($metodo);

		echo "OK" . PHP_EOL;
	}

	private static function testeAllowCancelFalse($metodo){

		$oNFSe = new NFSeGenerico(array(
			'cpfCnpj' => '00000000000000',
			'insMunicipal' => '123456',
			'privKey' => '', 'pubKey' => '', 'certKey' => '',
			'homologacao' => array('wsdl' => 'https://homologacao.teste/nfse?wsdl'),
			'metodos' => array($metodo => array('allowCancel' => false))
		));

		$oCancelar = new NFSeGenericoCancelarNfseEnvio();
		$oCancelar->Numero = '123';
		$oCancelar->CodigoVerificacao = 'ABC123';
		$oCancelar->DescricaoCancelamento = 'ERRO AO EMITIR NFSe';

		$ret = $oNFSe->$metodo($oCancelar);

		$aInfPedido = $ret['RetCancelamento']['NfseCancelamento'][0]['Confirmacao']['Pedido']['InfPedidoCancelamento'];

		assert(count($ret['ListaMensagemRetorno']) == 0, $metodo . ': ListaMensagemRetorno deve vir vazia');
		assert($aInfPedido['IdentificacaoNfse']['Numero'] == '123', $metodo . ': Numero da NFSe');
		assert($aInfPedido['IdentificacaoNfse']['CpfCnpj'] == '00000000000000', $metodo . ': CpfCnpj assume o da configuração');
		assert($aInfPedido['IdentificacaoNfse']['InscricaoMunicipal'] == '123456', $metodo . ': InscricaoMunicipal assume a da configuração');
		assert($aInfPedido['CodigoCancelamento'] == '1', $metodo . ': CodigoCancelamento assume o codCancelamento configurado');
	}
}

TesteCancelamentoNaoPermitido::main();

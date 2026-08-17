<?php
/**
 * Testa o retorno do 'cancelarNFSeEnvio' no padrão Nacional por SOAP (CancelarNfseResposta).
 * Rodar: php -d zend.assertions=1 -d assert.exception=1 testes/TesteCancelarNFSeEnvioSoap.php
 */
require_once __DIR__ . '/../../../autoload.php';//vendor/autoload.php do projeto que instalou a lib

use NFSe\generico\NFSeGenerico;
use NFSe\generico\NFSeGenericoReturn;

class TesteCancelarNFSeEnvioSoap{

	public static function main(){

		self::testeEventoRegistrado();
		self::testeMensagemRetorno();
		self::testeRespostaVazia();
		self::testeEventoSemDhProc();
		self::testeRetCancelamentoIncompleto();
		self::testeRetCancelamentoAbrasfCompleto();

		echo "OK" . PHP_EOL;
	}

	/**
	 * Não declara tagMap: o default de metodos.cancelarNFSeEnvio tem que suprir
	 *
	 * @return NFSeGenericoReturn
	 */
	private static function retReturn(){

		return new NFSeGenericoReturn(new NFSeGenerico(array(
			'cpfCnpj' => '00000000000000',
			'insMunicipal' => '123456',
			'privKey' => '', 'pubKey' => '', 'certKey' => '',
			'homologacao' => array('wsdl' => 'https://homologacao.teste/nfse?wsdl')
		)));
	}

	private static function retEnvelope($conteudo){

		return '<?xml version="1.0" encoding="utf-8"?>'
			. '<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/"><soap:Body>'
			. '<CancelarNfseResposta xmlns="http://www.sped.fazenda.gov.br/nfse">' . $conteudo . '</CancelarNfseResposta>'
			. '</soap:Body></soap:Envelope>';
	}

	private static function retListaEventoXML($hasDhProc = true){

		return '<ListaEvento>
			<evento versao="1.01">
				<infEvento Id="EVT00000000000000000000000000000000000000000000000001101101001">
					<verAplic>1.01</verAplic>
					<ambGer>2</ambGer>
					<nSeqEvento>001</nSeqEvento>
					' . ($hasDhProc ? '<dhProc>2026-08-14T10:15:00-03:00</dhProc>' : '') . '
					<nDFSe>4321</nDFSe>
					<pedRegEvento versao="1.01">
						<infPedReg Id="PRE00000000000000000000000000000000000000000000000001101101">
							<tpAmb>2</tpAmb>
							<verAplic>1.01</verAplic>
							<dhEvento>2026-08-14T10:14:00-03:00</dhEvento>
							<CNPJAutor>00000000000000</CNPJAutor>
							<chNFSe>00000000000000000000000000000000000000000000000001</chNFSe>
							<e101101>
								<xDesc>Cancelamento de NFS-e</xDesc>
								<cMotivo>1</cMotivo>
								<xMotivo>ERRO AO EMITIR NFSe</xMotivo>
							</e101101>
						</infPedReg>
					</pedRegEvento>
				</infEvento>
			</evento>
		</ListaEvento>';
	}

	/**
	 * Caso 1: ListaEvento completa -> sucesso com RetCancelamento derivado e ListaEvento
	 */
	private static function testeEventoRegistrado(){

		$ret = self::retReturn()->getReturn(self::retEnvelope(self::retListaEventoXML()), 'cancelarNFSeEnvio');

		assert(count($ret['ListaMensagemRetorno']) == 0, 'Caso 1: ListaMensagemRetorno deve vir vazia');
		assert(count($ret['RetCancelamento']['NfseCancelamento']) == 1, 'Caso 1: RetCancelamento deve ter 1 item');
		assert(count($ret['ListaEvento']) == 1, 'Caso 1: ListaEvento deve ter 1 item');

		$aConfirmacao = $ret['RetCancelamento']['NfseCancelamento'][0]['Confirmacao'];
		$aInfPedido = $aConfirmacao['Pedido']['InfPedidoCancelamento'];

		assert($aConfirmacao['DataHora'] == '2026-08-14T10:15:00-03:00', 'Caso 1: DataHora vem do dhProc');
		assert($aInfPedido['IdentificacaoNfse']->Numero == '4321', 'Caso 1: Numero vem do nDFSe');
		assert($aInfPedido['IdentificacaoNfse']->CodigoVerificacao == '00000000000000000000000000000000000000000000000001', 'Caso 1: CodigoVerificacao vem do chNFSe');
		assert($aInfPedido['IdentificacaoNfse']->CpfCnpj == '00000000000000', 'Caso 1: CpfCnpj vem do CNPJAutor');
		assert(is_null($aInfPedido['IdentificacaoNfse']->InscricaoMunicipal), 'Caso 1: InscricaoMunicipal nao existe no evento');
		assert(is_null($aInfPedido['IdentificacaoNfse']->CodigoMunicipio), 'Caso 1: CodigoMunicipio nao existe no evento');
		assert($aInfPedido['CodigoCancelamento'] == '1', 'Caso 1: CodigoCancelamento vem do cMotivo');
		assert($aInfPedido['DescricaoCancelamento'] == 'ERRO AO EMITIR NFSe', 'Caso 1: DescricaoCancelamento vem do xMotivo');
		assert($ret['ListaEvento'][0]['pedRegEvento']['tpEvento'] == '101101', 'Caso 1: tpEvento sai sem o "e"');
		assert(is_null($ret['ListaEvento'][0]['pedRegEvento']['CPFAutor']), 'Caso 1: CPFAutor fica null quando vem CNPJAutor');
	}

	/**
	 * Caso 2: ListaMensagemRetorno -> erro, sem RetCancelamento nem ListaEvento
	 */
	private static function testeMensagemRetorno(){

		$xml = self::retEnvelope('<ListaMensagemRetorno><MensagemRetorno><Codigo>E0001</Codigo><Mensagem>NFS-e ja cancelada</Mensagem></MensagemRetorno></ListaMensagemRetorno>');

		$ret = self::retReturn()->getReturn($xml, 'cancelarNFSeEnvio');

		assert(count($ret['ListaMensagemRetorno']) == 1, 'Caso 2: ListaMensagemRetorno deve ter 1 item');
		assert($ret['ListaMensagemRetorno'][0]->Codigo == 'E0001', 'Caso 2: Codigo da mensagem');
		assert(!isset($ret['RetCancelamento']), 'Caso 2: nao pode ter RetCancelamento');
		assert(!isset($ret['ListaEvento']), 'Caso 2: nao pode ter ListaEvento');
	}

	/**
	 * Caso 3: resposta vazia -> retorno fora do formato esperado
	 */
	private static function testeRespostaVazia(){

		$ret = self::retReturn()->getReturn(self::retEnvelope(''), 'cancelarNFSeEnvio');

		assert(count($ret['ListaMensagemRetorno']) == 1, 'Caso 3: ListaMensagemRetorno deve ter 1 item');
		assert($ret['ListaMensagemRetorno'][0]->Mensagem == 'Retorno fora do formato esperado!', 'Caso 3: mensagem de formato inesperado');
	}

	/**
	 * Caso 4: evento sem dhProc nao e confirmacao concreta -> nao pode virar sucesso
	 */
	private static function testeEventoSemDhProc(){

		$ret = self::retReturn()->getReturn(self::retEnvelope(self::retListaEventoXML(false)), 'cancelarNFSeEnvio');

		assert(count($ret['ListaMensagemRetorno']) == 1, 'Caso 4: evento sem dhProc nao pode devolver lista vazia');
		assert($ret['ListaMensagemRetorno'][0]->Mensagem == 'Retorno fora do formato esperado!', 'Caso 4: mensagem de formato inesperado');
		assert(!isset($ret['RetCancelamento']), 'Caso 4: nao pode ter RetCancelamento');
	}

	/**
	 * Caso 5: RetCancelamento sem Pedido -> nao pode gerar fatal (\Error nao e capturado pelo consumidor)
	 */
	private static function testeRetCancelamentoIncompleto(){

		$xml = self::retEnvelope('<RetCancelamento><NfseCancelamento versao="1.01"><Confirmacao><DataHora>2026-08-14T10:15:00</DataHora></Confirmacao></NfseCancelamento></RetCancelamento>');

		try {

			$ret = self::retReturn()->getReturn($xml, 'cancelarNFSeEnvio');

			assert(count($ret['ListaMensagemRetorno']) == 1, 'Caso 5: ListaMensagemRetorno deve ter 1 item');
			assert($ret['ListaMensagemRetorno'][0]->Mensagem == 'Retorno fora do formato esperado!', 'Caso 5: mensagem de formato inesperado');
			assert(!isset($ret['RetCancelamento']), 'Caso 5: nao pode ter RetCancelamento');

		} catch (\AssertionError $e) {
			throw $e;
		} catch (\Throwable $e) {
			assert(false, 'Caso 5: RetCancelamento incompleto nao pode gerar fatal: ' . get_class($e) . ' - ' . $e->getMessage());
		}
	}

	private static function retRetCancelamentoAbrasfXML(){

		return '<RetCancelamento>
			<NfseCancelamento versao="2.03">
				<Confirmacao>
					<Pedido>
						<InfPedidoCancelamento Id="P1">
							<IdentificacaoNfse>
								<Numero>4321</Numero>
								<CpfCnpj><Cnpj>00000000000000</Cnpj></CpfCnpj>
								<InscricaoMunicipal>123456</InscricaoMunicipal>
								<CodigoVerificacao>ABC123</CodigoVerificacao>
							</IdentificacaoNfse>
							<CodigoCancelamento>1</CodigoCancelamento>
							<DescricaoCancelamento>ERRO AO EMITIR NFSe</DescricaoCancelamento>
						</InfPedidoCancelamento>
					</Pedido>
					<DataHora>2026-08-14T10:15:00</DataHora>
				</Confirmacao>
			</NfseCancelamento>
		</RetCancelamento>';
	}

	/**
	 * Caso 6: RetCancelamento ABRASF completo pelos dois caminhos - prefeitura hibrida (c) e regressao do cancelarNfse
	 */
	private static function testeRetCancelamentoAbrasfCompleto(){

		//Caminho (c): envelope do padrao Nacional com RetCancelamento no formato ABRASF
		$ret = self::retReturn()->getReturn(self::retEnvelope(self::retRetCancelamentoAbrasfXML()), 'cancelarNFSeEnvio');

		assert(count($ret['ListaMensagemRetorno']) == 0, 'Caso 6: hibrida deve vir sem mensagem');
		assert(count($ret['RetCancelamento']['NfseCancelamento']) == 1, 'Caso 6: hibrida deve ter 1 item');
		assert(!isset($ret['ListaEvento']), 'Caso 6: hibrida nao tem ListaEvento');

		//Regressao do ABRASF: as guardas de null nao podem descartar um documento completo
		$xmlAbrasf = '<?xml version="1.0" encoding="utf-8"?>'
			. '<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/"><soap:Body>'
			. '<cancelarNfseResponse><CancelarNfseResposta>' . self::retRetCancelamentoAbrasfXML() . '</CancelarNfseResposta></cancelarNfseResponse>'
			. '</soap:Body></soap:Envelope>';

		$retAbrasf = self::retReturn()->getReturn($xmlAbrasf, 'cancelarNfse');

		assert(count($retAbrasf['RetCancelamento']['NfseCancelamento']) == 1, 'Caso 6: ABRASF completo continua devolvendo 1 item');

		$aInfPedido = $retAbrasf['RetCancelamento']['NfseCancelamento'][0]['Confirmacao']['Pedido']['InfPedidoCancelamento'];

		assert($aInfPedido['IdentificacaoNfse']->Numero == '4321', 'Caso 6: ABRASF Numero');
		assert($aInfPedido['IdentificacaoNfse']->CpfCnpj == '00000000000000', 'Caso 6: ABRASF CpfCnpj continua preenchido apos a guarda');
		assert($aInfPedido['IdentificacaoNfse']->InscricaoMunicipal == '123456', 'Caso 6: ABRASF InscricaoMunicipal');
		assert($aInfPedido['CodigoCancelamento'] == '1', 'Caso 6: ABRASF CodigoCancelamento');
		assert($retAbrasf['RetCancelamento']['NfseCancelamento'][0]['Confirmacao']['DataHora'] == '2026-08-14T10:15:00', 'Caso 6: ABRASF DataHora');
	}
}

TesteCancelarNFSeEnvioSoap::main();

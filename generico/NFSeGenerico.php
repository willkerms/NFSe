<?php

namespace NFSe\generico;

use NFSe\NFSe;
use NFSe\NFSeDocument;
use PQD\PQDUtil;

class NFSeGenerico extends NFSe {

	private $isHomologacao = true;

	private $aConfig;

	/**
	 * @var NFSeGenericoReturn
	 */
	private $oReturn;

	public function __construct(array $aConfig, $isHomologacao = true) {
		$aConfig["isHomologacao"] = $isHomologacao;

		if (isset($aConfig['pfx']) && isset($aConfig['pwdPFX']))
		$this->createTempFiles($aConfig['pfx'], $aConfig['pwdPFX'], $aConfig['cnpj'], $aConfig);

		parent::__construct($aConfig['privKey'], $aConfig['pubKey'], $aConfig['certKey']);
		
		$aConfig = PQDUtil::setDefault($aConfig, array(
			/*
			'proxy' => array(
				'ip' => '::1',
				'port' => '80',
				'user' => 'user',
				'pass' => 'pass'
			),
			*/
			'cpfCnpj' => '',
			'insMunicipal' => '',
			'curl' => array(
				'header' => array(
					'Content-Type: text/xml'
				), 
				'port' => 443 //Quando porta 443 envia faz autenticação SSL na conexão
			),
			'soap' => array(
				'version' => '1.1'
			),
			'escapeAsHTML' => false,
			'retirarAcentos' => false,//Somente funciona a remoção dos acentos se também escapar o HTML, ou seja para retirar os acentos escapeAsHTML tem que ser true
			'autenticacao' => array(
				'type' => 'none',
				//'type' => 'xml',
				//'type' => 'soap',

				'tagUsuario' => 'username',
				'tagPassword' => 'password',
				'tagChavePrivada' => 'chavePrivada'
			),
			'homologacao' => array(
				'wsdl' => '',
				'usuario' => '',
				'senha' => '',
				'chavePrivada' => ''
			),

			'producao' => array(
				'wsdl' => '',
				'usuario' => '',
				'senha' => '',
				'chavePrivada' => ''
			),
			'hasConsultaUrlNfse' => false,
			'pathSaveXMLs' => realpath(dirname(__FILE__) . '/../xml') . '/',
			'templates' => array(
				'path' => realpath(dirname(__FILE__) . '/../templates/') . '/',
				'folder' => 'abrasf-v2.4',
				'rps' => 'Rps.xml',
				'enviarLoteRps' => 'EnviarLoteRps.xml',
				'deducao' => 'Deducao.xml',
				'gerarNfse' => 'GerarNfseEnvio.xml',
				'consultarNFSePorRps' => 'ConsultarNfseRpsEnvio.xml',
				'consultarLoteRps' => 'ConsultarLoteRpsEnvio.xml',
				'consultarUrlNfse' => 'ConsultarUrlNfseEnvio.xml',
				'cancelarNfse' => 'CancelarNfseEnvio.xml',
				'soap' => 'Soap.xml'
			),
			'metodos' => array(
				'gerarNfse' => array(
					//'typeCommunication' => 'soap',
					//'typeCommunication' => 'curl',
					'action' => 'gerarNfse',
					//'returnType' => 'string',
					'nameSpace' => '',
					'tagSign' => 'InfDeclaracaoPrestacaoServico', 
					'tagAppend' => 'Rps',
					'tagMap' => array(
						'return' => 'gerarNfseResponse'
					),
					'search' => array("\r\n", "\n", "\r", "\t"),
					'replace' => ""
				),
				'enviarLoteRps' => array(
					'typeCommunication' => 'soap',
					'action' => 'RecepcionarLoteRps',
					'actionSoapHeader' => 'RecepcionarLoteRps',
					'nameSpace' => '',
					'signRps' => false,//true irá assinar os RPS, utilizando as tags informadas no metodo gerarNfse (tagSign, tagAppend, nameSpace)
					'tagSign' => 'LoteRps', 
					'tagAppend' => 'EnviarLoteRpsEnvio',
					'tagMap' => array(
						'return' => 'RecepcionarLoteRpsResponse',
						'respostaLote' => 'EnviarLoteRpsResposta'
					),
				),
				'consultarNFSePorRps' => array(
					'action' => 'consultarNfseRps',
					'signConsulta' => false,
					'nameSpace' => '',
					'tagSign' => 'Pedido', 
					'tagAppend' => 'ConsultarNfseRpsEnvio',
					'tagMap' => array(
						'return' => 'consultarNfseRpsResponse'
					)
				),
				'consultarLoteRps' => array(
					'action' => 'ConsultarLoteRps',
					'tagMap' => array(
						'return' => 'ConsultarLoteRpsResponse',
						'respostaConsultaLote' => 'ConsultarLoteRpsResposta'
					)
				),
				'consultarUrlNfse' => array(
					'action' => 'ConsultarUrlNfse',
					'signConsulta' => false,
					'nameSpace' => '',
					'tagSign' => 'Pedido', 
					'tagAppend' => 'ConsultarUrlNfseEnvio',
					'tagMap' => array(
						'return' => 'ConsultarUrlNfseResposta'
					)
				),
				'cancelarNfse' => array(
					'allowCancel' => true,//Tem prefeituras que não permitem cancelamento pelo WebService, deste modo o sistema nem transmite somente retorna que foi cancelado
					'action' => 'cancelarNfse',
					'nameSpace' => '',
					'tagSign' => 'InfPedidoCancelamento', 
					'tagAppend' => 'Pedido',
					'codCancelamento' => '1',//1 - Erro na emissao
					'tagMap' => array(
						'return' => 'cancelarNfseResponse'
					)
				)
			)/*,
			'fields' => array(
				'dataEmissao' => array(
					'fn' => 'substr',
					'args' => array(0, 10)
				)
			)*/
		));

		//Compatibilidade com outras versões
		$aConfig['cpfCnpj'] = isset($aConfig['cnpj']) && !empty($aConfig['cnpj']) && empty($aConfig['cpfCnpj']) ? $aConfig['cnpj'] : $aConfig['cpfCnpj'];
		$aConfig['insMunicipal'] = isset($aConfig['inscMunicipal']) && !empty($aConfig['inscMunicipal']) && empty($aConfig['insMunicipal']) ? $aConfig['inscMunicipal'] : $aConfig['insMunicipal'];

		if($isHomologacao && empty($aConfig['homologacao']['wsdl']))
			throw new \Exception("URL do WebService de homologação não configurado!");

		if(!$isHomologacao && empty($aConfig['producao']['wsdl']))
			throw new \Exception("URL do WebService de produção não configurado!");

		$this->aConfig = $aConfig;
		$this->isHomologacao = $isHomologacao;
	}

	public function cancelarNfse(NFSeGenericoCancelarNfseEnvio $oCancelar) {

		$fileName = $oCancelar->Numero . ".xml";
		$metodo = 'cancelarNfse';

		$oCancelar = $this->escapeTextObj($oCancelar);

		$cpfCnpj = is_null($oCancelar->CpfCnpj) ? $this->aConfig['cpfCnpj'] : $oCancelar->CpfCnpj;
		$inscMunicipal = is_null($oCancelar->InscricaoMunicipal) ? $this->aConfig['insMunicipal'] : $oCancelar->InscricaoMunicipal;

		$oCancelar->CodigoCancelamento = is_null($oCancelar->CodigoCancelamento) ? $this->aConfig['metodos'][$metodo]['codCancelamento'] : $oCancelar->CodigoCancelamento;

		//Quando a prefeitura não permite cancelamento pelo WebService
		if(!$this->aConfig['metodos'][$metodo]['allowCancel']){
			return array(
				'ListaMensagemRetorno' => array(), 
				'RetCancelamento' => array( 
					'NfseCancelamento' => array( 
						array( 
							'Confirmacao' => array(
								'Pedido' => array(
									'InfPedidoCancelamento' => array(
										'IdentificacaoNfse' => array(
											'Numero' => $oCancelar->Numero,
											'CodigoVerificacao' => $oCancelar->CodigoVerificacao,
											'CpfCnpj' => $oCancelar->CpfCnpj,
											'InscricaoMunicipal' => $oCancelar->InscricaoMunicipal,
											'CodigoMunicipio' => $oCancelar->CodigoMunicipio
										),
										'CodigoCancelamento' => $oCancelar->CodigoCancelamento,
										'DescricaoCancelamento' => $oCancelar->DescricaoCancelamento
									)
								),
								'DataHora' => str_replace(" ", "T", date('Y-m-d H:i:s'))
							)
						 ) 
					) 
				)
			);
		}

		//Gerar NFSe
		$tpl = $this->getTemplate($metodo);

		$aReplaces = $this->retReplaceUsuarios('xml');
		
		$aReplaces['replace']['{@idInfPedidoCancelamento}'] = $oCancelar->idInfPedidoCancelamento;
		$aReplaces['replace']['{@Numero}'] = $oCancelar->Numero;
		$aReplaces['replace']['{@CodigoVerificacao}'] = $oCancelar->CodigoVerificacao;
		$aReplaces['replace']['{@CodigoMunicipio}'] = $oCancelar->CodigoMunicipio;
		$aReplaces['replace']['{@CodigoCancelamento}'] = $oCancelar->CodigoCancelamento;
		$aReplaces['replace']['{@DescricaoCancelamento}'] = $oCancelar->DescricaoCancelamento;

		$aReplaces['replace']['{@Cpf}'] = $cpfCnpj;
		$aReplaces['replace']['{@Cnpj}'] = $cpfCnpj;
		$aReplaces['replace']['{@InscricaoMunicipal}'] = $inscMunicipal;

		foreach($aReplaces['replace'] as $k => $v){
			$field = str_replace(['{@', '}'], '', $k);
			$field = strtolower(substr($field, 0, 1)) . substr($field, 1);
			$aReplaces['replace'][$k] = $this->applyFnField($field, $v);
		}

		$aReplaces['ifs'][] = array('begin' => '{@ifCpf}', 'end' => '{@endifCpf}', 'bool' => strlen($cpfCnpj) == 11);
		$aReplaces['ifs'][] = array('begin' => '{@ifCnpj}', 'end' => '{@endifCnpj}', 'bool' => strlen($cpfCnpj) == 14);
		$aReplaces['ifs'][] = array('begin' => '{@ifInscricaoMunicipal}', 'end' => '{@endifInscricaoMunicipal}', 'bool' => !empty($inscMunicipal));
		$aReplaces['ifs'][] = array('begin' => '{@ifCodigoCancelamento}', 'end' => '{@endifCodigoCancelamento}', 'bool' => !is_null($oCancelar->CodigoCancelamento));

		$xml = $this->retXML(PQDUtil::procTplText($tpl, $aReplaces['replace'], $aReplaces['ifs']));
		$xml = $this->signXML($xml, 
			$this->aConfig['metodos'][$metodo]['tagSign'], 
			$this->aConfig['metodos'][$metodo]['tagAppend'], 
			$this->aConfig['metodos'][$metodo]['nameSpace'],
			true
		);

		$this->saveXML($xml, $metodo . '-' . $fileName);

		return $this->procReturn($this->makeSOAPRequest($metodo, $xml, $fileName), $metodo);
	}
	
	/**
	 * Consulta uma NFSe por RPS
	 *
	 * @param NFSeGenericoIdentificacaoRps $oIdentificacaoRps
	 * @return string
	 */
	public function consultarNFSePorRps(NFSeGenericoIdentificacaoRps $oIdentificacaoRps){

		$fileName = $oIdentificacaoRps->Numero . "-" . $oIdentificacaoRps->Serie . ".xml";
		$metodo = 'consultarNFSePorRps';

		//Gerar NFSe
		$tpl = $this->getTemplate($metodo);

		$aReplaces = $this->retReplaceUsuarios('xml');
		$aReplaces['replace']['{@Numero}'] = $oIdentificacaoRps->Numero;
		$aReplaces['replace']['{@Serie}'] = $oIdentificacaoRps->Serie;
		$aReplaces['replace']['{@SerieRps}'] = $oIdentificacaoRps->Serie;
		$aReplaces['replace']['{@SerieRpsAsInt}'] = (int)$oIdentificacaoRps->Serie;
		$aReplaces['replace']['{@Tipo}'] = $oIdentificacaoRps->Tipo;

		$aReplaces['replace']['{@CpfPrestador}'] = $this->aConfig['cpfCnpj'];
		$aReplaces['replace']['{@CnpjPrestador}'] = $this->aConfig['cpfCnpj'];
		$aReplaces['replace']['{@InscricaoMunicipal}'] = $this->aConfig['insMunicipal'];

		foreach($aReplaces['replace'] as $k => $v){
			$field = str_replace(['{@', '}'], '', $k);
			$field = strtolower(substr($field, 0, 1)) . substr($field, 1);
			$aReplaces['replace'][$k] = $this->applyFnField($field, $v);
		}

		$aReplaces['ifs'][] = array('begin' => '{@ifCpfPrestador}', 'end' => '{@endifCpfPrestador}', 'bool' => strlen($this->aConfig['cpfCnpj']) == 11);
		$aReplaces['ifs'][] = array('begin' => '{@ifCnpjPrestador}', 'end' => '{@endifCnpjPrestador}', 'bool' => strlen($this->aConfig['cpfCnpj']) == 14);
		$aReplaces['ifs'][] = array('begin' => '{@ifInscricaoMunicipal}', 'end' => '{@endifInscricaoMunicipal}', 'bool' => !empty($this->aConfig['insMunicipal']) );

		$xml = $this->retXML(PQDUtil::procTplText($tpl, $aReplaces['replace'], $aReplaces['ifs']));
		
		if( $this->aConfig['metodos'][$metodo]['signConsulta'] ){
			$xml = $this->signXML(
				$xml, 
				$this->aConfig['metodos'][$metodo]['tagSign'], 
				$this->aConfig['metodos'][$metodo]['tagAppend'], 
				$this->aConfig['metodos'][$metodo]['nameSpace'],
				true
			);		
		}

		$this->saveXML($xml, $metodo . '-' . $fileName);

		return $this->procReturn($this->makeSOAPRequest($metodo, $xml, $fileName), $metodo);
	}

	private function escapeTextObj($obj){

		if( $this->aConfig['escapeAsHTML'] )
			PQDUtil::escapeHTML( $obj );
		else
			PQDUtil::recursive($obj, function($data){
				return htmlspecialchars($data);
			});
		
		if( $this->aConfig['retirarAcentos'] ){
			PQDUtil::recursive($obj, function($str){
				return preg_replace("/&([aeiouc])(tilde|acute|grave|circ|cedil);/i", "$1", $str);
			});	
		}

		return $obj;
	}

	/**
	 * Gera uma NFSe a partir do end point 'gerarNfse' do servidor comunicado
	 * 
	 * @param NFSeGenericoInfRps $oRps
	 * @param string $id
	 * 
	 */
	public function gerarNfse(NFSeGenericoInfRps $oRps) {
		
		$fileName = $oRps->IdentificacaoRps->Numero . "-" . $oRps->IdentificacaoRps->Serie . ".xml";
		$metodo = 'gerarNfse';
		
		//RPS
		$xml = $this->retXMLRps($oRps);
		
		$search = PQDUtil::retDefault($this->aConfig['metodos'][$metodo], 'search', null);
		$search = is_null($search) ? array("\r\n", "\n", "\r", "\t") : array_map('stripcslashes', $search);

		$replace = PQDUtil::retDefault($this->aConfig['metodos'][$metodo], 'replace', null);
		$replace = is_null($replace) ? "" : ( is_array($replace) ? array_map('stripcslashes', $replace) : $replace );
		$xml = $this->signXML(
			$xml, 
			$this->aConfig['metodos'][$metodo]['tagSign'], 
			$this->aConfig['metodos'][$metodo]['tagAppend'], 
			$this->aConfig['metodos'][$metodo]['nameSpace'],
			true,
			true,
			$search,
			$replace
		);

		if($this->isHomologacao)
			$this->saveXML($xml, $metodo . '-rps-' . $fileName);

		//Gerar NFSe
		$tpl = $this->getTemplate($metodo);

		$aReplaces = $this->retReplaceUsuarios('xml');
		$aReplaces['replace']['{@Rps}'] = $xml;

		$xml = $this->retXML(PQDUtil::procTplText($tpl, $aReplaces['replace'], $aReplaces['ifs']));
		$this->saveXML($xml, $metodo . '-' . $fileName);

		return $this->procReturn($this->makeSOAPRequest($metodo, $xml, $fileName), $metodo);
	}

	/**
	 * @param array[NFSeGenericoInfRps] $oRps
	 * @param string $id
	 * 
	 */
	public function enviarLoteRps(array $aRps, NFSeGenericoLoteRps $oLote) {

		$fileName = $oLote->NumeroLote . ".xml";
		
		$metodo = 'enviarLoteRps';
		$xml = "";

		//Buscando textos que devem ser substituidos antes da assinatura.
		$search = PQDUtil::retDefault($this->aConfig['metodos'][$metodo], 'search', null);
		$search = is_null($search) ? array("\r\n", "\n", "\r", "\t") : array_map('stripcslashes', $search);

		$replace = PQDUtil::retDefault($this->aConfig['metodos'][$metodo], 'replace', null);
		$replace = is_null($replace) ? "" : ( is_array($replace) ? array_map('stripcslashes', $replace) : $replace );

		/**
		 * @var NFSeGenericoInfRps $oRps
		 */
		foreach($aRps as $oRps){
			$oRps->idInfDeclaracaoPrestacaoServico = $oRps->idRps = null;

			$rps = $this->retXMLRps($oRps);

			if($this->aConfig['metodos'][$metodo]['signRps']){
				$rps = $this->signXML(
					$rps, 
					$this->aConfig['metodos']['gerarNfse']['tagSign'], 
					$this->aConfig['metodos']['gerarNfse']['tagAppend'], 
					$this->aConfig['metodos']['gerarNfse']['nameSpace'],
					true,
					true,
					$search,
					$replace
				);		
			}

			$xml .= $rps;
		}

		$aReplaces = $this->retReplaceUsuarios('xml');

		$aReplace = $aReplaces['replace'];
		$aReplace['{@idLote}'] = $oLote->NumeroLote;
		$aReplace['{@CpfPrestador}'] = $this->aConfig['cpfCnpj'];
		$aReplace['{@CnpjPrestador}'] = $this->aConfig['cpfCnpj'];
		$aReplace['{@InscricaoMunicipalPrestador}'] = $this->aConfig['insMunicipal'];
		$aReplace['{@ListaRps}'] = $xml;
		$aReplace['{@QuantidadeRps}'] = count($aRps);
		$aReplace['{@NumeroLote}'] = $oLote->NumeroLote;

		foreach($aReplace as $k => $v){
			$field = str_replace(['{@', '}'], '', $k);
			$field = strtolower(substr($field, 0, 1)) . substr($field, 1);
			$aReplace[$k] = $this->applyFnField($field, $v);
		}

		$aIfs = $aReplaces['ifs'];
		$aIfs[] = array('begin' => '{@ifCpfPrestador}', 'end' => '{@endifCpfPrestador}', 'bool' => strlen($oRps->Prestador->CpfCnpj) == 11);
		$aIfs[] = array('begin' => '{@ifCnpjPrestador}', 'end' => '{@endifCnpjPrestador}', 'bool' => strlen($oRps->Prestador->CpfCnpj) == 14);

		$tplLista = $this->getTemplate('enviarLoteRps');

		$xml = $this->retXML(PQDUtil::procTplText($tplLista, $aReplace, $aIfs));

		//Assinando
		$xml = $this->signXML(
			$xml, 
			$this->aConfig['metodos'][$metodo]['tagSign'], 
			$this->aConfig['metodos'][$metodo]['tagAppend'], 
			$this->aConfig['metodos'][$metodo]['nameSpace'],
			true,
			true,
			$search,
			$replace
		);
		
		if($this->isHomologacao)
			$this->saveXML($xml, $metodo . '-' . $fileName);

		return $this->procReturn($this->makeSOAPRequest($metodo, $xml, $fileName), $metodo);
	}

	
	/**
	 * @param NFSeGenericoConsultarLote $oRps
	 * 
	 */
	public function consultarLoteRps(NFSeGenericoConsultarLote $oRps) {
		
		$metodo = 'consultarLoteRps';
		$xml = "";

		$fileName = $oRps->Protocolo . ".xml";

		$aReplace = array(
			'{@CpfPrestador}' => $this->aConfig['cpfCnpj'],
			'{@CnpjPrestador}' => $this->aConfig['cpfCnpj'],
			'{@InscricaoMunicipalPrestador}' => $this->aConfig['insMunicipal'],
			'{@Protocolo}' => $oRps->Protocolo
		);

		foreach($aReplace as $k => $v){
			$field = str_replace(['{@', '}'], '', $k);
			$field = strtolower(substr($field, 0, 1)) . substr($field, 1);
			$aReplace[$k] = $this->applyFnField($field, $v);
		}

		$aIfs = array(
			array('begin' => '{@ifCpfPrestador}', 'end' => '{@endifCpfPrestador}', 'bool' => strlen($this->aConfig['cpfCnpj']) == 11),
			array('begin' => '{@ifCnpjPrestador}', 'end' => '{@endifCnpjPrestador}', 'bool' => strlen($this->aConfig['cpfCnpj']) == 14),
		);

		$tplConsulta = $this->getTemplate('consultarLoteRps');
		
		$xml = $this->retXML(PQDUtil::procTplText($tplConsulta, $aReplace, $aIfs));

		if($this->isHomologacao)
			$this->saveXML($xml, $metodo . '-consultarLoteRps.xml');

		return $this->procReturn($this->makeSOAPRequest($metodo, $xml, $fileName), $metodo);
	}

	/**
	 * @param NFSeGenericoConsultarUrlNfse $oConsulta
	 * 
	 */
	public function consultarUrlNfse(NFSeGenericoConsultarUrlNfse $oConsulta) {
		
		$metodo = 'consultarUrlNfse';
		$xml = "";

		$fileName = $oConsulta->NumeroNfse . ".xml";

		$aReplace = array(
			'{@CpfPrestador}' => $this->aConfig['cpfCnpj'],
			'{@CnpjPrestador}' => $this->aConfig['cpfCnpj'],
			'{@InscricaoMunicipalPrestador}' => $this->aConfig['insMunicipal'],
			'{@NumeroRps}' => $oConsulta->IdentificacaoRps->Numero,
			'{@SerieRps}' => $oConsulta->IdentificacaoRps->Serie,
			'{@Tipo}' => $oConsulta->IdentificacaoRps->Tipo,
			'{@NumeroNfse}' => $oConsulta->NumeroNfse,
			'{@DataInicialEmissao}' => $oConsulta->DataInicialEmissao,
			'{@DataFinalEmissao}' => $oConsulta->DataFinalEmissao,
			'{@DataInicialCompetencia}' => $oConsulta->DataInicialCompetencia,
			'{@DataFinalCompetencia}' => $oConsulta->DataFinalCompetencia,
			'{@CnpjTomador}' => $oConsulta->Tomador->CpfCnpj,
			'{@CpfTomador}' => $oConsulta->Tomador->CpfCnpj,
			'{@InscricaoMunicipalTomador}' => $oConsulta->Tomador->InscricaoMunicipal,
			'{@CnpjIntermediario}' => $oConsulta->Intermediario->CpfCnpj,
			'{@CpfIntermediario}' => $oConsulta->Intermediario->CpfCnpj,
			'{@InscricaoMunicipalIntermediario}' => $oConsulta->Intermediario->InscricaoMunicipal,
			'{@Pagina}' => $oConsulta->Pagina
		);

		foreach($aReplace as $k => $v){
			$field = str_replace(['{@', '}'], '', $k);
			$field = strtolower(substr($field, 0, 1)) . substr($field, 1);
			$aReplace[$k] = $this->applyFnField($field, $v);
		}

		$aIfs = array(
			['begin' => '{@ifCnpjPrestador}', 'end' => '{@endifCnpjPrestador}', 'bool' => strlen($this->aConfig['cpfCnpj']) == 14],
			['begin' => '{@ifCpfPrestador}', 'end' => '{@endifCpfPrestador}', 'bool' => strlen($this->aConfig['cpfCnpj']) == 11],
			['begin' => '{@ifInscricaoMunicipalPrestador}', 'end' => '{@endifInscricaoMunicipalPrestador}', 'bool' => !empty($this->aConfig['insMunicipal'])],
			['begin' => '{@ifIdentificacaoRps}', 'end' => '{@endifIdentificacaoRps}', 'bool' => !empty($oConsulta->IdentificacaoRps->Numero) ],
			['begin' => '{@ifNumeroNfse}', 'end' => '{@endifNumeroNfse}', 'bool' => !empty($oConsulta->NumeroNfse)],
			['begin' => '{@ifPeriodoEmissao}', 'end' => '{@endifPeriodoEmissao}', 'bool' => !empty($oConsulta->DataInicialEmissao)],
			['begin' => '{@ifPeriodoCompetencia}', 'end' => '{@endifPeriodoCompetencia}', 'bool' => !empty($oConsulta->DataInicialEmissao)],
			['begin' => '{@ifTomador}', 'end' => '{@endifTomador}', 'bool' => !empty($oConsulta->Tomador->CpfCnpj)],
			['begin' => '{@ifCnpjTomador}', 'end' => '{@endifCnpjTomador}', 'bool' => strlen($oConsulta->Tomador->CpfCnpj) == 14],
			['begin' => '{@ifCpfTomador}', 'end' => '{@endifCpfTomador}', 'bool' => strlen($oConsulta->Tomador->CpfCnpj) == 11],
			['begin' => '{@ifIntermediario}', 'end' => '{@endifIntermediario}', 'bool' => !empty($oConsulta->Intermediario->CpfCnpj)],
			['begin' => '{@ifCnpjIntermediario}', 'end' => '{@endifCnpjIntermediario}', 'bool' => strlen($oConsulta->Intermediario->CpfCnpj) == 14],
			['begin' => '{@ifCpfIntermediario}', 'end' => '{@endifCpfIntermediario}', 'bool' => strlen($oConsulta->Intermediario->CpfCnpj) == 11]
		);

		$tplConsulta = $this->getTemplate($metodo);
		
		$xml = $this->retXML(PQDUtil::procTplText($tplConsulta, $aReplace, $aIfs));

		if($this->aConfig['metodos'][$metodo]['signConsulta']){
			$xml = $this->signXML($xml, 
				$this->aConfig['metodos'][$metodo]['tagSign'], 
				$this->aConfig['metodos'][$metodo]['tagAppend'], 
				$this->aConfig['metodos'][$metodo]['nameSpace'],
				true
			);
		}

		if($this->isHomologacao)
			$this->saveXML($xml, $metodo . '-consultarLoteRps.xml');

		return $this->procReturn($this->makeSOAPRequest($metodo, $xml, $fileName), $metodo);
	}

	/**
	 * Salva o XML em um arquivo, caso tenha sido configurado o caminho para ser salvo 'pathSaveXMLs'
	 * 
	 * @param string $xml
	 * @param string $name
	 * 
	 */
	private function saveXML($xml, $name){

		if(isset($this->aConfig['pathSaveXMLs']) && is_dir($this->aConfig['pathSaveXMLs']))
			file_put_contents($this->aConfig['pathSaveXMLs'] . $name, $xml);
	}

	/**
	 * Retorna a string de um template
	 * 
	 * @param string $template
	 * 
	 * @return string
	 */
	private function getTemplate($template){
		if(is_file($this->aConfig['templates']['path'] . $this->aConfig['templates']['folder'] . '/' . $this->aConfig['templates'][$template]))
			return file_get_contents($this->aConfig['templates']['path'] . $this->aConfig['templates']['folder'] . '/' . $this->aConfig['templates'][$template]);
		
		throw new \Exception("Template: " . $template . " não encontrado!");
	}

	/**
	 * Faz uma requisição SOAP
	 * 
	 * @return string
	 */
	private function makeSOAPRequest($metodo, $xml, $fileName){

		$action = $this->aConfig['metodos'][$metodo]['action'];
		
		$xml = $this->retXMLSoap($xml, $action);

		if($this->isHomologacao)
			$this->saveXML($xml, $metodo . '-soap-' . $fileName);
		
		$soapReturn = $this->sendRequest($metodo, $xml);

		//if($this->isHomologacao)
		//Alterado para sempre salvar o retorno, atualmente não está salvando
		$this->saveXML($soapReturn, $metodo . '-soap-return-' . $fileName);
		
		return $soapReturn;
	}

	/**
	 * Envia uma requisição de acordo com o metodo e tipo de comunicação no metodo
	 * 
	 * @param string $metodo
	 * @param string $xml
	 * 
	 * @return string
	 */
	private function sendRequest($metodo, $xml){

		$action = PQDUtil::retDefault($this->aConfig['metodos'][$metodo], 'actionSoapHeader', $this->aConfig['metodos'][$metodo]['action']);


		$wsdl = $this->isHomologacao ? $this->aConfig['homologacao']['wsdl'] : $this->aConfig['producao']['wsdl'];
		$url = $this->isHomologacao ? PQDUtil::retDefault($this->aConfig['homologacao'], 'url', $wsdl) : PQDUtil::retDefault($this->aConfig['producao'], 'url', $wsdl);

		$typeCommunication = PQDUtil::retDefault($this->aConfig['metodos'][$metodo], 'typeCommunication', 'soap');

		switch($typeCommunication){
			case 'curl':

				$curl = PQDUtil::retDefault($this->aConfig, 'curl', array());

				$headers = array();
				foreach(PQDUtil::retDefault($curl, 'header', array() ) as $header)
					$headers[] = $header; 
				foreach(PQDUtil::retDefault($this->aConfig['metodos'][$metodo], 'header', array() ) as $header)
					$headers[] = $header; 

				return $this->curl(
					$url, 
					$xml, 
					count($headers) == 0 ? null : $headers, 
					PQDUtil::retDefault($curl, 'port', 443), 
					PQDUtil::retDefault($this->aConfig, 'proxy', null)
				);

			break;
			case 'soap':

				$soap = PQDUtil::retDefault($this->aConfig, 'soap', array());

				return $this->soap($wsdl, $url, $action, $xml, PQDUtil::retDefault($soap, 'version', '1.1'));
			break;
		}
	}

	/**
	 * Retorna um array com os replaces de usuário, senha e chave privada
	 * 
	 * Caso o tipo de autenticação configurado ($this->aConfig) seja igual a váriavel $typeAutentication retorna os array preenchidos
	 * Caso a tipo de autenticação configurado seja diferente da váriavel retorna array vazio.
	 * 
	 * @param string $typeAutentication
	 * 
	 * @return array
	 */
	private function retReplaceUsuarios($typeAutentication){

		if($this->aConfig['autenticacao']['type'] == $typeAutentication){

			if($this->isHomologacao){
				$usuario = $this->aConfig['homologacao']['usuario'];
				$senha = $this->aConfig['homologacao']['senha'];
				$chavePrivada = $this->aConfig['homologacao']['chavePrivada'];
			}
			else{
				$usuario = $this->aConfig['producao']['usuario'];
				$senha = $this->aConfig['producao']['senha'];
				$chavePrivada = $this->aConfig['producao']['chavePrivada'];
			}
	
			$aReplace = array(
				'{@' . $this->aConfig['autenticacao']['tagUsuario'] . '}' => $usuario,
				'{@' . $this->aConfig['autenticacao']['tagPassword'] . '}' => $senha,
				'{@' . $this->aConfig['autenticacao']['tagChavePrivada'] . '}' => $chavePrivada
			);
	
			$aIfs = array(
				array(
					'begin' => '{@if' . ucwords($this->aConfig['autenticacao']['tagUsuario']) . '}',
					'end' => '{@endif' . ucwords($this->aConfig['autenticacao']['tagUsuario']) . '}', 
					'bool' => !empty($usuario)
				),
				array(
					'begin' => '{@if' . ucwords($this->aConfig['autenticacao']['tagPassword']) . '}',
					'end' => '{@endif' . ucwords($this->aConfig['autenticacao']['tagPassword']) . '}', 
					'bool' => !empty($senha)
				),
				array(
					'begin' => '{@if' . ucwords($this->aConfig['autenticacao']['tagChavePrivada']) . '}',
					'end' => '{@endif' . ucwords($this->aConfig['autenticacao']['tagChavePrivada']) . '}', 
					'bool' => !empty($chavePrivada)
				)
			);
	
			return array('replace' => $aReplace, 'ifs' => $aIfs);
		}

		return array( 'replace' => array(), 'ifs' => array() );
	}

	/**
	 * Remove os comentários de um documento
	 * 
	 * @param \DOMDocument $oDocument
	 * 
	 * @return void
	 */
	private function removeComments(\DOMDocument $oDocument){
		$xpath = new \DOMXPath($oDocument);
		foreach ($xpath->query('//comment()') as $comment)
			$comment->parentNode->removeChild($comment);
	}

	/**
	 * Retorna o XML sem espaços em branco, sem tags em branco e sem comentários
	 * 
	 * Caso o parâmetro $firstChild true retorna sem '<?xml version="1.0" encoding="utf-8"?>'
	 * Caso o parâmetro $firstChild false retorna com '<?xml version="1.0" encoding="utf-8"?>'
	 * 
	 * @param string $xml 
	 * @param bool $xml 
	 * 
	 * @return string
	 */
	private function retXML($xml, $firstChild = true){

		$document = new NFSeDocument();
		$document->loadXML($xml, LIBXML_NOBLANKS | LIBXML_NOERROR | LIBXML_NOWARNING  );

		$this->removeComments($document);

		return trim($document->saveXML($firstChild ? $document->firstChild : null, LIBXML_NOEMPTYTAG));
	}

	private function applyFnField($field, $value){
		$fns = PQDUtil::retDefault($this->aConfig, 'fields', array());

		$fn = PQDUtil::retDefault($fns, $field);

		if(is_null($fn))
			return $value;
		else{
			$args = PQDUtil::retDefault($fn, 'args', array());
			array_unshift($args, $value);
			return call_user_func_array($fn['fn'],  $args);
		}
	}

	/**
	 * Retorna o XML do RPS de acordo com o template 'rps'
	 * 
	 * @param NFSeGenericoInfRps $oRps
	 * @param string $id
	 * 
	 * @return string
	 */
	private function retXMLRps(NFSeGenericoInfRps $oRps){

		$oRps = $this->escapeTextObj($oRps);

		$tplRps = $this->getTemplate('rps');
		$tplDeducao = $this->getTemplate('deducao');

		$deducoes = '';
		foreach($oRps->aDeducoes as $deducao){
			/**
			 * @var NFSeGenericoDeducao $deducao
			 */
			$aReplace = array(
				'{@TipoDeducao}' => $deducao->TipoDeducao,
				'{@DescricaoDeducao}' => $deducao->DescricaoDeducao,
				'{@CodigoMunicipioGerador}' => $deducao->IdentificacaoDocumentoDeducao->CodigoMunicipioGerador,
				'{@NumeroNfse}' => $deducao->IdentificacaoDocumentoDeducao->NumeroNfse,
				'{@CodigoVerificacao}' => $deducao->IdentificacaoDocumentoDeducao->CodigoVerificacao,
				'{@NumeroNfe}' => $deducao->IdentificacaoDocumentoDeducao->NumeroNfe,
				'{@UfNfe}' => $deducao->IdentificacaoDocumentoDeducao->UfNfe,
				'{@ChaveAcessoNfe}' => $deducao->IdentificacaoDocumentoDeducao->ChaveAcessoNfe,
				'{@IdentificacaoDocumento}' => $deducao->IdentificacaoDocumentoDeducao->IdentificacaoDocumento,
				'{@Cpf}' => $deducao->DadosFornecedor->CpfCnpj,
				'{@Cnpj}' => $deducao->DadosFornecedor->CpfCnpj,
				'{@NifFornecedor}' => $deducao->DadosFornecedor->NifFornecedor,
				'{@CodigoPais}' => $deducao->DadosFornecedor->CodigoPais,
				'{@DataEmissao}' => $deducao->DataEmissao,
				'{@ValorDedutivel}' => $deducao->ValorDedutivel,
				'{@ValorUtilizadoDeducao}' => $deducao->ValorUtilizadoDeducao
			);

			$aIfs = array(
				array('begin' => '{@ifIdentificacaoNfse}', 'end' => '{@endifIdentificacaoNfse}', 'bool' => $deducao->IdentificacaoDocumentoDeducao->tpDocumento == 0),
				array('begin' => '{@ifIdentificacaoNfe}', 'end' => '{@endifIdentificacaoNfe}', 'bool' => $deducao->IdentificacaoDocumentoDeducao->tpDocumento == 1),
				array('begin' => '{@ifOutroDocumento}', 'end' => '{@endifOutroDocumento}', 'bool' => $deducao->IdentificacaoDocumentoDeducao->tpDocumento == 2),
				array('begin' => '{@ifIdentificacaoFornecedor}', 'end' => '{@endifIdentificacaoFornecedor}', 'bool' => is_null($deducao->DadosFornecedor->CodigoPais)),
				array('begin' => '{@ifCpf}', 'end' => '{@endifCpf}', 'bool' => strlen($deducao->DadosFornecedor->CpfCnpj) == 11),
				array('begin' => '{@ifCnpj}', 'end' => '{@endifCnpj}', 'bool' => strlen($deducao->DadosFornecedor->CpfCnpj) == 14),
				array('begin' => '{@ifFornecedorExterior}', 'end' => '{@endifFornecedorExterior}', 'bool' => !is_null($deducao->DadosFornecedor->CodigoPais)),
				array('begin' => '{@ifNifFornecedor}', 'end' => '{@endifNifFornecedor}', 'bool' => !is_null($deducao->DadosFornecedor->NifFornecedor))
			);

			$deducoes .= PQDUtil::procTplText($tplDeducao, $aReplace, $aIfs);
		}

		//RPS
		$aReplace = array(
			'{@idRps}' => $oRps->idRps,
			'{@idInfDeclaracaoPrestacaoServico}' => $oRps->idInfDeclaracaoPrestacaoServico,
			'{@NumeroRps}' => $oRps->IdentificacaoRps->Numero,
			'{@SerieRps}' => $oRps->IdentificacaoRps->Serie,
			'{@SerieRpsAsInt}' => (int)$oRps->IdentificacaoRps->Serie,
			'{@TipoRps}' => $oRps->IdentificacaoRps->Tipo,
			'{@DataEmissao}' => $oRps->DataEmissao,
			'{@Status}' => $oRps->Status,
			'{@NumeroRpsSubstituido}' => $oRps->RpsSubstituido->Numero,
			'{@SerieRpsSubstituido}' => $oRps->RpsSubstituido->Serie,
			'{@SerieRpsSubstituidoAsInt}' => (int)$oRps->RpsSubstituido->Serie,
			'{@TipoRpsSubstituido}' => $oRps->RpsSubstituido->Tipo,
			'{@Competencia}' => $oRps->Competencia,
			'{@ValorServicos}' => $oRps->Servico->Valores->ValorServicos,
			'{@ValorDeducoes}' => $oRps->Servico->Valores->ValorDeducoes,
			'{@ValorPis}' => $oRps->Servico->Valores->ValorPis,
			'{@ValorCofins}' => $oRps->Servico->Valores->ValorCofins,
			'{@ValorInss}' => $oRps->Servico->Valores->ValorInss,
			'{@ValorIr}' => $oRps->Servico->Valores->ValorIr,
			'{@ValorCsll}' => $oRps->Servico->Valores->ValorCsll,
			'{@OutrasRetencoes}' => $oRps->Servico->Valores->OutrasRetencoes,
			'{@ValTotTributos}' => $oRps->Servico->Valores->ValTotTributos,
			'{@ValorIss}' => $oRps->Servico->Valores->ValorIss,
			'{@Aliquota}' => $oRps->Servico->Valores->Aliquota,
			'{@ValorLiquidoNfse}' => $oRps->Servico->Valores->ValorLiquidoNfse,
			'{@ValorIssRetido}' => $oRps->Servico->Valores->ValorIssRetido,
			'{@BaseCalculo}' => $oRps->Servico->Valores->BaseCalculo,
			'{@DescontoIncondicionado}' => $oRps->Servico->Valores->DescontoIncondicionado,
			'{@DescontoCondicionado}' => $oRps->Servico->Valores->DescontoCondicionado,
			'{@IssRetido}' => $oRps->Servico->IssRetido,
			'{@ResponsavelRetencao}' => $oRps->Servico->ResponsavelRetencao,
			'{@ItemListaServico}' => $oRps->Servico->ItemListaServico,
			'{@CodigoCnae}' => $oRps->Servico->CodigoCnae,
			'{@CodigoTributacaoMunicipio}' => $oRps->Servico->CodigoTributacaoMunicipio,
			'{@CodigoNbs}' => $oRps->Servico->CodigoNbs,
			'{@Discriminacao}' => $oRps->Servico->Discriminacao,
			'{@CodigoMunicipioServico}' => $oRps->Servico->CodigoMunicipio,
			'{@CodigoPaisServico}' => $oRps->Servico->CodigoPais,
			'{@ExigibilidadeISS}' => $oRps->Servico->ExigibilidadeISS,
			'{@IdentifNaoExigibilidade}' => $oRps->Servico->IdentifNaoExigibilidade,
			'{@MunicipioIncidencia}' => $oRps->Servico->MunicipioIncidencia,
			'{@NumeroProcesso}' => $oRps->Servico->NumeroProcesso,
			'{@CpfPrestador}' => $oRps->Prestador->CpfCnpj,
			'{@CnpjPrestador}' => $oRps->Prestador->CpfCnpj,
			'{@InscricaoMunicipalPrestador}' => $oRps->Prestador->InscricaoMunicipal,
			'{@CpfTomadorServico}' => $oRps->Tomador->IdentificacaoTomador->CpfCnpj,
			'{@CnpjTomadorServico}' => $oRps->Tomador->IdentificacaoTomador->CpfCnpj,
			'{@InscricaoMunicipalTomadorServico}' => $oRps->Tomador->IdentificacaoTomador->InscricaoMunicipal,
			'{@NifTomador}' => $oRps->Tomador->NifTomador,
			'{@RazaoSocialTomadorServico}' => $oRps->Tomador->RazaoSocial,
			'{@Endereco}' => $oRps->Tomador->Endereco->Endereco,
			'{@Numero}' => $oRps->Tomador->Endereco->Numero,
			'{@Complemento}' => $oRps->Tomador->Endereco->Complemento,
			'{@Bairro}' => $oRps->Tomador->Endereco->Bairro,
			'{@CodigoMunicipioTomadorServico}' => $oRps->Tomador->Endereco->CodigoMunicipio,
			'{@Uf}' => $oRps->Tomador->Endereco->Uf,
			'{@Cep}' => $oRps->Tomador->Endereco->Cep,
			'{@CodigoPaisEnderecoExterior}' => $oRps->Tomador->Endereco->CodigoPaisEstrangeiro,
			'{@EnderecoCompletoExterior}' => $oRps->Tomador->Endereco->EnderecoCompletoExterior,
			'{@Telefone}' => $oRps->Tomador->Contato->Telefone,
			'{@Email}' => $oRps->Tomador->Contato->Email,
			'{@CpfIntermediario}' => $oRps->IntermediarioServico->IdentificacaoIntermediario->CpfCnpj,
			'{@CnpjIntermediario}' => $oRps->IntermediarioServico->IdentificacaoIntermediario->CpfCnpj,
			'{@InscricaoMunicipalIntermediario}' => $oRps->IntermediarioServico->IdentificacaoIntermediario->InscricaoMunicipal,
			'{@RazaoSocialIntermediario}' => $oRps->IntermediarioServico->RazaoSocial,
			'{@CodigoMunicipioIntermediario}' => $oRps->IntermediarioServico->CodigoMunicipio,
			'{@CodigoObra}' => $oRps->ConstrucaoCivil->CodigoObra,
			'{@Art}' => $oRps->ConstrucaoCivil->Art,
			'{@RegimeEspecialTributacao}' => $oRps->RegimeEspecialTributacao,
			'{@OptanteSimplesNacional}' => $oRps->OptanteSimplesNacional,
			'{@IncentivoFiscal}' => $oRps->IncentivoFiscal,
			'{@IdentificacaoEvento}' => $oRps->Evento->IdentificacaoEvento,
			'{@DescricaoEvento}' => $oRps->Evento->DescricaoEvento,
			'{@InformacoesComplementares}' => $oRps->InformacoesComplementares,
			'{@NaturezaOperacao}' => $oRps->NaturezaOperacao,
			'{@IncentivadorCultural}' => $oRps->IncentivadorCultural,
			'{@Deducoes}' => $deducoes
		);

		foreach($aReplace as $k => $v){
			$field = str_replace(['{@', '}'], '', $k);
			$field = strtolower(substr($field, 0, 1)) . substr($field, 1);
			$aReplace[$k] = $this->applyFnField($field, $v);
		}

		$aIfs = array(
			array('begin' => '{@ifRpsSubstituido}', 'end' => '{@endifRpsSubstituido}', 'bool' => !is_null($oRps->RpsSubstituido->Numero) ),

			['begin' => '{@ifValorDeducoes}', 'end' => '{@endifValorDeducoes}', 'bool' => $oRps->Servico->Valores->ValorDeducoes > 0],
			['begin' => '{@ifValorPis}', 'end' => '{@endifValorPis}', 'bool' => $oRps->Servico->Valores->ValorPis > 0],
			['begin' => '{@ifValorCofins}', 'end' => '{@endifValorCofins}', 'bool' => $oRps->Servico->Valores->ValorCofins > 0],
			['begin' => '{@ifValorInss}', 'end' => '{@endifValorInss}', 'bool' => $oRps->Servico->Valores->ValorInss > 0],
			['begin' => '{@ifValorIr}', 'end' => '{@endifValorIr}', 'bool' => $oRps->Servico->Valores->ValorIr > 0],
			['begin' => '{@ifValorCsll}', 'end' => '{@endifValorCsll}', 'bool' => $oRps->Servico->Valores->ValorCsll > 0],
			['begin' => '{@ifOutrasRetencoes}', 'end' => '{@endifOutrasRetencoes}', 'bool' => $oRps->Servico->Valores->OutrasRetencoes > 0],
			['begin' => '{@ifValTotTributos}', 'end' => '{@endifValTotTributos}', 'bool' => $oRps->Servico->Valores->ValTotTributos > 0],
			['begin' => '{@ifValorIss}', 'end' => '{@endifValorIss}', 'bool' => $oRps->Servico->Valores->ValorIss > 0],
			['begin' => '{@ifAliquota}', 'end' => '{@endifAliquota}', 'bool' => $oRps->Servico->Valores->Aliquota > 0],
			['begin' => '{@ifDescontoIncondicionado}', 'end' => '{@endifDescontoIncondicionado}', 'bool' => $oRps->Servico->Valores->DescontoIncondicionado > 0],
			['begin' => '{@ifDescontoCondicionado}', 'end' => '{@endifDescontoCondicionado}', 'bool' => $oRps->Servico->Valores->DescontoCondicionado > 0],
			
			['begin' => '{@ifCodigoCnae}', 'end' => '{@endifCodigoCnae}', 'bool' => !is_null($oRps->Servico->CodigoCnae) ],

			array('begin' => '{@ifResponsavelRetencao}', 'end' => '{@endifResponsavelRetencao}', 'bool' => !is_null($oRps->Servico->ResponsavelRetencao) ),
			array('begin' => '{@ifCodigoTributacaoMunicipio}', 'end' => '{@endifCodigoTributacaoMunicipio}', 'bool' => !is_null($oRps->Servico->CodigoTributacaoMunicipio) ),
			array('begin' => '{@ifCodigoNbs}', 'end' => '{@endifCodigoNbs}', 'bool' => !is_null($oRps->Servico->CodigoNbs) ),
			array('begin' => '{@ifCodigoPaisServico}', 'end' => '{@endifCodigoPaisServico}', 'bool' => !is_null($oRps->Servico->CodigoPais) ),
			array('begin' => '{@ifIdentifNaoExigibilidade}', 'end' => '{@endifIdentifNaoExigibilidade}', 'bool' => !is_null($oRps->Servico->IdentifNaoExigibilidade) ),
			array('begin' => '{@ifMunicipioIncidencia}', 'end' => '{@endifMunicipioIncidencia}', 'bool' => !is_null($oRps->Servico->MunicipioIncidencia) ),
			array('begin' => '{@ifNumeroProcesso}', 'end' => '{@endifNumeroProcesso}', 'bool' => !is_null($oRps->Servico->NumeroProcesso) ),
			array('begin' => '{@ifCpfPrestador}', 'end' => '{@endifCpfPrestador}', 'bool' => strlen($oRps->Prestador->CpfCnpj) == 11),
			array('begin' => '{@ifCnpjPrestador}', 'end' => '{@endifCnpjPrestador}', 'bool' => strlen($oRps->Prestador->CpfCnpj) == 14),
			array('begin' => '{@ifCpfTomadorServico}', 'end' => '{@endifCpfTomadorServico}', 'bool' => strlen($oRps->Tomador->IdentificacaoTomador->CpfCnpj) == 11),
			array('begin' => '{@ifCnpjTomadorServico}', 'end' => '{@endifCnpjTomadorServico}', 'bool' => strlen($oRps->Tomador->IdentificacaoTomador->CpfCnpj) == 14),
			array('begin' => '{@ifInscricaoMunicipalTomadorServico}', 'end' => '{@endifInscricaoMunicipalTomadorServico}', 'bool' => !empty($oRps->Tomador->IdentificacaoTomador->InscricaoMunicipal)),
			array('begin' => '{@ifNifTomador}', 'end' => '{@endifNifTomador}', 'bool' => !is_null($oRps->Tomador->NifTomador)),
			array('begin' => '{@ifComplementoTomador}', 'end' => '{@endifComplementoTomador}', 'bool' => !empty($oRps->Tomador->Endereco->Complemento)),
			array('begin' => '{@ifEndereco}', 'end' => '{@endifEndereco}', 'bool' => is_null($oRps->Tomador->Endereco->CodigoPaisEstrangeiro) ),
			array('begin' => '{@ifEnderecoExterior}', 'end' => '{@endifEnderecoExterior}', 'bool' => !is_null($oRps->Tomador->Endereco->CodigoPaisEstrangeiro) ),
			array('begin' => '{@ifContato}', 'end' => '{@endifContato}', 'bool' => !is_null($oRps->Tomador->Contato->Email) || !is_null($oRps->Tomador->Contato->Telefone) ),
			array('begin' => '{@ifTelefone}', 'end' => '{@endifTelefone}', 'bool' => !is_null($oRps->Tomador->Contato->Telefone) ),
			array('begin' => '{@ifEmail}', 'end' => '{@endifEmail}', 'bool' => !is_null($oRps->Tomador->Contato->Email) ),
			array('begin' => '{@ifIntermediario}', 'end' => '{@endifIntermediario}', 'bool' => !is_null($oRps->IntermediarioServico->IdentificacaoIntermediario->CpfCnpj) ),
			array('begin' => '{@ifCpfIntermediario}}', 'end' => '{@endifCpfIntermediario}}', 'bool' => strlen($oRps->IntermediarioServico->IdentificacaoIntermediario->CpfCnpj) == 11),
			array('begin' => '{@ifCnpjIntermediario}', 'end' => '{@endifCnpjIntermediario}', 'bool' => strlen($oRps->IntermediarioServico->IdentificacaoIntermediario->CpfCnpj) == 14),
			array('begin' => '{@ifConstrucaoCivil}', 'end' => '{@endifConstrucaoCivil}', 'bool' => !is_null($oRps->ConstrucaoCivil->Art) || !is_null($oRps->ConstrucaoCivil->CodigoObra)),
			array('begin' => '{@ifCodigoObra}', 'end' => '{@endifCodigoObra}', 'bool' => !is_null($oRps->ConstrucaoCivil->CodigoObra)),
			array('begin' => '{@ifArt}', 'end' => '{@endifArt}', 'bool' => !is_null($oRps->ConstrucaoCivil->Art)),
			array('begin' => '{@ifRegimeEspecialTributacao}', 'end' => '{@endifRegimeEspecialTributacao}', 'bool' => !is_null($oRps->RegimeEspecialTributacao) ),
			array('begin' => '{@ifEvento}', 'end' => '{@endifEvento}', 'bool' => !is_null($oRps->Evento->DescricaoEvento) || !is_null($oRps->Evento->IdentificacaoEvento) ),
			array('begin' => '{@ifIdentificacaoEvento}', 'end' => '{@endifIdentificacaoEvento}', 'bool' => !is_null($oRps->Evento->IdentificacaoEvento) ),
			array('begin' => '{@ifDescricaoEvento}', 'end' => '{@endifDescricaoEvento}', 'bool' => !is_null($oRps->Evento->DescricaoEvento) ),
			array('begin' => '{@ifInformacoesComplementares}', 'end' => '{@endifInformacoesComplementares}', 'bool' => !is_null($oRps->InformacoesComplementares) ),
			
			array('begin' => '{@ifIdInfDeclaracaoPrestacaoServico}', 'end' => '{@endifIdInfDeclaracaoPrestacaoServico}', 'bool' =>  !is_null($oRps->idInfDeclaracaoPrestacaoServico)  ),
			array('begin' => '{@ifIdRps}', 'end' => '{@endifIdRps}', 'bool' => !is_null($oRps->idRps) ),
		);

		return $this->retXML(PQDUtil::procTplText($tplRps, $aReplace, $aIfs));
	}

	/**
	 * Retorna o XML da requisição SOAP de acordo com o template 'soap'
	 * 
	 * @return string
	 */
	private function retXMLSoap($xml, $action) {

		$tpl = $this->getTemplate('soap');

		$aReplaces = $this->retReplaceUsuarios('soap');

		$aReplaces['replace']['{@xml}'] = $xml;
		$aReplaces['replace']['{@action}'] = $action;

		return $this->retXML(PQDUtil::procTplText($tpl, $aReplaces['replace'], $aReplaces['ifs']), false);
	}
	
	private function procReturn($return, $metodo){

		if(is_null($this->oReturn))
			$this->oReturn = new NFSeGenericoReturn($this);

		return $this->oReturn->getReturn($return, $metodo);
	}
	
	public function getIsHomologacao(){
		return $this->isHomologacao;
	}

	public function getConfig($key = null, $default = null){

		if(!is_null($key))
			return PQDUtil::retDefault($this->aConfig, $key, $default);

		return $this->aConfig;
	}
}

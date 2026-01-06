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

		parent::__construct($aConfig['privKey'] ?? null, $aConfig['pubKey'] ?? null, $aConfig['certKey'] ?? null);
		
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
					//'replaceXmlSOAP' => ['action2' => 'GerarNfse'],//Para replaces no do XML SOAP, quando a prefeitura tem mais de um action
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
					//'replaceXmlSOAP' => ['action2' => 'GerarNfse'],
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
					//'replaceXmlSOAP' => ['action2' => 'GerarNfse'],
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
	 * @param NFSeGenericoInfRps | NFSeGenericoInfDPS $oRps
	 * @param string $id
	 * 
	*/
	public function gerarNfse(NFSeGenericoInfRps | NFSeGenericoInfDPS $oRps) {
		
		$metodo = 'gerarNfse';
		
		// Determinar o nome do arquivo baseado no tipo de entrada
		if($oRps instanceof NFSeGenericoInfRps){
			//RPS
			$fileName = $oRps->IdentificacaoRps->Numero . "-" . $oRps->IdentificacaoRps->Serie . ".xml";
			$xml = $this->retXMLRps($oRps);
		}
		else{
			//DPS
			$fileName = $oRps->nDPS . "-" . $oRps->serie . ".xml";
			$xml = $this->retXMLDPS($oRps);
		}
		
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
		if($oRps instanceof NFSeGenericoInfRps)
			$aReplaces['replace']['{@Rps}'] = $xml;
		else
			$aReplaces['replace']['{@Dps}'] = $xml;

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
		
		$aReplaces = $this->aConfig['metodos'][$metodo]['replaceXmlSOAP'] ?? [];
		
		$xml = $this->retXMLSoap($xml, $action, $aReplaces);

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
	 * Gera uma NFS-e a partir de um DPS (Declaração de Prestação de Serviços)
	 * usando o padrão nacional
	 * 
	 * @param NFSeGenericoInfDPS $oDps - Objeto contendo os dados do DPS
	 * @return array
	 */
	public function gerarNfseEnvio(NFSeGenericoInfDPS $oDps){
		return $this->gerarNfse($oDps);
	}

	/**
	 * Retorna o XML do DPS de acordo com o template 'dps'
	 * 
	 * @param NFSeGenericoInfDPS $oDPS
	 * 
	 * @return string
	 */
	private function retXMLDPS(NFSeGenericoInfDPS $oDPS){

		$oDPS = $this->escapeTextObj($oDPS);

		$tplDPS = $this->getTemplate('dps');

		// Mapeamento dos campos do DPS
		$aReplace = array(
			'{@IdDPS}' => $oDPS->serie . $oDPS->nDPS,
			'{@tpAmb}' => $oDPS->tpAmb,
			'{@dhEmi}' => $oDPS->dhEmi,
			'{@verAplic}' => $oDPS->verAplic,
			'{@serie}' => $oDPS->serie,
			'{@nDPS}' => $oDPS->nDPS,
			'{@dCompet}' => $oDPS->dCompet,
			'{@tpEmit}' => $oDPS->tpEmit,
			'{@cMotivoEmisTI}' => $oDPS->cMotivoEmisTI,
			'{@chNFSeRej}' => $oDPS->chNFSeRej,
			'{@cLocEmi}' => $oDPS->cLocEmi,
			
			// Substituição
			'{@chSubstda}' => $oDPS->subst->chSubstda,
			'{@cMotivo}' => $oDPS->subst->cMotivo,
			'{@xMotivo}' => $oDPS->subst->xMotivo,
			
			// Prestador
			'{@CNPJPrestador}' => $oDPS->prest->CNPJ ?? null,
			'{@CPFPrestador}' => $oDPS->prest->CPF ?? null,
			// '{@NIFPrestador}' => $oDPS->prest->NIF ?? null, // Não utilizado no padrão da Nota Control
			// '{@cNaoNIFPrestador}' => $oDPS->prest->cNaoNIF ?? null, // Não utilizado no padrão da Nota Control
			'{@CAEPFPrestador}' => $oDPS->prest->CAEPF ?? null,
			'{@IMPrestador}' => $oDPS->prest->IM,
			'{@opSimpNac}' => $oDPS->prest->regTrib->opSimpNac,
			'{@regApTribSN}' => $oDPS->prest->regTrib->regApTribSN,
			'{@regEspTrib}' => $oDPS->prest->regTrib->regEspTrib,
			
			// Tomador
			'{@CNPJTomador}' => $oDPS->toma->CNPJ ?? null,
			'{@CPFTomador}' => $oDPS->toma->CPF ?? null,
			'{@NIFTomador}' => $oDPS->toma->NIF ?? null,
			'{@cNaoNIFTomador}' => $oDPS->toma->cNaoNIF ?? null,
			'{@CAEPFTomador}' => $oDPS->toma->CAEPF ?? null,
			'{@IMTomador}' => $oDPS->toma->IM ?? null,
			'{@xNomeTomador}' => $oDPS->toma->xNome,
			'{@cMunTomador}' => $oDPS->toma->end->endNacEndExt->cMun ?? null,
			'{@CEPTomador}' => $oDPS->toma->end->endNacEndExt->CEP ?? null,
			'{@cPaisTomador}' => $oDPS->toma->end->endNacEndExt->cPais ?? null,
			'{@cEndPostTomador}' => $oDPS->toma->end->endNacEndExt->cEndPost ?? null,
			'{@xCidadeTomador}' => $oDPS->toma->end->endNacEndExt->xCidade ?? null,
			'{@xEstProvRegTomador}' => $oDPS->toma->end->endNacEndExt->xEstProvReg ?? null,
			'{@xLgrTomador}' => $oDPS->toma->end->xLgr,
			'{@nroTomador}' => $oDPS->toma->end->nro,
			'{@xCplTomador}' => $oDPS->toma->end->xCpl,
			'{@xBairroTomador}' => $oDPS->toma->end->xBairro,
			'{@foneTomador}' => $oDPS->toma->fone,
			'{@emailTomador}' => $oDPS->toma->email,
			
			// Intermediário
			'{@CNPJInterm}' => $oDPS->interm->CNPJ ?? null,
			'{@CPFInterm}' => $oDPS->interm->CPF ?? null,
			'{@NIFInterm}' => $oDPS->interm->NIF ?? null,
			'{@cNaoNIFInterm}' => $oDPS->interm->cNaoNIF ?? null,
			'{@IMInterm}' => $oDPS->interm->IM,
			'{@xNomeInterm}' => $oDPS->interm->xNome,			
			'{@cMunInterm}' => $oDPS->interm->end->endNacEndExt->cMun ?? null,
			'{@CEPInterm}' => $oDPS->interm->end->endNacEndExt->CEP ?? null,
			'{@cPaisInterm}' => $oDPS->interm->end->endNacEndExt->cPais ?? null,
			'{@cEndPostInterm}' => $oDPS->interm->end->endNacEndExt->cEndPost ?? null,
			'{@xCidadeInterm}' => $oDPS->interm->end->endNacEndExt->xCidade ?? null,
			'{@xEstProvRegInterm}' => $oDPS->interm->end->endNacEndExt->xEstProvReg ?? null,
			'{@xLgrInterm}' => $oDPS->interm->end->xLgr,
			'{@nroInterm}' => $oDPS->interm->end->nro,
			'{@xCplInterm}' => $oDPS->interm->end->xCpl ?? null,
			'{@xBairroInterm}' => $oDPS->interm->end->xBairro,
			'{@foneInterm}' => $oDPS->interm->fone,
			'{@emailInterm}' => $oDPS->interm->email,
			
			// Serviço - Local de Prestação
			'{@cLocPrestacao}' => $oDPS->serv->locPrest->cLocPrestacao ?? null,
			'{@cPaisPrestacao}' => $oDPS->serv->locPrest->cPaisPrestacao ?? null,
			
			// Serviço - Código do Serviço
			'{@cTribNac}' => $oDPS->serv->cServ->cTribNac,
			'{@cTribMun}' => $oDPS->serv->cServ->cTribMun,
			'{@xDescServ}' => $oDPS->serv->cServ->xDescServ,
			'{@cNBS}' => $oDPS->serv->cServ->cNBS,
			'{@cIntContrib}' => $oDPS->serv->cServ->cIntContrib,
			
			// Serviço - Comércio Exterior
			'{@mdPrestacao}' => $oDPS->serv->comExt->mdPrestacao ?? null,
			'{@vincPrest}' => $oDPS->serv->comExt->vincPrest ?? null,
			'{@tpMoeda}' => $oDPS->serv->comExt->tpMoeda ?? null,
			'{@vServMoeda}' => $oDPS->serv->comExt->vServMoeda ?? null,
			'{@mecAFComexP}' => $oDPS->serv->comExt->mecAFComexP ?? null,
			'{@mecAFComexT}' => $oDPS->serv->comExt->mecAFComexT ?? null,
			'{@movTempBens}' => $oDPS->serv->comExt->movTempBens ?? null,
			'{@nDI}' => $oDPS->serv->comExt->nDI ?? null,
			'{@nRE}' => $oDPS->serv->comExt->nRE ?? null,
			'{@mdic}' => $oDPS->serv->comExt->mdic ?? null,
			
			// Serviço - Obra
			'{@inscImobFiscObra}' => $oDPS->serv->obra->inscImobFisc ?? null,
			'{@cObra}' => $oDPS->serv->obra->cObra ?? null,
			'{@cCIBObra}' => $oDPS->serv->obra->cCIB ?? null,
			'{@CEPObra}' => $oDPS->serv->obra->end->CEP ?? null,
			'{@cEndPostObra}' => $oDPS->serv->obra->end->endNacEndExt->cEndPost ?? null,
			'{@xCidadeObra}' => $oDPS->serv->obra->end->endNacEndExt->xCidade ?? null,
			'{@xEstProvRegObra}' => $oDPS->serv->obra->end->endNacEndExt->xEstProvReg ?? null,
			'{@xLgrObra}' => $oDPS->serv->obra->end->xLgr ?? null,
			'{@nroObra}' => $oDPS->serv->obra->end->nro ?? null,
			'{@xCplObra}' => $oDPS->serv->obra->end->xCpl ?? null,
			'{@xBairroObra}' => $oDPS->serv->obra->end->xBairro ?? null,
			
			// Serviço - Atividade/Evento
			'{@xNomeAtvEvento}' => $oDPS->serv->atvEvento->xNome ?? null,
			'{@dtIniAtvEvento}' => $oDPS->serv->atvEvento->dtIni ?? null,
			'{@dtFimAtvEvento}' => $oDPS->serv->atvEvento->dtFim ?? null,
			'{@idAtvEv}' => $oDPS->serv->atvEvento->idAtvEv ?? null,
			'{@CEPAtvEvento}' => $oDPS->serv->atvEvento->end->CEP ?? null,
			'{@cEndPostAtvEvento}' => $oDPS->serv->atvEvento->end->endNacEndExt->cEndPost ?? null,
			'{@xCidadeAtvEvento}' => $oDPS->serv->atvEvento->end->endNacEndExt->xCidade ?? null,
			'{@xEstProvRegAtvEvento}' => $oDPS->serv->atvEvento->end->endNacEndExt->xEstProvReg ?? null,
			'{@xLgrAtvEvento}' => $oDPS->serv->atvEvento->end->xLgr ?? null,
			'{@nroAtvEvento}' => $oDPS->serv->atvEvento->end->nro ?? null,
			'{@xCplAtvEvento}' => $oDPS->serv->atvEvento->end->xCpl ?? null,
			'{@xBairroAtvEvento}' => $oDPS->serv->atvEvento->end->xBairro ?? null,
			
			// Serviço - Informações Complementares
			'{@idDocTec}' => $oDPS->serv->infoCompl->idDocTec,
			'{@docRef}' => $oDPS->serv->infoCompl->docRef,
			'{@xPed}' => $oDPS->serv->infoCompl->xPed,
			'{@xItemPed}' => $oDPS->serv->infoCompl->gItemPed->xItemPed ?? null,
			'{@xInfComp}' => $oDPS->serv->infoCompl->xInfComp,
			
			// Valores - Serviço Prestado
			'{@vReceb}' => $oDPS->valores->vServPrest->vReceb,
			'{@vServ}' => $oDPS->valores->vServPrest->vServ,
			
			// Valores - Descontos
			'{@vDescIncond}' => $oDPS->valores->vDescCondIncond->vDescIncond,
			'{@vDescCond}' => $oDPS->valores->vDescCondIncond->vDescCond,
			
			// Valores - Deduções/Reduções
			'{@pDR}' => $oDPS->valores->vDedRed->pDR ?? null,
			'{@vDR}' => $oDPS->valores->vDedRed->vDR ?? null,
			
			// Tributação Municipal - ISSQN
			'{@tribISSQN}' => $oDPS->valores->trib->tribMun->tribISSQN ?? null,
			'{@cPaisResult}' => $oDPS->valores->trib->tribMun->cPaisResult ?? null,
			'{@tpImunidade}' => $oDPS->valores->trib->tribMun->tpImunidade ?? null,
			'{@tpSusp}' => $oDPS->valores->trib->tribMun->exigSusp->tpSusp ?? null,
			'{@nProcesso}' => $oDPS->valores->trib->tribMun->exigSusp->nProcesso ?? null,
			'{@nBM}' => $oDPS->valores->trib->tribMun->BM->nBM ?? null,
			'{@vRedBCBM}' => $oDPS->valores->trib->tribMun->BM->vRedBCBM ?? null,
			'{@pRedBCBM}' => $oDPS->valores->trib->tribMun->BM->pRedBCBM ?? null,
			'{@tpRetISSQN}' => $oDPS->valores->trib->tribMun->tpRetISSQN ?? null,
			'{@vBCISSQN}' => $oDPS->valores->trib->tribMun->vBCISSQN ?? null,
			'{@pAliqISSQN}' => $oDPS->valores->trib->tribMun->pAliqISSQN ?? null,
			'{@vISSQN}' => $oDPS->valores->trib->tribMun->vISSQN ?? null,
			
			// Tributação Federal - PIS
			'{@polTribPIS}' => $oDPS->valores->trib->tribFed->PIS->polTrib ?? null,
			'{@vBCPIS}' => $oDPS->valores->trib->tribFed->PIS->vBC ?? null,
			'{@pAliqPIS}' => $oDPS->valores->trib->tribFed->PIS->pAliq ?? null,
			'{@vTribPIS}' => $oDPS->valores->trib->tribFed->PIS->vTrib ?? null,
			
			// Tributação Federal - COFINS
			'{@polTribCOFINS}' => $oDPS->valores->trib->tribFed->COFINS->polTrib ?? null,
			'{@vBCCOFINS}' => $oDPS->valores->trib->tribFed->COFINS->vBC ?? null,
			'{@pAliqCOFINS}' => $oDPS->valores->trib->tribFed->COFINS->pAliq ?? null,
			'{@vTribCOFINS}' => $oDPS->valores->trib->tribFed->COFINS->vTrib ?? null,
			
			// Tributação Federal - CSLL
			'{@polTribCSLL}' => $oDPS->valores->trib->tribFed->CSLL->polTrib ?? null,
			'{@vBCCSLL}' => $oDPS->valores->trib->tribFed->CSLL->vBC ?? null,
			'{@pAliqCSLL}' => $oDPS->valores->trib->tribFed->CSLL->pAliq ?? null,
			'{@vTribCSLL}' => $oDPS->valores->trib->tribFed->CSLL->vTrib ?? null,
			
			// Tributação Federal - IRRF
			'{@polTribIRRF}' => $oDPS->valores->trib->tribFed->IRRF->polTrib ?? null,
			'{@vBCIRRF}' => $oDPS->valores->trib->tribFed->IRRF->vBC ?? null,
			'{@pAliqIRRF}' => $oDPS->valores->trib->tribFed->IRRF->pAliq ?? null,
			'{@vTribIRRF}' => $oDPS->valores->trib->tribFed->IRRF->vTrib ?? null,
			
			// Tributação Federal - INSS
			'{@polTribINSS}' => $oDPS->valores->trib->tribFed->INSS->polTrib ?? null,
			'{@vBCINSS}' => $oDPS->valores->trib->tribFed->INSS->vBC ?? null,
			'{@pAliqINSS}' => $oDPS->valores->trib->tribFed->INSS->pAliq ?? null,
			'{@vTribINSS}' => $oDPS->valores->trib->tribFed->INSS->vTrib ?? null,
			
			// Total de Tributos
			'{@vTotTribFed}' => $oDPS->valores->trib->totTrib->vTotTribFed ?? null,
			'{@vTotTribEst}' => $oDPS->valores->trib->totTrib->vTotTribEst ?? null,
			'{@vTotTribMun}' => $oDPS->valores->trib->totTrib->vTotTribMun ?? null,
			'{@pTotTribFed}' => $oDPS->valores->trib->totTrib->pTotTribFed ?? null,
			'{@pTotTribEst}' => $oDPS->valores->trib->totTrib->pTotTribEst ?? null,
			'{@pTotTribMun}' => $oDPS->valores->trib->totTrib->pTotTribMun ?? null,
			
			// IBS/CBS
			'{@finNFSe}' => $oDPS->IBSCBS->finNFSe ?? null,
			'{@indFinal}' => $oDPS->IBSCBS->indFinal ?? null,
			'{@cIndOp}' => $oDPS->IBSCBS->cIndOp ?? null,
			'{@tpOper}' => $oDPS->IBSCBS->tpOper ?? null,
			'{@tpEnteGov}' => $oDPS->IBSCBS->tpEnteGov ?? null,
			'{@indDest}' => $oDPS->IBSCBS->indDest ?? null,
			
			// IBS/CBS - Destinatário
			'{@CNPJDest}' => $oDPS->IBSCBS->dest->CnpjCpfNifCNaoNif ?? null,
			'{@CPFDest}' => $oDPS->IBSCBS->dest->CnpjCpfNifCNaoNif ?? null,
			'{@NIFDest}' => $oDPS->IBSCBS->dest->CnpjCpfNifCNaoNif ?? null,
			'{@cNaoNIFDest}' => $oDPS->IBSCBS->dest->CnpjCpfNifCNaoNif ?? null,
			'{@xNomeDest}' => $oDPS->IBSCBS->dest->xNome ?? null,
			'{@cMunDest}' => $oDPS->IBSCBS->dest->end->endNacEndExt->cMun ?? null,
			'{@CEPDest}' => $oDPS->IBSCBS->dest->end->endNacEndExt->CEP ?? null,
			'{@cPaisDest}' => $oDPS->IBSCBS->dest->end->endNacEndExt->cPais ?? null,
			'{@cEndPostDest}' => $oDPS->IBSCBS->dest->end->endNacEndExt->cEndPost ?? null,
			'{@xCidadeDest}' => $oDPS->IBSCBS->dest->end->endNacEndExt->xCidade ?? null,
			'{@xEstProvRegDest}' => $oDPS->IBSCBS->dest->end->endNacEndExt->xEstProvReg ?? null,
			'{@xLgrDest}' => $oDPS->IBSCBS->dest->end->xLgr ?? null,
			'{@nroDest}' => $oDPS->IBSCBS->dest->end->nro ?? null,
			'{@xCplDest}' => $oDPS->IBSCBS->dest->end->xCpl ?? null,
			'{@xBairroDest}' => $oDPS->IBSCBS->dest->end->xBairro ?? null,
			'{@foneDest}' => $oDPS->IBSCBS->dest->fone ?? null,
			'{@emailDest}' => $oDPS->IBSCBS->dest->email ?? null,
			
			// IBS/CBS - Imóvel
			'{@inscImobFiscImovel}' => $oDPS->IBSCBS->imovel->inscImobFisc ?? null,
			'{@cCIBImovel}' => $oDPS->IBSCBS->imovel->cCIB ?? null,
			'{@CEPImovel}' => $oDPS->IBSCBS->imovel->end->CEP ?? null,
			'{@cEndPostImovel}' => $oDPS->IBSCBS->imovel->end->endNacEndExt->cEndPost ?? null,
			'{@xCidadeImovel}' => $oDPS->IBSCBS->imovel->end->endNacEndExt->xCidade ?? null,
			'{@xEstProvRegImovel}' => $oDPS->IBSCBS->imovel->end->endNacEndExt->xEstProvReg ?? null,
			'{@xLgrImovel}' => $oDPS->IBSCBS->imovel->end->xLgr ?? null,
			'{@nroImovel}' => $oDPS->IBSCBS->imovel->end->nro ?? null,
			'{@xCplImovel}' => $oDPS->IBSCBS->imovel->end->xCpl ?? null,
			'{@xBairroImovel}' => $oDPS->IBSCBS->imovel->end->xBairro ?? null,
		);

		// Processar arrays de documentos de dedução/redução se existirem
		if(!empty($oDPS->valores->vDedRed->aDocDedRed) && is_array($oDPS->valores->vDedRed->aDocDedRed)){
			$docDedRedXml = '';
			foreach($oDPS->valores->vDedRed->aDocDedRed as $doc){
				$docDedRedXml .= '<tc:docDedRed>';
				if(!empty($doc->chNFSe)) $docDedRedXml .= '<tc:chNFSe>' . $doc->chNFSe . '</tc:chNFSe>';
				if(!empty($doc->chNFe)) $docDedRedXml .= '<tc:chNFe>' . $doc->chNFe . '</tc:chNFe>';
				if(!empty($doc->tpDedRed)) $docDedRedXml .= '<tc:tpDedRed>' . $doc->tpDedRed . '</tc:tpDedRed>';
				if(!empty($doc->xDescOutDed)) $docDedRedXml .= '<tc:xDescOutDed>' . $doc->xDescOutDed . '</tc:xDescOutDed>';
				if(!empty($doc->dtEmiDoc)) $docDedRedXml .= '<tc:dtEmiDoc>' . $doc->dtEmiDoc . '</tc:dtEmiDoc>';
				if(!empty($doc->vDedutivelRedutivel)) $docDedRedXml .= '<tc:vDedutivelRedutivel>' . $doc->vDedutivelRedutivel . '</tc:vDedutivelRedutivel>';
				if(!empty($doc->vDeducaoReducao)) $docDedRedXml .= '<tc:vDeducaoReducao>' . $doc->vDeducaoReducao . '</tc:vDeducaoReducao>';
				$docDedRedXml .= '</tc:docDedRed>';
			}
			$aReplace['{@foreachDocDedRed}'] = $docDedRedXml;
		} else {
			$aReplace['{@foreachDocDedRed}'] = '';
		}
		
		// Processar arrays de referências NFS-e se existirem
		if(!empty($oDPS->IBSCBS->gRefNFSe) && is_array($oDPS->IBSCBS->gRefNFSe)){
			$gRefNFSeXml = '<tc:gRefNFSe>';
			foreach($oDPS->IBSCBS->gRefNFSe as $ref){
				$gRefNFSeXml .= '<tc:refNFSe>' . $ref->refNFSe . '</tc:refNFSe>';
			}
			$gRefNFSeXml .= '</tc:gRefNFSe>';
			$aReplace['{@foreachGRefNFSe}'] = $gRefNFSeXml;
		} else {
			$aReplace['{@foreachGRefNFSe}'] = '';
		}
		
		// Processar arrays de reembolsos/repasses/ressarcimentos se existirem
		if(!empty($oDPS->IBSCBS->valores->gReeRepRes->documentos) && is_array($oDPS->IBSCBS->valores->gReeRepRes->documentos)){
			$gReeRepResXml = '';
			foreach($oDPS->IBSCBS->valores->gReeRepRes->documentos as $ree){
				$gReeRepResXml .= '<tc:gReeRepRes>';
				if(!empty($ree->chNFSe)) $gReeRepResXml .= '<tc:chNFSe>' . $ree->chNFSe . '</tc:chNFSe>';
				if(!empty($ree->tpReeRepRes)) $gReeRepResXml .= '<tc:tpReeRepRes>' . $ree->tpReeRepRes . '</tc:tpReeRepRes>';
				if(!empty($ree->dtEmiDoc)) $gReeRepResXml .= '<tc:dtEmiDoc>' . $ree->dtEmiDoc . '</tc:dtEmiDoc>';
				if(!empty($ree->vReeRepRes)) $gReeRepResXml .= '<tc:vReeRepRes>' . $ree->vReeRepRes . '</tc:vReeRepRes>';
				$gReeRepResXml .= '</tc:gReeRepRes>';
			}
			$aReplace['{@foreachGReeRepRes}'] = $gReeRepResXml;
		} else {
			$aReplace['{@foreachGReeRepRes}'] = '';
		}

		// Apply field functions
		foreach($aReplace as $k => $v){
			$field = str_replace(['{@', '}'], '', $k);
			$field = strtolower(substr($field, 0, 1)) . substr($field, 1);
			$aReplace[$k] = $this->applyFnField($field, $v);
		}

		// Conditional statements
		$aIfs = array(
			['begin' => '{@ifVerAplic}', 'end' => '{@endifVerAplic}', 'bool' => !empty($oDPS->verAplic)],
			['begin' => '{@ifSubst}', 'end' => '{@endifSubst}', 'bool' => !empty($oDPS->subst->chSubstda)],
			
			['begin' => '{@ifCNPJPrestador}', 'end' => '{@endifCNPJPrestador}', 'bool' => strlen($oDPS->prest->CnpjCpfNifCNaoNif ?? '') == 14],
			['begin' => '{@ifCPFPrestador}', 'end' => '{@endifCPFPrestador}', 'bool' => strlen($oDPS->prest->CnpjCpfNifCNaoNif ?? '') == 11],
			['begin' => '{@ifCAEPFPrestador}', 'end' => '{@endifCAEPFPrestador}', 'bool' => !empty($oDPS->prest->CAEPF)],
			['begin' => '{@ifRegApTribSN}', 'end' => '{@endifRegApTribSN}', 'bool' => !empty($oDPS->prest->regTrib->regApTribSN)],
			
			['begin' => '{@ifToma}', 'end' => '{@endifToma}', 'bool' => !empty($oDPS->toma->CnpjCpfNifCNaoNif) || !empty($oDPS->toma->xNome)],
			['begin' => '{@ifCNPJTomador}', 'end' => '{@endifCNPJTomador}', 'bool' => strlen($oDPS->toma->CnpjCpfNifCNaoNif ?? '') == 14],
			['begin' => '{@ifCPFTomador}', 'end' => '{@endifCPFTomador}', 'bool' => strlen($oDPS->toma->CnpjCpfNifCNaoNif ?? '') == 11],
			['begin' => '{@ifNIFTomador}', 'end' => '{@endifNIFTomador}', 'bool' => !empty($oDPS->toma->NIF)],
			['begin' => '{@ifCNaoNIFTomador}', 'end' => '{@endifCNaoNIFTomador}', 'bool' => !empty($oDPS->toma->cNaoNIF)],
			['begin' => '{@ifCAEPFTomador}', 'end' => '{@endifCAEPFTomador}', 'bool' => !empty($oDPS->toma->CAEPF)],
			['begin' => '{@ifIMTomador}', 'end' => '{@endifIMTomador}', 'bool' => !empty($oDPS->toma->IM)],
			['begin' => '{@ifEndTomador}', 'end' => '{@endifEndTomador}', 'bool' => 
				( !is_null($oDPS->toma->end->endNacEndExt->cMun) && !is_null($oDPS->toma->end->endNacEndExt->CEP) ) ||
				( !is_null($oDPS->toma->end->endNacEndExt->cPais) && !is_null($oDPS->toma->end->endNacEndExt->cEndPost) && !is_null($oDPS->toma->end->endNacEndExt->xCidade) && !is_null($oDPS->toma->end->endNacEndExt->xEstProvReg) )
			],
			['begin' => '{@ifEndNacTomador}', 'end' => '{@endifEndNacTomador}', 'bool' => 
				( !is_null($oDPS->toma->end->endNacEndExt->cMun) && !is_null($oDPS->toma->end->endNacEndExt->CEP) ) &&
				( is_null($oDPS->toma->end->endNacEndExt->cPais) && is_null($oDPS->toma->end->endNacEndExt->cEndPost) && is_null($oDPS->toma->end->endNacEndExt->xCidade) && is_null($oDPS->toma->end->endNacEndExt->xEstProvReg) )
			],
			['begin' => '{@ifEndExtTomador}', 'end' => '{@endifEndExtTomador}', 'bool' =>
				( is_null($oDPS->toma->end->endNacEndExt->cMun) && is_null($oDPS->toma->end->endNacEndExt->CEP) ) && 
				( !is_null($oDPS->toma->end->endNacEndExt->cPais) && !is_null($oDPS->toma->end->endNacEndExt->cEndPost) && !is_null($oDPS->toma->end->endNacEndExt->xCidade) && !is_null($oDPS->toma->end->endNacEndExt->xEstProvReg) )
			],
			['begin' => '{@ifXCplTomador}', 'end' => '{@endifXCplTomador}', 'bool' => !empty($oDPS->toma->end->xCpl)],
			['begin' => '{@ifFoneTomador}', 'end' => '{@endifFoneTomador}', 'bool' => !empty($oDPS->toma->fone)],
			['begin' => '{@ifEmailTomador}', 'end' => '{@endifEmailTomador}', 'bool' => !empty($oDPS->toma->email)],
			
			// Intermediário conditionals
			['begin' => '{@ifInterm}', 'end' => '{@endifInterm}', 'bool' => !empty($oDPS->interm->CnpjCpfNifCNaoNif) || !empty($oDPS->interm->xNome)],
			['begin' => '{@ifCNPJInterm}', 'end' => '{@endifCNPJInterm}', 'bool' => strlen($oDPS->interm->CnpjCpfNifCNaoNif ?? '') == 14],
			['begin' => '{@ifCPFInterm}', 'end' => '{@endifCPFInterm}', 'bool' => strlen($oDPS->interm->CnpjCpfNifCNaoNif ?? '') == 11],
			['begin' => '{@ifNIFInterm}', 'end' => '{@endifNIFInterm}', 'bool' => !empty($oDPS->interm->NIF)],
			['begin' => '{@ifCNaoNIFInterm}', 'end' => '{@endifCNaoNIFInterm}', 'bool' => !empty($oDPS->interm->cNaoNIF)],
			['begin' => '{@ifCAEPFInterm}', 'end' => '{@endifCAEPFInterm}', 'bool' => !empty($oDPS->interm->CAEPF)],
			['begin' => '{@ifIMInterm}', 'end' => '{@endifIMInterm}', 'bool' => !empty($oDPS->interm->IM)],
			['begin' => '{@ifEndInterm}', 'end' => '{@endifEndInterm}', 'bool' => !empty($oDPS->interm->end->xLgr)],
			['begin' => '{@ifEndNacInterm}', 'end' => '{@endifEndNacInterm}', 'bool' => !empty($oDPS->interm->end->endNacEndExt->cMun)],
			['begin' => '{@ifEndExtInterm}', 'end' => '{@endifEndExtInterm}', 'bool' => !empty($oDPS->interm->end->endNacEndExt->cPais)],
			['begin' => '{@ifXCplInterm}', 'end' => '{@endifXCplInterm}', 'bool' => !empty($oDPS->interm->end->xCpl)],
			['begin' => '{@ifFoneInterm}', 'end' => '{@endifFoneInterm}', 'bool' => !empty($oDPS->interm->fone)],
			['begin' => '{@ifEmailInterm}', 'end' => '{@endifEmailInterm}', 'bool' => !empty($oDPS->interm->email)],
			
			['begin' => '{@ifCLocPrestacao}', 'end' => '{@endifCLocPrestacao}', 'bool' => !empty($oDPS->serv->locPrest->cLocPrestacao)],
			['begin' => '{@ifCPaisPrestacao}', 'end' => '{@endifCPaisPrestacao}', 'bool' => !empty($oDPS->serv->locPrest->cPaisPrestacao)],
			['begin' => '{@ifCIntContrib}', 'end' => '{@endifCIntContrib}', 'bool' => !empty($oDPS->serv->cServ->cIntContrib)],
			['begin' => '{@ifComExt}', 'end' => '{@endifComExt}', 'bool' => !empty($oDPS->serv->comExt->mdPrestacao)],
			['begin' => '{@ifNDI}', 'end' => '{@endifNDI}', 'bool' => !empty($oDPS->serv->comExt->nDI)],
			['begin' => '{@ifNRE}', 'end' => '{@endifNRE}', 'bool' => !empty($oDPS->serv->comExt->nRE)],
			['begin' => '{@ifObra}', 'end' => '{@endifObra}', 'bool' => !empty($oDPS->serv->obra->cObra) || !empty($oDPS->serv->obra->cCIB) || !empty($oDPS->serv->obra->inscImobFisc)],
			['begin' => '{@ifInscImobFiscObra}', 'end' => '{@endifInscImobFiscObra}', 'bool' => !empty($oDPS->serv->obra->inscImobFisc)],
			['begin' => '{@ifCObra}', 'end' => '{@endifCObra}', 'bool' => !empty($oDPS->serv->obra->cObra)],
			['begin' => '{@ifCCIBObra}', 'end' => '{@endifCCIBObra}', 'bool' => !empty($oDPS->serv->obra->cCIB)],
			['begin' => '{@ifEndObra}', 'end' => '{@endifEndObra}', 'bool' => !empty($oDPS->serv->obra->end->xLgr)],
			['begin' => '{@ifCEPObra}', 'end' => '{@endifCEPObra}', 'bool' => !empty($oDPS->serv->obra->end->CEP)],
			['begin' => '{@ifEndExtObra}', 'end' => '{@endifEndExtObra}', 'bool' => !empty($oDPS->serv->obra->end->endNacEndExt->cEndPost)],
			['begin' => '{@ifXCplObra}', 'end' => '{@endifXCplObra}', 'bool' => !empty($oDPS->serv->obra->end->xCpl)],
			['begin' => '{@ifAtvEvento}', 'end' => '{@endifAtvEvento}', 'bool' => !empty($oDPS->serv->atvEvento->xNome)],
			['begin' => '{@ifIdAtvEv}', 'end' => '{@endifIdAtvEv}', 'bool' => !empty($oDPS->serv->atvEvento->idAtvEv)],
			['begin' => '{@ifEndAtvEvento}', 'end' => '{@endifEndAtvEvento}', 'bool' => !empty($oDPS->serv->atvEvento->end->xLgr)],
			['begin' => '{@ifCEPAtvEvento}', 'end' => '{@endifCEPAtvEvento}', 'bool' => !empty($oDPS->serv->atvEvento->end->CEP)],
			['begin' => '{@ifEndExtAtvEvento}', 'end' => '{@endifEndExtAtvEvento}', 'bool' => !empty($oDPS->serv->atvEvento->end->endNacEndExt->cEndPost)],
			['begin' => '{@ifXCplAtvEvento}', 'end' => '{@endifXCplAtvEvento}', 'bool' => !empty($oDPS->serv->atvEvento->end->xCpl)],
			['begin' => '{@ifInfoCompl}', 'end' => '{@endifInfoCompl}', 'bool' => !empty($oDPS->serv->infoCompl->xInfComp) || !empty($oDPS->serv->infoCompl->idDocTec) || !empty($oDPS->serv->infoCompl->docRef)],
			['begin' => '{@ifIdDocTec}', 'end' => '{@endifIdDocTec}', 'bool' => !empty($oDPS->serv->infoCompl->idDocTec)],
			['begin' => '{@ifDocRef}', 'end' => '{@endifDocRef}', 'bool' => !empty($oDPS->serv->infoCompl->docRef)],
			['begin' => '{@ifXPed}', 'end' => '{@endifXPed}', 'bool' => !empty($oDPS->serv->infoCompl->xPed)],
			['begin' => '{@ifGItemPed}', 'end' => '{@endifGItemPed}', 'bool' => !empty($oDPS->serv->infoCompl->gItemPed->xItemPed)],
			['begin' => '{@ifXInfComp}', 'end' => '{@endifXInfComp}', 'bool' => !empty($oDPS->serv->infoCompl->xInfComp)],
			
			['begin' => '{@ifVReceb}', 'end' => '{@endifVReceb}', 'bool' => !empty($oDPS->valores->vServPrest->vReceb)],
			['begin' => '{@ifVDescCondIncond}', 'end' => '{@endifVDescCondIncond}', 'bool' => !empty($oDPS->valores->vDescCondIncond->vDescIncond) || !empty($oDPS->valores->vDescCondIncond->vDescCond)],
			['begin' => '{@ifVDescIncond}', 'end' => '{@endifVDescIncond}', 'bool' => !empty($oDPS->valores->vDescCondIncond->vDescIncond)],
			['begin' => '{@ifVDescCond}', 'end' => '{@endifVDescCond}', 'bool' => !empty($oDPS->valores->vDescCondIncond->vDescCond)],
			['begin' => '{@ifVDedRed}', 'end' => '{@endifVDedRed}', 'bool' => !empty($oDPS->valores->vDedRed->pDR) || !empty($oDPS->valores->vDedRed->vDR) || !empty($oDPS->valores->vDedRed->aDocDedRed)],
			['begin' => '{@ifPDR}', 'end' => '{@endifPDR}', 'bool' => !empty($oDPS->valores->vDedRed->pDR)],
			['begin' => '{@ifVDR}', 'end' => '{@endifVDR}', 'bool' => !empty($oDPS->valores->vDedRed->vDR)],
			['begin' => '{@ifDocumentos}', 'end' => '{@endifDocumentos}', 'bool' => !empty($oDPS->valores->vDedRed->documentos)],
			
			// Tributação Municipal
			['begin' => '{@ifCPaisResult}', 'end' => '{@endifCPaisResult}', 'bool' => !empty($oDPS->valores->trib->tribMun->cPaisResult)],
			['begin' => '{@ifTpImunidade}', 'end' => '{@endifTpImunidade}', 'bool' => !empty($oDPS->valores->trib->tribMun->tpImunidade)],
			['begin' => '{@ifExigSusp}', 'end' => '{@endifExigSusp}', 'bool' => !empty($oDPS->valores->trib->tribMun->exigSusp->tpSusp)],
			['begin' => '{@ifBM}', 'end' => '{@endifBM}', 'bool' => !empty($oDPS->valores->trib->tribMun->BM->nBM)],
			['begin' => '{@ifVRedBCBM}', 'end' => '{@endifVRedBCBM}', 'bool' => !empty($oDPS->valores->trib->tribMun->BM->vRedBCBM)],
			['begin' => '{@ifPRedBCBM}', 'end' => '{@endifPRedBCBM}', 'bool' => !empty($oDPS->valores->trib->tribMun->BM->pRedBCBM)],
			['begin' => '{@ifTpRetISSQN}', 'end' => '{@endifTpRetISSQN}', 'bool' => !empty($oDPS->valores->trib->tribMun->tpRetISSQN)],
			['begin' => '{@ifVBCISSQN}', 'end' => '{@endifVBCISSQN}', 'bool' => !empty($oDPS->valores->trib->tribMun->vBCISSQN)],
			['begin' => '{@ifPAliqISSQN}', 'end' => '{@endifPAliqISSQN}', 'bool' => !empty($oDPS->valores->trib->tribMun->pAliqISSQN)],
			['begin' => '{@ifVISSQN}', 'end' => '{@endifVISSQN}', 'bool' => !empty($oDPS->valores->trib->tribMun->vISSQN)],
			
			// Tributação Federal
			['begin' => '{@ifTribFed}', 'end' => '{@endifTribFed}', 'bool' => !empty($oDPS->valores->trib->tribFed)],
			['begin' => '{@ifTribPIS}', 'end' => '{@endifTribPIS}', 'bool' => !empty($oDPS->valores->trib->tribFed->PIS->polTrib)],
			['begin' => '{@ifVBCPIS}', 'end' => '{@endifVBCPIS}', 'bool' => !empty($oDPS->valores->trib->tribFed->PIS->vBC)],
			['begin' => '{@ifPAliqPIS}', 'end' => '{@endifPAliqPIS}', 'bool' => !empty($oDPS->valores->trib->tribFed->PIS->pAliq)],
			['begin' => '{@ifVTribPIS}', 'end' => '{@endifVTribPIS}', 'bool' => !empty($oDPS->valores->trib->tribFed->PIS->vTrib)],
			['begin' => '{@ifTribCOFINS}', 'end' => '{@endifTribCOFINS}', 'bool' => !empty($oDPS->valores->trib->tribFed->COFINS->polTrib)],
			['begin' => '{@ifVBCCOFINS}', 'end' => '{@endifVBCCOFINS}', 'bool' => !empty($oDPS->valores->trib->tribFed->COFINS->vBC)],
			['begin' => '{@ifPAliqCOFINS}', 'end' => '{@endifPAliqCOFINS}', 'bool' => !empty($oDPS->valores->trib->tribFed->COFINS->pAliq)],
			['begin' => '{@ifVTribCOFINS}', 'end' => '{@endifVTribCOFINS}', 'bool' => !empty($oDPS->valores->trib->tribFed->COFINS->vTrib)],
			['begin' => '{@ifTribCSLL}', 'end' => '{@endifTribCSLL}', 'bool' => !empty($oDPS->valores->trib->tribFed->CSLL->polTrib)],
			['begin' => '{@ifVBCCSLL}', 'end' => '{@endifVBCCSLL}', 'bool' => !empty($oDPS->valores->trib->tribFed->CSLL->vBC)],
			['begin' => '{@ifPAliqCSLL}', 'end' => '{@endifPAliqCSLL}', 'bool' => !empty($oDPS->valores->trib->tribFed->CSLL->pAliq)],
			['begin' => '{@ifVTribCSLL}', 'end' => '{@endifVTribCSLL}', 'bool' => !empty($oDPS->valores->trib->tribFed->CSLL->vTrib)],
			['begin' => '{@ifTribIRRF}', 'end' => '{@endifTribIRRF}', 'bool' => !empty($oDPS->valores->trib->tribFed->IRRF->polTrib)],
			['begin' => '{@ifVBCIRRF}', 'end' => '{@endifVBCIRRF}', 'bool' => !empty($oDPS->valores->trib->tribFed->IRRF->vBC)],
			['begin' => '{@ifPAliqIRRF}', 'end' => '{@endifPAliqIRRF}', 'bool' => !empty($oDPS->valores->trib->tribFed->IRRF->pAliq)],
			['begin' => '{@ifVTribIRRF}', 'end' => '{@endifVTribIRRF}', 'bool' => !empty($oDPS->valores->trib->tribFed->IRRF->vTrib)],
			['begin' => '{@ifTribINSS}', 'end' => '{@endifTribINSS}', 'bool' => !empty($oDPS->valores->trib->tribFed->INSS->polTrib)],
			['begin' => '{@ifVBCINSS}', 'end' => '{@endifVBCINSS}', 'bool' => !empty($oDPS->valores->trib->tribFed->INSS->vBC)],
			['begin' => '{@ifPAliqINSS}', 'end' => '{@endifPAliqINSS}', 'bool' => !empty($oDPS->valores->trib->tribFed->INSS->pAliq)],
			['begin' => '{@ifVTribINSS}', 'end' => '{@endifVTribINSS}', 'bool' => !empty($oDPS->valores->trib->tribFed->INSS->vTrib)],
			
			// Total de Tributos
			['begin' => '{@ifTotTrib}', 'end' => '{@endifTotTrib}', 'bool' => !empty($oDPS->valores->trib->totTrib)],
			['begin' => '{@ifVTotTribFed}', 'end' => '{@endifVTotTribFed}', 'bool' => !empty($oDPS->valores->trib->totTrib->vTotTribFed)],
			['begin' => '{@ifVTotTribEst}', 'end' => '{@endifVTotTribEst}', 'bool' => !empty($oDPS->valores->trib->totTrib->vTotTribEst)],
			['begin' => '{@ifVTotTribMun}', 'end' => '{@endifVTotTribMun}', 'bool' => !empty($oDPS->valores->trib->totTrib->vTotTribMun)],
			['begin' => '{@ifPTotTribFed}', 'end' => '{@endifPTotTribFed}', 'bool' => !empty($oDPS->valores->trib->totTrib->pTotTribFed)],
			['begin' => '{@ifPTotTribEst}', 'end' => '{@endifPTotTribEst}', 'bool' => !empty($oDPS->valores->trib->totTrib->pTotTribEst)],
			['begin' => '{@ifPTotTribMun}', 'end' => '{@endifPTotTribMun}', 'bool' => !empty($oDPS->valores->trib->totTrib->pTotTribMun)],
			
			// IBS/CBS
			['begin' => '{@ifTpOper}', 'end' => '{@endifTpOper}', 'bool' => !empty($oDPS->IBSCBS->tpOper)],
			['begin' => '{@ifGRefNFSe}', 'end' => '{@endifGRefNFSe}', 'bool' => !empty($oDPS->IBSCBS->gRefNFSe)],
			['begin' => '{@ifTpEnteGov}', 'end' => '{@endifTpEnteGov}', 'bool' => !empty($oDPS->IBSCBS->tpEnteGov)],
			['begin' => '{@ifDest}', 'end' => '{@endifDest}', 'bool' => !empty($oDPS->IBSCBS->dest->xNome)],
			['begin' => '{@ifCNPJDest}', 'end' => '{@endifCNPJDest}', 'bool' => strlen($oDPS->IBSCBS->dest->CNPJ ?? '') == 14],
			['begin' => '{@ifCPFDest}', 'end' => '{@endifCPFDest}', 'bool' => strlen($oDPS->IBSCBS->dest->CPF ?? '') == 11],
			['begin' => '{@ifNIFDest}', 'end' => '{@endifNIFDest}', 'bool' => !empty($oDPS->IBSCBS->dest->NIF)],
			['begin' => '{@ifCNaoNIFDest}', 'end' => '{@endifCNaoNIFDest}', 'bool' => !empty($oDPS->IBSCBS->dest->cNaoNIF)],
			['begin' => '{@ifEndDest}', 'end' => '{@endifEndDest}', 'bool' => !empty($oDPS->IBSCBS->dest->end->xLgr)],
			['begin' => '{@ifEndNacDest}', 'end' => '{@endifEndNacDest}', 'bool' => !empty($oDPS->IBSCBS->dest->end->endNacEndExt->cMun)],
			['begin' => '{@ifEndExtDest}', 'end' => '{@endifEndExtDest}', 'bool' => !empty($oDPS->IBSCBS->dest->end->endNacEndExt->cPais)],
			['begin' => '{@ifXCplDest}', 'end' => '{@endifXCplDest}', 'bool' => !empty($oDPS->IBSCBS->dest->end->xCpl)],
			['begin' => '{@ifFoneDest}', 'end' => '{@endifFoneDest}', 'bool' => !empty($oDPS->IBSCBS->dest->fone)],
			['begin' => '{@ifEmailDest}', 'end' => '{@endifEmailDest}', 'bool' => !empty($oDPS->IBSCBS->dest->email)],
			['begin' => '{@ifImovel}', 'end' => '{@endifImovel}', 'bool' => !empty($oDPS->IBSCBS->imovel)],
			['begin' => '{@ifInscImobFiscImovel}', 'end' => '{@endifInscImobFiscImovel}', 'bool' => !empty($oDPS->IBSCBS->imovel->inscImobFisc)],
			['begin' => '{@ifCCIBImovel}', 'end' => '{@endifCCIBImovel}', 'bool' => !empty($oDPS->IBSCBS->imovel->cCIB)],
			['begin' => '{@ifEndImovel}', 'end' => '{@endifEndImovel}', 'bool' => !empty($oDPS->IBSCBS->imovel->end->xLgr)],
			['begin' => '{@ifCEPImovel}', 'end' => '{@endifCEPImovel}', 'bool' => !empty($oDPS->IBSCBS->imovel->end->CEP)],
			['begin' => '{@ifEndExtImovel}', 'end' => '{@endifEndExtImovel}', 'bool' => !empty($oDPS->IBSCBS->imovel->end->endNacEndExt->cEndPost)],
			['begin' => '{@ifXCplImovel}', 'end' => '{@endifXCplImovel}', 'bool' => !empty($oDPS->IBSCBS->imovel->end->xCpl)],
			['begin' => '{@ifGReeRepRes}', 'end' => '{@endifGReeRepRes}', 'bool' => !empty($oDPS->IBSCBS->valores->gReeRepRes)],
			['begin' => '{@ifTribIBS}', 'end' => '{@endifTribIBS}', 'bool' => !empty($oDPS->IBSCBS->valores->trib->IBS->polTrib)],
			['begin' => '{@ifVBCIBS}', 'end' => '{@endifVBCIBS}', 'bool' => !empty($oDPS->IBSCBS->valores->trib->IBS->vBC)],
			['begin' => '{@ifPAliqIBS}', 'end' => '{@endifPAliqIBS}', 'bool' => !empty($oDPS->IBSCBS->valores->trib->IBS->pAliq)],
			['begin' => '{@ifVTribIBS}', 'end' => '{@endifVTribIBS}', 'bool' => !empty($oDPS->IBSCBS->valores->trib->IBS->vTrib)],
			['begin' => '{@ifTribCBS}', 'end' => '{@endifTribCBS}', 'bool' => !empty($oDPS->IBSCBS->valores->trib->CBS->polTrib)],
			['begin' => '{@ifVBCCBS}', 'end' => '{@endifVBCCBS}', 'bool' => !empty($oDPS->IBSCBS->valores->trib->CBS->vBC)],
			['begin' => '{@ifPAliqCBS}', 'end' => '{@endifPAliqCBS}', 'bool' => !empty($oDPS->IBSCBS->valores->trib->CBS->pAliq)],
			['begin' => '{@ifVTribCBS}', 'end' => '{@endifVTribCBS}', 'bool' => !empty($oDPS->IBSCBS->valores->trib->CBS->vTrib)],
		);

		return $this->retXML(PQDUtil::procTplText($tplDPS, $aReplace, $aIfs));
	}

	/**
	 * Retorna o XML da requisição SOAP de acordo com o template 'soap'
	 * 
	 * @return string
	 */
	private function retXMLSoap($xml, $action, array $aReplaces = []) {

		$tpl = $this->getTemplate('soap');

		$aRep = $this->retReplaceUsuarios('soap');

		$aRep['replace']['{@xml}'] = $xml;
		$aRep['replace']['{@action}'] = $action;

		foreach($aReplaces as $k => $v)
			$aRep['replace']['{@' . $k . '}'] = $v;

		return $this->retXML(PQDUtil::procTplText($tpl, $aRep['replace'], $aRep['ifs']), false);
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

	/**
	 * Cancela uma NFS-e usando o padrão nacional
	 * 
	 * @param string $chNFSe - Chave da NFS-e
	 * @param string $nPedRegEvento - Número do pedido de registro de evento
	 * @param string $tpEvento - Tipo do evento (cancelamento)
	 * @param string $xMotivo - Motivo do cancelamento (opcional)
	 * @return array
	 */
	public function cancelarNFSeEnvio($chNFSe, $nPedRegEvento, $tpEvento = '101101', $xMotivo = null) {
		
		$metodo = 'cancelarNFSeEnvio';
		$fileName = $chNFSe . ".xml";

		// Gerar XML de cancelamento
		$tpl = $this->getTemplate($metodo);

		$cpfCnpj = $this->aConfig['cpfCnpj'];
		
		$aReplaces = $this->retReplaceUsuarios('xml');
		$aReplaces['replace']['{@tpAmb}'] = $this->isHomologacao ? '2' : '1';
		$aReplaces['replace']['{@verAplic}'] = PQDUtil::retDefault($this->aConfig, 'verAplic', '1.01');
		$aReplaces['replace']['{@dhEvento}'] = date('Y-m-d\TH:i:s');
		$aReplaces['replace']['{@CnpjCpf}'] = $cpfCnpj;
		$aReplaces['replace']['{@chNFSe}'] = $chNFSe;
		$aReplaces['replace']['{@nPedRegEvento}'] = $nPedRegEvento;
		$aReplaces['replace']['{@tpEvento}'] = $tpEvento;

		foreach($aReplaces['replace'] as $k => $v){
			$field = str_replace(['{@', '}'], '', $k);
			$field = strtolower(substr($field, 0, 1)) . substr($field, 1);
			$aReplaces['replace'][$k] = $this->applyFnField($field, $v);
		}

		$aReplaces['ifs'][] = array('begin' => '{@ifXMotivo}', 'end' => '{@endifXMotivo}', 'bool' => !is_null($xMotivo));
		
		if(!is_null($xMotivo)){
			$aReplaces['replace']['{@codCancelamento}'] = '<tc:xMotivo>' . htmlspecialchars($xMotivo) . '</tc:xMotivo>';
		} else {
			$aReplaces['replace']['{@codCancelamento}'] = '';
		}

		$xml = $this->retXML(PQDUtil::procTplText($tpl, $aReplaces['replace'], $aReplaces['ifs']));
		
		// Assinar XML se necessário
		if(isset($this->aConfig['metodos'][$metodo]['tagSign'])){
			$xml = $this->signXML($xml, 
				$this->aConfig['metodos'][$metodo]['tagSign'], 
				$this->aConfig['metodos'][$metodo]['tagAppend'], 
				$this->aConfig['metodos'][$metodo]['nameSpace'],
				true
			);
		}

		$this->saveXML($xml, $metodo . '-' . $fileName);

		return $this->procReturn($this->makeSOAPRequest($metodo, $xml, $fileName), $metodo);
	}

	/**
	 * Consulta um lote de DPS pelo protocolo
	 * 
	 * @param string $protocolo - Protocolo do lote
	 * @return array
	 */
	public function consultarLoteDpsEnvio($protocolo) {
		
		$metodo = 'consultarLoteDpsEnvio';
		$fileName = $protocolo . ".xml";

		$tpl = $this->getTemplate($metodo);

		$cpfCnpj = $this->aConfig['cpfCnpj'];
		$inscMunicipal = $this->aConfig['insMunicipal'];

		$aReplace = array(
			'{@CNPJPrestador}' => $cpfCnpj,
			'{@CPFPrestador}' => $cpfCnpj,
			'{@IMPrestador}' => $inscMunicipal,
			'{@Protocolo}' => $protocolo
		);

		foreach($aReplace as $k => $v){
			$field = str_replace(['{@', '}'], '', $k);
			$field = strtolower(substr($field, 0, 1)) . substr($field, 1);
			$aReplace[$k] = $this->applyFnField($field, $v);
		}

		$aIfs = array(
			array('begin' => '{@ifCPFPrestador}', 'end' => '{@endifCPFPrestador}', 'bool' => strlen($cpfCnpj) == 11),
			array('begin' => '{@ifCNPJPrestador}', 'end' => '{@endifCNPJPrestador}', 'bool' => strlen($cpfCnpj) == 14),
		);

		$xml = $this->retXML(PQDUtil::procTplText($tpl, $aReplace, $aIfs));

		// Assinar se necessário
		if(isset($this->aConfig['metodos'][$metodo]['signConsulta']) && $this->aConfig['metodos'][$metodo]['signConsulta']){
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

	/**
	 * Consulta uma NFS-e por DPS
	 * 
	 * @param string $numDPS - Número do DPS
	 * @param string $serieDPS - Série do DPS
	 * @param string $protocolo - Protocolo (opcional)
	 * @return array
	 */
	public function consultarNfseDpsEnvio($numDPS, $serieDPS, $protocolo = null) {
		
		$metodo = 'consultarNfseDpsEnvio';
		$fileName = $numDPS . "-" . $serieDPS . ".xml";

		$tpl = $this->getTemplate($metodo);

		$cpfCnpj = $this->aConfig['cpfCnpj'];
		$inscMunicipal = $this->aConfig['insMunicipal'];

		$aReplace = array(
			'{@NumDPS}' => $numDPS,
			'{@SerieDPS}' => $serieDPS,
			'{@CNPJPrestador}' => $cpfCnpj,
			'{@CPFPrestador}' => $cpfCnpj,
			'{@IMPrestador}' => $inscMunicipal,
			'{@Protocolo}' => $protocolo
		);

		foreach($aReplace as $k => $v){
			$field = str_replace(['{@', '}'], '', $k);
			$field = strtolower(substr($field, 0, 1)) . substr($field, 1);
			$aReplace[$k] = $this->applyFnField($field, $v);
		}

		$aIfs = array(
			array('begin' => '{@ifCPFPrestador}', 'end' => '{@endifCPFPrestador}', 'bool' => strlen($cpfCnpj) == 11),
			array('begin' => '{@ifCNPJPrestador}', 'end' => '{@endifCNPJPrestador}', 'bool' => strlen($cpfCnpj) == 14),
			array('begin' => '{@ifProtocolo}', 'end' => '{@endifProtocolo}', 'bool' => !is_null($protocolo)),
		);

		$xml = $this->retXML(PQDUtil::procTplText($tpl, $aReplace, $aIfs));

		// Assinar se necessário
		if(isset($this->aConfig['metodos'][$metodo]['signConsulta']) && $this->aConfig['metodos'][$metodo]['signConsulta']){
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

	/**
	 * Envia um lote de DPS
	 * 
	 * @param array[NFSeGenericoInfDPS] $aDps - Array de objetos DPS
	 * @param string $numeroLote - Número do lote
	 * @return array
	 */
	public function enviarLoteDpsEnvio(array $aDps, $numeroLote) {
		
		$metodo = 'enviarLoteDpsEnvio';
		$fileName = $numeroLote . ".xml";
		$xml = "";

		// Buscar textos que devem ser substituidos antes da assinatura
		$search = PQDUtil::retDefault($this->aConfig['metodos'][$metodo], 'search', null);
		$search = is_null($search) ? array("\r\n", "\n", "\r", "\t") : array_map('stripcslashes', $search);

		$replace = PQDUtil::retDefault($this->aConfig['metodos'][$metodo], 'replace', null);
		$replace = is_null($replace) ? "" : ( is_array($replace) ? array_map('stripcslashes', $replace) : $replace );

		/**
		 * @var NFSeGenericoInfDPS $oDps
		 */
		foreach($aDps as $oDps){
			$dps = $this->retXMLDPS($oDps);

			if(isset($this->aConfig['metodos'][$metodo]['signDps']) && $this->aConfig['metodos'][$metodo]['signDps']){
				$dps = $this->signXML(
					$dps, 
					PQDUtil::retDefault($this->aConfig['metodos'][$metodo], 'tagSignDps', 'infDPS'), 
					PQDUtil::retDefault($this->aConfig['metodos'][$metodo], 'tagAppendDps', 'DPS'), 
					PQDUtil::retDefault($this->aConfig['metodos'][$metodo], 'nameSpaceDps', ''),
					true,
					true,
					$search,
					$replace
				);
			}

			$xml .= $dps;
		}

		$aReplaces = $this->retReplaceUsuarios('xml');

		$aReplace = $aReplaces['replace'];
		$aReplace['{@idRps}'] = $numeroLote;
		$aReplace['{@NumeroLote}'] = $numeroLote;
		$aReplace['{@CPFPrestador}'] = $this->aConfig['cpfCnpj'];
		$aReplace['{@CNPJPrestador}'] = $this->aConfig['cpfCnpj'];
		$aReplace['{@IMPrestador}'] = $this->aConfig['insMunicipal'];
		$aReplace['{@QuantidadeDps}'] = count($aDps);
		$aReplace['{@ListaDps}'] = $xml;

		foreach($aReplace as $k => $v){
			$field = str_replace(['{@', '}'], '', $k);
			$field = strtolower(substr($field, 0, 1)) . substr($field, 1);
			$aReplace[$k] = $this->applyFnField($field, $v);
		}

		$aIfs = $aReplaces['ifs'];
		$aIfs[] = array('begin' => '{@ifCPFPrestador}', 'end' => '{@endifCPFPrestador}', 'bool' => strlen($this->aConfig['cpfCnpj']) == 11);
		$aIfs[] = array('begin' => '{@ifCNPJPrestador}', 'end' => '{@endifCNPJPrestador}', 'bool' => strlen($this->aConfig['cpfCnpj']) == 14);

		$tplLista = $this->getTemplate('enviarLoteDpsEnvio');

		$xml = $this->retXML(PQDUtil::procTplText($tplLista, $aReplace, $aIfs));

		// Assinando o lote
		if(isset($this->aConfig['metodos'][$metodo]['tagSign'])){
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
		}
		
		if($this->isHomologacao)
			$this->saveXML($xml, $metodo . '-' . $fileName);

		return $this->procReturn($this->makeSOAPRequest($metodo, $xml, $fileName), $metodo);
	}
}

<?php
namespace NFSe\generico;

use NFSe\NFSeReturn;
use NFSe\NFSeDocument;
use PQD\PQDUtil;

class NFSeGenericoReturn extends NFSeReturn {

	private $oGenerico;

	public function __construct(NFSeGenerico $oGenerico){
		$this->oGenerico = $oGenerico;
	}

	/**
	 * @param NFSeDocument $oDocument
	 * @return array[NFSeGenericoMensagemRetorno]
	 */
	private function retListaMensagem(NFSeDocument $oDocument, $contextNode = null, $listaMensagem = 'ListaMensagemRetorno') {
		
		$return = array();

		$ListaMensagemRetorno = $oDocument->documentElement->getElementsByTagName($listaMensagem);

		if($ListaMensagemRetorno->length == 1) {

			$ListaMensagemRetorno = $ListaMensagemRetorno->item(0);

			$aMensagens = $ListaMensagemRetorno->getElementsByTagName('MensagemRetorno');

			for ($i = 0; $i< $aMensagens->length; $i++){

				$oMensagem = new NFSeGenericoMensagemRetorno();
				$oMensagem->Codigo = $oDocument->getValue($aMensagens->item($i), 'Codigo');//codigo do erro
				$oMensagem->Mensagem = $oDocument->getValue($aMensagens->item($i), 'Mensagem');//mensagem de erro
				$oMensagem->Correcao = $oDocument->getValue($aMensagens->item($i), 'Correcao');//correcao

				$return[] = $oMensagem;
				
			}

		}

		return $return;

	}

	private function retMsgForaEsperado() {

		$oMensagem = new NFSeGenericoMensagemRetorno();
		$oMensagem->Mensagem = "Retorno fora do formato esperado!";//mensagem de erro
		$oMensagem->Correcao = "Verificar XML Retornado!";//correcao

		return $oMensagem;

	}

	private function retInfNFSe(\DOMElement $oCompNfse, NFSeDocument $oDocument) {

		$aConfig = $this->oGenerico->getConfig($this->oGenerico->getIsHomologacao() ? 'homologacao': 'producao', array());

		if( $oCompNfse->getElementsByTagName('Nfse')->length == 0 )
			return null;

		$Nfse = $oCompNfse->getElementsByTagName('Nfse')->item(0);

		$InfNfse 					= $Nfse->getElementsByTagName('InfNfse')->item(0);
		$ValoresNfse 				= $Nfse->getElementsByTagName('ValoresNfse')->item(0);
		$EnderecoPrestadorServico 	= $Nfse->getElementsByTagName('EnderecoPrestadorServico')->item(0);
		$DeclaracaoPrestacaoServico = $Nfse->getElementsByTagName('DeclaracaoPrestacaoServico')->item(0);

		$InfDeclaracaoPrestacaoServico = $DeclaracaoPrestacaoServico->getElementsByTagName('InfDeclaracaoPrestacaoServico')->item(0);

		//Rps e opciional
		$Rps = $InfDeclaracaoPrestacaoServico->getElementsByTagName("Rps")->item(0);

		$IdentificacaoRps = $Rps->getElementsByTagName("IdentificacaoRps")->item(0);

		$Servico = $InfDeclaracaoPrestacaoServico->getElementsByTagName("Servico")->item(0);

		$Prestador = $InfDeclaracaoPrestacaoServico->getElementsByTagName("Prestador")->item(0);

		//Tomador e opciional
		$IdentificacaoTomador = $Tomador = null;

		if($InfDeclaracaoPrestacaoServico->getElementsByTagName("Tomador")->length == 1)
			$Tomador = $InfDeclaracaoPrestacaoServico->getElementsByTagName("Tomador")->item(0);
		else if($InfDeclaracaoPrestacaoServico->getElementsByTagName("TomadorServico")->length == 1)
			$Tomador = $InfDeclaracaoPrestacaoServico->getElementsByTagName("TomadorServico")->item(0);

		if( !is_null($Tomador) ){
			$IdentificacaoTomador = $Tomador->getElementsByTagName("IdentificacaoTomador")->item(0);
	
			$EnderecoTomador = $Tomador->getElementsByTagName("Endereco")->item(0);
	
			$ContatoTomador = $Tomador->getElementsByTagName("Contato")->item(0);
		}

		$oNFSeGenericoInfNFSe = new NFSeGenericoInfNFSe();
		$oNFSeGenericoInfNFSe->Numero = $oDocument->getValue($InfNfse, "Numero");
		$oNFSeGenericoInfNFSe->CodigoVerificacao = $oDocument->getValue($InfNfse, "CodigoVerificacao");
		$oNFSeGenericoInfNFSe->DataEmissao = $oDocument->getValue($InfNfse, "DataEmissao");
		$oNFSeGenericoInfNFSe->StatusNfse = $oDocument->getValue($InfNfse, "StatusNfse");
		$oNFSeGenericoInfNFSe->NfseSubstituida = $oDocument->getValue($InfNfse, "NfseSubstituida");
		$oNFSeGenericoInfNFSe->OutrasInformacoes = $oDocument->getValue($InfNfse, "OutrasInformacoes");

		$url = $oDocument->getValue($InfNfse, "UrlNfse");
		$url = is_null($url) ? PQDUtil::retDefault($aConfig, 'urlNfse', '') : $url;
		$url = PQDUtil::procTplText($url, array(
			'{@numeroNFSe}' => $oDocument->getValue($InfNfse, "Numero"),
			'{@codigoVerificacao}' => $oDocument->getValue($InfNfse, "CodigoVerificacao"),
			'{@sha1CodigoVerificacao}' => sha1($oDocument->getValue($InfNfse, "CodigoVerificacao")),
			'{@idInfNfse}' => $InfNfse->getAttribute('Id')
		));

		$oNFSeGenericoInfNFSe->Url = ( !empty($url) && substr($url, 0, 7) != 'http://' && substr($url, 0, 8) != 'https://' ? 'http://' : '') . $url;
		$oNFSeGenericoInfNFSe->ValorCredito = $oDocument->getValue($InfNfse, "ValorCredito");

		$oNFSeGenericoInfNFSe->IdentificacaoRps->Numero = $oDocument->getValue($IdentificacaoRps, "Numero");
		$oNFSeGenericoInfNFSe->IdentificacaoRps->Tipo = $oDocument->getValue($IdentificacaoRps, "Tipo");
		$oNFSeGenericoInfNFSe->DataEmissaoRps = $oDocument->getValue($Rps, "DataEmissao");

		$Valores = $Servico->getElementsByTagName("Valores")->item(0);

		$oNFSeGenericoInfNFSe->Servico->Valores->ValorServicos = $oDocument->getValue($Valores, "ValorServicos");
		$oNFSeGenericoInfNFSe->Servico->Valores->BaseCalculo = $oDocument->getValue($ValoresNfse, "BaseCalculo");
		$oNFSeGenericoInfNFSe->Servico->Valores->Aliquota = $oDocument->getValue($ValoresNfse, "Aliquota");
		$oNFSeGenericoInfNFSe->Servico->Valores->ValorIss = $oDocument->getValue($ValoresNfse, "ValorIss");
		$oNFSeGenericoInfNFSe->Servico->Valores->ValorLiquidoNfse = $oDocument->getValue($ValoresNfse, "ValorLiquidoNfse");

		$oNFSeGenericoInfNFSe->Servico->Valores->ValorDeducoes = $oDocument->getValue($Valores, "ValorDeducoes");
		$oNFSeGenericoInfNFSe->Servico->Valores->ValorPis = $oDocument->getValue($Valores, "ValorPis");
		$oNFSeGenericoInfNFSe->Servico->Valores->ValorCofins = $oDocument->getValue($Valores, "ValorCofins");
		$oNFSeGenericoInfNFSe->Servico->Valores->ValorInss = $oDocument->getValue($Valores, "ValorInss");
		$oNFSeGenericoInfNFSe->Servico->Valores->ValorIr = $oDocument->getValue($Valores, "ValorIr");
		$oNFSeGenericoInfNFSe->Servico->Valores->ValorCsll = $oDocument->getValue($Valores, "ValorCsll");
		$oNFSeGenericoInfNFSe->Servico->Valores->ValorIssRetido = $oDocument->getValue($Valores, "ValorIssRetido");

		$oNFSeGenericoInfNFSe->Servico->Valores->OutrasRetencoes = $oDocument->getValue($Valores, "OutrasRetencoes");
		$oNFSeGenericoInfNFSe->Servico->Valores->DescontoCondicionado = $oDocument->getValue($Valores, "DescontoCondicionado");
		$oNFSeGenericoInfNFSe->Servico->Valores->DescontoIncondicionado = $oDocument->getValue($Valores, "DescontoIncondicionado");

		$oNFSeGenericoInfNFSe->Servico->ItemListaServico = $oDocument->getValue($Servico, "ItemListaServico");
		$oNFSeGenericoInfNFSe->Servico->CodigoCnae = $oDocument->getValue($Servico, "CodigoCnae");
		$oNFSeGenericoInfNFSe->Servico->CodigoTributacaoMunicipio = $oDocument->getValue($Servico, "CodigoTributacaoMunicipio");
		$oNFSeGenericoInfNFSe->Servico->Discriminacao = $oDocument->getValue($Servico, "Discriminacao");
		$oNFSeGenericoInfNFSe->Servico->CodigoMunicipio = $oDocument->getValue($Servico, "CodigoMunicipio");
		$oNFSeGenericoInfNFSe->Servico->ExigibilidadeISS = $oDocument->getValue($Servico, "ExigibilidadeISS");

		if($Prestador->getElementsByTagName("Cnpj")->length == 1) {
			$oNFSeGenericoInfNFSe->PrestadorServico->Identificacao->CpfCnpj = $oDocument->getValue($Prestador, "Cnpj");
		} else {
			$oNFSeGenericoInfNFSe->PrestadorServico->Identificacao->CpfCnpj = $oDocument->getValue($Prestador, "Cpf");
		}

		$oNFSeGenericoInfNFSe->PrestadorServico->Identificacao->InscricaoMunicipal = $oDocument->getValue($Prestador, "InscricaoMunicipal");

		$oNFSeGenericoInfNFSe->PrestadorServico->Endereco->TipoLogradouro = $oDocument->getValue($EnderecoPrestadorServico, "TipoLogradouro");
		$oNFSeGenericoInfNFSe->PrestadorServico->Endereco->Logradouro = $oDocument->getValue($EnderecoPrestadorServico, "Logradouro");
		$oNFSeGenericoInfNFSe->PrestadorServico->Endereco->Numero = $oDocument->getValue($EnderecoPrestadorServico, "Numero");
		$oNFSeGenericoInfNFSe->PrestadorServico->Endereco->Complemento = $oDocument->getValue($EnderecoPrestadorServico, "Complemento");
		$oNFSeGenericoInfNFSe->PrestadorServico->Endereco->Bairro = $oDocument->getValue($EnderecoPrestadorServico, "Bairro");
		$oNFSeGenericoInfNFSe->PrestadorServico->Endereco->CodigoMunicipio = $oDocument->getValue($EnderecoPrestadorServico, "CodigoMunicipio");
		$oNFSeGenericoInfNFSe->PrestadorServico->Endereco->CodigoPaisEstrangeiro = $oDocument->getValue($EnderecoPrestadorServico, "CodigoPaisEstrangeiro");
		$oNFSeGenericoInfNFSe->PrestadorServico->Endereco->EstadoPaisEstrangeiro = $oDocument->getValue($EnderecoPrestadorServico, "EstadoPaisEstrangeiro");
		$oNFSeGenericoInfNFSe->PrestadorServico->Endereco->CidadePaisEstrangeiro = $oDocument->getValue($EnderecoPrestadorServico, "CidadePaisEstrangeiro");
		$oNFSeGenericoInfNFSe->PrestadorServico->Endereco->Uf = $oDocument->getValue($EnderecoPrestadorServico, "Uf");
		$oNFSeGenericoInfNFSe->PrestadorServico->Endereco->Cep = $oDocument->getValue($EnderecoPrestadorServico, "Cep");


		if(!is_null($IdentificacaoTomador)){

			if($IdentificacaoTomador->getElementsByTagName("Cnpj")->length == 1) {
				$oNFSeGenericoInfNFSe->TomadorServico->IdentificacaoTomador->CpfCnpj = $oDocument->getValue($IdentificacaoTomador, "Cnpj");
			} else {
				$oNFSeGenericoInfNFSe->TomadorServico->IdentificacaoTomador->CpfCnpj = $oDocument->getValue($IdentificacaoTomador, "Cpf");
			}
	
			$oNFSeGenericoInfNFSe->TomadorServico->IdentificacaoTomador->InscricaoMunicipal = $oDocument->getValue($IdentificacaoTomador, "InscricaoMunicipal");
		}

		$oNFSeGenericoInfNFSe->TomadorServico->RazaoSocial = $oDocument->getValue($Tomador, "RazaoSocial");

		$oNFSeGenericoInfNFSe->TomadorServico->Endereco->TipoLogradouro = $oDocument->getValue($EnderecoTomador, "TipoLogradouro");
		$oNFSeGenericoInfNFSe->TomadorServico->Endereco->Logradouro = $oDocument->getValue($EnderecoTomador, "Logradouro");
		$oNFSeGenericoInfNFSe->TomadorServico->Endereco->Numero = $oDocument->getValue($EnderecoTomador, "Numero");
		$oNFSeGenericoInfNFSe->TomadorServico->Endereco->Complemento = $oDocument->getValue($EnderecoTomador, "Complemento");
		$oNFSeGenericoInfNFSe->TomadorServico->Endereco->Bairro = $oDocument->getValue($EnderecoTomador, "Bairro");
		$oNFSeGenericoInfNFSe->TomadorServico->Endereco->CodigoMunicipio = $oDocument->getValue($EnderecoTomador, "CodigoMunicipio");
		$oNFSeGenericoInfNFSe->TomadorServico->Endereco->CodigoPaisEstrangeiro = $oDocument->getValue($EnderecoTomador, "CodigoPaisEstrangeiro");
		$oNFSeGenericoInfNFSe->TomadorServico->Endereco->EstadoPaisEstrangeiro = $oDocument->getValue($EnderecoTomador, "EstadoPaisEstrangeiro");
		$oNFSeGenericoInfNFSe->TomadorServico->Endereco->CidadePaisEstrangeiro = $oDocument->getValue($EnderecoTomador, "CidadePaisEstrangeiro");
		$oNFSeGenericoInfNFSe->TomadorServico->Endereco->Uf = $oDocument->getValue($EnderecoTomador, "Uf");
		$oNFSeGenericoInfNFSe->TomadorServico->Endereco->Cep = $oDocument->getValue($EnderecoTomador, "Cep");

		$oNFSeGenericoInfNFSe->TomadorServico->Contato->Ddd = $oDocument->getValue($ContatoTomador, "Ddd");
		$oNFSeGenericoInfNFSe->TomadorServico->Contato->Telefone = $oDocument->getValue($ContatoTomador, "Telefone");
		$oNFSeGenericoInfNFSe->TomadorServico->Contato->TipoTelefone = $oDocument->getValue($ContatoTomador, "TipoTelefone");
		$oNFSeGenericoInfNFSe->TomadorServico->Contato->Email = $oDocument->getValue($ContatoTomador, "Email");

		return $oNFSeGenericoInfNFSe;
	}

	/**
	 *
	 * @param NFSeDocument $oDocument
	 * @return array[NFSeGenericoInfNFSe]
	 */
	private function retListNFSe(NFSeDocument $oDocument) {

		$ListaNfse = $oDocument->getElementsByTagName("ListaNfse");

		if (is_null($ListaNfse) || $ListaNfse->length != 1)
			return array();

		$ListaNfse = $ListaNfse->item(0);

		$return = array('CompNfse' => array(), 'ListaMensagemAlertaRetorno' => $this->retListaMensagem($oDocument, $ListaNfse, 'ListaMensagemAlertaRetorno'));
		$aCompNfse = $ListaNfse->getElementsByTagName('CompNfse');
		
		for ($i = 0; $i < $aCompNfse->length; $i++) {

			/**
			 * @var \DOMElement $CompNfse
			 * @var \DOMElement $Nfse
			 * @var \DOMElement $InfNfse
			 * @var \DOMElement $ValoresNfse
			 * @var \DOMElement $EnderecoPrestadorServico
			 * @var \DOMElement $DeclaracaoPrestacaoServico
			 * @var \DOMElement $InfDeclaracaoPrestacaoServico
			 */
			$CompNfse = $aCompNfse->item($i);

			$return['CompNfse'][] = $this->retInfNFSe($CompNfse, $oDocument);

		}

		return $return;

	}

	private function retDocReturn(NFSeDocument $dom, $metodo){

		$aConfig = $this->oGenerico->getConfig('metodos', array());
		$xml = "";
		$oReturn = $dom->getElementsByTagName($aConfig[$metodo]['tagMap']['return'])->item(0);

		if(PQDUtil::retDefault($aConfig[$metodo], 'returnType', 'child') == 'string')
			$xml = htmlspecialchars_decode( $oReturn->nodeValue );
		else
			$xml = $dom->saveXML($oReturn);

		$oReturnDocument  = new NFSeDocument();
		$oReturnDocument->loadXML($xml, LIBXML_NOERROR);

		return $oReturnDocument;
	}

	/**
	 *
	 * @param string $return
	 * @param string $action
	 * @throws \Exception
	 *
	 * @return NFSeDocument
	 */
	public function getReturn($return, $metodo) {

		$oReturn = new NFSeDocument();

		if(!empty($return)) {

			$encoding = mb_detect_encoding($return, array('UTF-8', 'ISO-8859-1', 'WINDOWS-1252'), false);
			if($encoding == 'ISO-8859-1' || $encoding == 'WINDOWS-1252')
				$return = iconv($encoding, 'UTF-8', $return);

			$dom = new NFSeDocument();
			$dom->loadXML($return);
			$fault = self::checkForFault($dom);

			if(!empty($fault)) {
				
				$fault;
				$oMensagem = new NFSeGenericoMensagemRetorno();
				$oMensagem->Mensagem = $fault;//mensagem de erro

				return array(
					'ListaMensagemRetorno' => array(
						$oMensagem
					)
				);

			}

			$oDocument = $this->retDocReturn($dom, $metodo);
			switch ($metodo) {

				case "cancelarNfse":
					return $this->cancelarNfseResposta($oDocument);
				break;
				
				case "gerarNfse":
					return $this->gerarNfseRetorno($oDocument);
				break;
				
				case "enviarLoteRps":
					return $this->enviarLoteRpsRetorno($oDocument);
				break;

				case "consultarNFSePorRps":
					return array(
						'ListaMensagemRetorno' => $this->retListaMensagem($oDocument),
						'CompNfse' => $this->retInfNFSe($oDocument->firstChild, $oDocument)
					);
				break;

				case "consultarUrlNfse":
					$aListaLinks = [];

					if($oDocument->getElementsByTagName('ListaLinks')->length == 1){
						$oListaLinks = $oDocument->getElementsByTagName('ListaLinks')->item(0)->getElementsByTagName('Links');

						for ($i = 0; $i< $oListaLinks->length; $i++){
							$oLink = $oListaLinks->item($i);

							$aLink = [];

							$oIdentificacaoNfse = $oLink->getElementsByTagName('IdentificacaoNfse')->item(0);
							$aLink['CodigoMunicipioGerador'] = $oDocument->getValue($oIdentificacaoNfse, 'CodigoMunicipio');
							$aLink['NumeroNfse'] = $oDocument->getValue($oIdentificacaoNfse, 'Numero');
							$aLink['CodigoVerificacao'] = $oDocument->getValue($oIdentificacaoNfse, 'CodigoVerificacao');

							if(!is_null($oDocument->getValue($oLink, 'UrlVisualizacaoNfse')))
								$aLink['Url'] = $oDocument->getValue($oLink, 'UrlVisualizacaoNfse');

							if(!is_null($oDocument->getValue($oLink, 'UrlVerificaAutenticidade')))
								$aLink['UrlAutenticidade'] = $oDocument->getValue($oLink, 'UrlVerificaAutenticidade');

							$aListaLinks[] = $aLink;
						}
					}

					return array(
						'ListaMensagemRetorno' => $this->retListaMensagem($oDocument),
						'ListaLinks' => $aListaLinks
					);
				break;
				
				case "consultarNfseServicoPrestado":

					$oReturn = $dom->getElementsByTagName("ConsultarNfseServicoPrestadoResposta")->item(0);
					$oConsultarNfseRpsResposta = new NFSeDocument();

					$oConsultarNfseRpsResposta->loadXML($oReturn->C14N());

					if(!is_null($pathFile))
						file_put_contents($pathFile, $oConsultarNfseRpsResposta->saveXML());

					$return = array(
						'ListaMensagemRetorno' => $this->retListaMensagem($oConsultarNfseRpsResposta)
					);

					$CompNfse = $oConsultarNfseRpsResposta->getElementsByTagName('CompNfse');

					if ($CompNfse->length == 1)
						$return['CompNfse'] = $this->retInfNFSe($CompNfse->item(0), $oConsultarNfseRpsResposta);

					return $return;

				break;
				
				case "consultarLoteRps":

					return $this->consultarLoteRpsRetorno($oDocument);

				break;

				case "ConsultarNfseFaixa":

					$oReturn = $dom->getElementsByTagName("ConsultarNfseFaixaResposta")->item(0);
					$oLoteRpsResposta = new NFSeDocument();
					$oLoteRpsResposta->loadXML($oReturn->nodeValue);

					if(!is_null($pathFile)) {
						file_put_contents($pathFile, $oLoteRpsResposta->saveXML());
					}

					return $this->gerarLoteRpsResposta($oLoteRpsResposta);

				break;

				default:
					throw new \Exception("Retorno nao definido! Action(" . $metodo . ") Retorno: " . PHP_EOL . $return);
				break;
				
			}

		} else {

			$oMensagem = new NFSeGenericoMensagemRetorno();
			$oMensagem->Mensagem = "Nenhum retorno informado!";//mensagem de erro

			return array(
				'ListaMensagemRetorno' => array(
					$oMensagem
				)
			);

			//$oReturn->createElement("fault", $fault);

		}

		return $oReturn;

	}

	/**
	 *
	 * @param NFSeDocument $oCancelarNfseResposta
	 *
	 * @return array
	 */
	private function retCancelamento(NFSeDocument $oCancelarNfseResposta){

		$return = array(
			'NfseCancelamento' => array()
		);

		$RetCancelamento = $oCancelarNfseResposta->getElementsByTagName('RetCancelamento');
		
		if ($RetCancelamento->length > 0) {

			$RetCancelamento = $RetCancelamento->item(0);

			$aNfseCancelamento = $RetCancelamento->getElementsByTagName('NfseCancelamento');

			for ($i = 0; $i < $aNfseCancelamento->length; $i++) {

				$NfseCancelamento = $RetCancelamento->getElementsByTagName('NfseCancelamento')->item($i);

				$Confirmacao = $NfseCancelamento->getElementsByTagName('Confirmacao')->item(0);

				$DataHora = $oCancelarNfseResposta->getValue($Confirmacao, "DataHora");
				$Pedido = $Confirmacao->getElementsByTagName('Pedido')->item(0);


				$InfPedidoCancelamento = $Pedido->getElementsByTagName('InfPedidoCancelamento')->item(0);

				$IdentificacaoNfse = $InfPedidoCancelamento->getElementsByTagName('IdentificacaoNfse')->item(0);

				$oIdentificacaoNfse = new NFSeGenericoIdentificacaoNfse();

				$oIdentificacaoNfse->Numero = $oCancelarNfseResposta->getValue($IdentificacaoNfse, "Numero");

				$CpfCnpj = $IdentificacaoNfse->getElementsByTagName('CpfCnpj')->item(0);

				if($CpfCnpj->getElementsByTagName("Cnpj")->length == 1)
					$oIdentificacaoNfse->CpfCnpj = $oCancelarNfseResposta->getValue($CpfCnpj, "Cnpj");
				else
					$oIdentificacaoNfse->CpfCnpj = $oCancelarNfseResposta->getValue($CpfCnpj, "Cpf");

				$oIdentificacaoNfse->InscricaoMunicipal = $oCancelarNfseResposta->getValue($IdentificacaoNfse, "InscricaoMunicipal");
				$oIdentificacaoNfse->CodigoVerificacao = $oCancelarNfseResposta->getValue($IdentificacaoNfse, "CodigoVerificacao");

				$CodigoCancelamento = $oCancelarNfseResposta->getValue($InfPedidoCancelamento, "CodigoCancelamento");
				$DescricaoCancelamento = $oCancelarNfseResposta->getValue($InfPedidoCancelamento, "DescricaoCancelamento");

				$return['NfseCancelamento'][] = array(
					'Confirmacao' => array(
						'Pedido' => array(
							'InfPedidoCancelamento' => array(
								'IdentificacaoNfse' => $oIdentificacaoNfse,
								'CodigoCancelamento' => $CodigoCancelamento,
								'DescricaoCancelamento' => $DescricaoCancelamento
							)
						),
						'DataHora' => $DataHora
					)
				);

			}

		}

		return $return;

	}

	/**
	 *
	 * @param NFSeDocument $oCancelarNfseResposta
	 *
	 * @return array
	 */
	private function cancelarNfseResposta(NFSeDocument $oCancelarNfseResposta) {
		
		if ($oCancelarNfseResposta->getElementsByTagName('CancelarNfseResposta')->length == 1) {
			
			return array(
				'ListaMensagemRetorno' => $this->retListaMensagem($oCancelarNfseResposta),
				'RetCancelamento' => $this->retCancelamento($oCancelarNfseResposta)
			);

		} else {

			return array('ListaMensagemRetorno' => [$this->retMsgForaEsperado()] );

		}

	}

	/**
	 *
	 * @param NFSeDocument $oGerarNfseRetorno
	 * @return array
	 */
	private function gerarNfseRetorno(NFSeDocument $oGerarNfseRetorno) {

		$return = array();
		if ($oGerarNfseRetorno->getElementsByTagName('GerarNfseResposta')->length == 1) {

			$return = array(
				'ListaMensagemRetorno' => $this->retListaMensagem($oGerarNfseRetorno),
				'ListaNfse' => $this->retListNFSe($oGerarNfseRetorno)
			);

		} else {

			$return = array('ListaMensagemRetorno' => array($this->retMsgForaEsperado()));

		}

		return $return;
	}

	private function enviarLoteRpsRetorno(NFSeDocument $oDocument) {

		$aConfig = $this->oGenerico->getConfig('metodos', array());
		if ($oDocument->getElementsByTagName($aConfig['enviarLoteRps']['tagMap']['respostaLote'])->length == 1) {

			$oEnviarLoteRpsSincronoResposta = $oDocument->getElementsByTagName($aConfig['enviarLoteRps']['tagMap']['respostaLote'])->item(0);
			
			return array(
				'NumeroLote' => $oDocument->getValue($oEnviarLoteRpsSincronoResposta, "NumeroLote"),
				'DataRecebimento' => $oDocument->getValue($oEnviarLoteRpsSincronoResposta, "DataRecebimento"),
				'Protocolo' => $oDocument->getValue($oEnviarLoteRpsSincronoResposta, "Protocolo"),
				'ListaNfse' => $this->retListNFSe($oDocument),
				'ListaMensagemRetorno' => $this->retListaMensagem($oDocument),
				'ListaMensagemRetornoLote' => $this->retListaMensagem($oDocument, null, 'ListaMensagemRetornoLote')
			);

		} 
		
		return array('ListaMensagemRetorno' => [$this->retMsgForaEsperado()]);
	}

	private function consultarLoteRpsRetorno(NFSeDocument $oDocument) {

		$aConfig = $this->oGenerico->getConfig('metodos', array());
		
		if ($oDocument->getElementsByTagName($aConfig['consultarLoteRps']['tagMap']['respostaConsultaLote'])->length == 1) {

			$oConsultarLoteRpsResposta = $oDocument->getElementsByTagName($aConfig['consultarLoteRps']['tagMap']['respostaConsultaLote'])->item(0);
			
			return array(
				'Situacao' => $oDocument->getValue($oConsultarLoteRpsResposta, "Situacao"),
				'ListaNfse' => $this->retListNFSe($oDocument),
				'ListaMensagemRetorno' => $this->retListaMensagem($oDocument),
				'ListaMensagemRetornoLote' => $this->retListaMensagem($oDocument, null, 'ListaMensagemRetornoLote')
			);

		} 

		return array('ListaMensagemRetorno' => [$this->retMsgForaEsperado()]);
	}
}

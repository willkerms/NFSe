<?php
namespace NFSe\sigep;

use NFSe\NFSeReturn;
use NFSe\NFSeDocument;
use PQD\PQDUtil;

class NFSeSigepReturn extends NFSeReturn{

	/**
	 * @param NFSeDocument $oDocument
	 * @return array[NFSeSigepMensagemRetorno]
	 */
	private static function retListaMensagem(NFSeDocument $oDocument, $contextNode = null, $listaMensagem = 'ListaMensagemRetorno'){
		$return = array();

		$xpath = new \DOMXPath($oDocument);
		$nSMain = $oDocument->documentElement->lookupPrefix(NFSeSigep::XMLNS);

		if(empty($nSMain))
			$ListaMensagemRetorno = $oDocument->documentElement->getElementsByTagName($listaMensagem);
		else
			$ListaMensagemRetorno = $xpath->query($nSMain . ':'  . $listaMensagem, $contextNode);

		if($ListaMensagemRetorno->length == 1){

			$ListaMensagemRetorno = $ListaMensagemRetorno->item(0);

			if(empty($nSMain))
				$aMensagens = $ListaMensagemRetorno->getElementsByTagName('MensagemRetorno');
			else
				$aMensagens = $xpath->query($nSMain . ':MensagemRetorno', $ListaMensagemRetorno);

			for ($i = 0; $i< $aMensagens->length; $i++){

				$oMensagem = new NFSeSigepMensagemRetorno();
				$oMensagem->Codigo = $oDocument->getValue($aMensagens->item($i), 'Codigo');//codigo do erro
				$oMensagem->Mensagem = $oDocument->getValue($aMensagens->item($i), 'Mensagem');//mensagem de erro
				$oMensagem->Correcao = $oDocument->getValue($aMensagens->item($i), 'Codigo');//correcao

				$return[] = $oMensagem;
			}
		}

		return $return;
	}

	private static function retMsgForaEsperado(){

		$oMensagem = new NFSeSigepMensagemRetorno();
		$oMensagem->Mensagem = "Retorno fora do formato esperado!";//mensagem de erro
		$oMensagem->Correcao = "Verificar XML Retornado!";//correcao

		return $oMensagem;
	}

	private static function retInfNFSe($oCompNfse, $oDocument){

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
		$Tomador = $InfDeclaracaoPrestacaoServico->getElementsByTagName("Tomador")->item(0);

		$IdentificacaoTomador = $Tomador->getElementsByTagName("IdentificacaoTomador")->item(0);

		$EnderecoTomador = $Tomador->getElementsByTagName("Endereco")->item(0);

		$ContatoTomador = $Tomador->getElementsByTagName("Contato")->item(0);


		$oNFSeSigepInfNFSe = new NFSeSigepInfNFSe();
		$oNFSeSigepInfNFSe->Numero = $oDocument->getValue($InfNfse, "Numero");
		$oNFSeSigepInfNFSe->CodigoVerificacao = $oDocument->getValue($InfNfse, "CodigoVerificacao");
		$oNFSeSigepInfNFSe->DataEmissao = $oDocument->getValue($InfNfse, "DataEmissao");
		$oNFSeSigepInfNFSe->StatusNfse = $oDocument->getValue($InfNfse, "StatusNfse");
		$oNFSeSigepInfNFSe->NfseSubstituida = $oDocument->getValue($InfNfse, "NfseSubstituida");
		$oNFSeSigepInfNFSe->OutrasInformacoes = $oDocument->getValue($InfNfse, "OutrasInformacoes");
		$url = $oDocument->getValue($InfNfse, "UrlNfse");
		$oNFSeSigepInfNFSe->Url = ( substr($url, 0, 7) != 'http://' && substr($url, 0, 8) != 'https://' ? 'http://' : '') . $url;
		$oNFSeSigepInfNFSe->ValorCredito = $oDocument->getValue($InfNfse, "ValorCredito");

		$oNFSeSigepInfNFSe->IdentificacaoRps->Numero = $oDocument->getValue($IdentificacaoRps, "Numero");
		$oNFSeSigepInfNFSe->IdentificacaoRps->Tipo = $oDocument->getValue($IdentificacaoRps, "Tipo");
		$oNFSeSigepInfNFSe->DataEmissaoRps = $oDocument->getValue($Rps, "DataEmissao");

		$Valores = $Servico->getElementsByTagName("Valores")->item(0);

		$oNFSeSigepInfNFSe->Servico->Valores->ValorServicos = $oDocument->getValue($Valores, "ValorServicos");
		$oNFSeSigepInfNFSe->Servico->Valores->BaseCalculo = $oDocument->getValue($ValoresNfse, "BaseCalculo");
		$oNFSeSigepInfNFSe->Servico->Valores->Aliquota = $oDocument->getValue($ValoresNfse, "Aliquota");
		$oNFSeSigepInfNFSe->Servico->Valores->ValorIss = $oDocument->getValue($ValoresNfse, "ValorIss");
		$oNFSeSigepInfNFSe->Servico->Valores->ValorLiquidoNfse = $oDocument->getValue($ValoresNfse, "ValorLiquidoNfse");

		$oNFSeSigepInfNFSe->Servico->Valores->ValorDeducoes = $oDocument->getValue($Valores, "ValorDeducoes");
		$oNFSeSigepInfNFSe->Servico->Valores->ValorPis = $oDocument->getValue($Valores, "ValorPis");
		$oNFSeSigepInfNFSe->Servico->Valores->ValorCofins = $oDocument->getValue($Valores, "ValorCofins");
		$oNFSeSigepInfNFSe->Servico->Valores->ValorInss = $oDocument->getValue($Valores, "ValorInss");
		$oNFSeSigepInfNFSe->Servico->Valores->ValorIr = $oDocument->getValue($Valores, "ValorIr");
		$oNFSeSigepInfNFSe->Servico->Valores->ValorCsll = $oDocument->getValue($Valores, "ValorCsll");
		$oNFSeSigepInfNFSe->Servico->Valores->ValorIssRetido = $oDocument->getValue($Valores, "ValorIssRetido");

		$oNFSeSigepInfNFSe->Servico->Valores->OutrasRetencoes = $oDocument->getValue($Valores, "OutrasRetencoes");
		$oNFSeSigepInfNFSe->Servico->Valores->DescontoCondicionado = $oDocument->getValue($Valores, "DescontoCondicionado");
		$oNFSeSigepInfNFSe->Servico->Valores->DescontoIncondicionado = $oDocument->getValue($Valores, "DescontoIncondicionado");

		$oNFSeSigepInfNFSe->Servico->ItemListaServico = $oDocument->getValue($Servico, "ItemListaServico");
		$oNFSeSigepInfNFSe->Servico->CodigoCnae = $oDocument->getValue($Servico, "CodigoCnae");
		$oNFSeSigepInfNFSe->Servico->CodigoTributacaoMunicipio = $oDocument->getValue($Servico, "CodigoTributacaoMunicipio");
		$oNFSeSigepInfNFSe->Servico->Discriminacao = $oDocument->getValue($Servico, "Discriminacao");
		$oNFSeSigepInfNFSe->Servico->CodigoMunicipio = $oDocument->getValue($Servico, "CodigoMunicipio");
		$oNFSeSigepInfNFSe->Servico->ExigibilidadeISS = $oDocument->getValue($Servico, "ExigibilidadeISS");

		if($Prestador->getElementsByTagName("Cnpj")->length == 1)
			$oNFSeSigepInfNFSe->PrestadorServico->Identificacao->setCpfCnpj($oDocument->getValue($Prestador, "Cnpj"));
		else
			$oNFSeSigepInfNFSe->PrestadorServico->Identificacao->setCpfCnpj($oDocument->getValue($Prestador, "Cpf"), 0);

		$oNFSeSigepInfNFSe->PrestadorServico->Identificacao->InscricaoMunicipal = $oDocument->getValue($Prestador, "InscricaoMunicipal");

		$oNFSeSigepInfNFSe->PrestadorServico->Endereco->TipoLogradouro = $oDocument->getValue($EnderecoPrestadorServico, "TipoLogradouro");
		$oNFSeSigepInfNFSe->PrestadorServico->Endereco->Logradouro = $oDocument->getValue($EnderecoPrestadorServico, "Logradouro");
		$oNFSeSigepInfNFSe->PrestadorServico->Endereco->Numero = $oDocument->getValue($EnderecoPrestadorServico, "Numero");
		$oNFSeSigepInfNFSe->PrestadorServico->Endereco->Complemento = $oDocument->getValue($EnderecoPrestadorServico, "Complemento");
		$oNFSeSigepInfNFSe->PrestadorServico->Endereco->Bairro = $oDocument->getValue($EnderecoPrestadorServico, "Bairro");
		$oNFSeSigepInfNFSe->PrestadorServico->Endereco->CodigoMunicipio = $oDocument->getValue($EnderecoPrestadorServico, "CodigoMunicipio");
		$oNFSeSigepInfNFSe->PrestadorServico->Endereco->CodigoPaisEstrangeiro = $oDocument->getValue($EnderecoPrestadorServico, "CodigoPaisEstrangeiro");
		$oNFSeSigepInfNFSe->PrestadorServico->Endereco->EstadoPaisEstrangeiro = $oDocument->getValue($EnderecoPrestadorServico, "EstadoPaisEstrangeiro");
		$oNFSeSigepInfNFSe->PrestadorServico->Endereco->CidadePaisEstrangeiro = $oDocument->getValue($EnderecoPrestadorServico, "CidadePaisEstrangeiro");
		$oNFSeSigepInfNFSe->PrestadorServico->Endereco->Uf = $oDocument->getValue($EnderecoPrestadorServico, "Uf");
		$oNFSeSigepInfNFSe->PrestadorServico->Endereco->Cep = $oDocument->getValue($EnderecoPrestadorServico, "Cep");

		if($IdentificacaoTomador->getElementsByTagName("Cnpj")->length == 1)
			$oNFSeSigepInfNFSe->PrestadorServico->Identificacao->setCpfCnpj($oDocument->getValue($IdentificacaoTomador, "Cnpj"));
		else
			$oNFSeSigepInfNFSe->PrestadorServico->Identificacao->setCpfCnpj($oDocument->getValue($IdentificacaoTomador, "Cpf"), 0);

		$oNFSeSigepInfNFSe->TomadorServico->IdentificacaoTomador->InscricaoMunicipal = $oDocument->getValue($IdentificacaoTomador, "InscricaoMunicipal");

		$oNFSeSigepInfNFSe->TomadorServico->RazaoSocial = $oDocument->getValue($Tomador, "RazaoSocial");

		$oNFSeSigepInfNFSe->TomadorServico->Endereco->TipoLogradouro = $oDocument->getValue($EnderecoTomador, "TipoLogradouro");
		$oNFSeSigepInfNFSe->TomadorServico->Endereco->Logradouro = $oDocument->getValue($EnderecoTomador, "Logradouro");
		$oNFSeSigepInfNFSe->TomadorServico->Endereco->Numero = $oDocument->getValue($EnderecoTomador, "Numero");
		$oNFSeSigepInfNFSe->TomadorServico->Endereco->Complemento = $oDocument->getValue($EnderecoTomador, "Complemento");
		$oNFSeSigepInfNFSe->TomadorServico->Endereco->Bairro = $oDocument->getValue($EnderecoTomador, "Bairro");
		$oNFSeSigepInfNFSe->TomadorServico->Endereco->CodigoMunicipio = $oDocument->getValue($EnderecoTomador, "CodigoMunicipio");
		$oNFSeSigepInfNFSe->TomadorServico->Endereco->CodigoPaisEstrangeiro = $oDocument->getValue($EnderecoTomador, "CodigoPaisEstrangeiro");
		$oNFSeSigepInfNFSe->TomadorServico->Endereco->EstadoPaisEstrangeiro = $oDocument->getValue($EnderecoTomador, "EstadoPaisEstrangeiro");
		$oNFSeSigepInfNFSe->TomadorServico->Endereco->CidadePaisEstrangeiro = $oDocument->getValue($EnderecoTomador, "CidadePaisEstrangeiro");
		$oNFSeSigepInfNFSe->TomadorServico->Endereco->Uf = $oDocument->getValue($EnderecoTomador, "Uf");
		$oNFSeSigepInfNFSe->TomadorServico->Endereco->Cep = $oDocument->getValue($EnderecoTomador, "Cep");

		$oNFSeSigepInfNFSe->TomadorServico->Contato->Ddd = $oDocument->getValue($ContatoTomador, "Ddd");
		$oNFSeSigepInfNFSe->TomadorServico->Contato->Telefone = $oDocument->getValue($ContatoTomador, "Telefone");
		$oNFSeSigepInfNFSe->TomadorServico->Contato->TipoTelefone = $oDocument->getValue($ContatoTomador, "TipoTelefone");
		$oNFSeSigepInfNFSe->TomadorServico->Contato->Email = $oDocument->getValue($ContatoTomador, "Email");

		return $oNFSeSigepInfNFSe;
	}

	/**
	 *
	 * @param NFSeDocument $oDocument
	 * @return array[NFSeSigepInfNFSe]
	 */
	private static function retListNFSe(NFSeDocument $oDocument){
		$xpath = new \DOMXPath($oDocument);

		$nSMain = $oDocument->lookupPrefix(NFSeSigep::XMLNS);

		$ListaNfse = $oDocument->getElementsByTagName("ListaNfse");

		if (is_null($ListaNfse) || $ListaNfse->length != 1)
			return array();

		$ListaNfse = $ListaNfse->item(0);

		$return = array('CompNfse' => array(), 'ListaMensagemAlertaRetorno' => self::retListaMensagem($oDocument, $ListaNfse, 'ListaMensagemAlertaRetorno'));
		$aCompNfse = $ListaNfse->getElementsByTagName('CompNfse');
		for ($i = 0; $i < $aCompNfse->length; $i++){

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

			$return['CompNfse'][] = self::retInfNFSe($CompNfse, $oDocument);
		}

		return $return;
	}

	/**
	 *
	 * @param string $return
	 * @param string $action
	 * @throws \Exception
	 *
	 * @return NFSeDocument
	 */
	public static function getReturn($return, $action, $pathFile = null){

		$oReturn = new NFSeDocument();

		if(!empty($return)){
			$dom = new NFSeDocument();
			$dom->loadXML($return);
			$fault = self::checkForFault($dom);

			if(!empty($fault)){
				$fault;
				$oMensagem = new NFSeSigepMensagemRetorno();
				$oMensagem->Mensagem = $fault;//mensagem de erro

				return array(
					'ListaMensagemRetorno' => array(
						$oMensagem
					)
				);
			}

			switch ($action){

				case "cancelarNfse":
					$oReturn = $dom->getElementsByTagName("CancelarNfseResposta")->item(0);
					$CancelarNfseResposta = new NFSeDocument();
					$CancelarNfseResposta->loadXML($oReturn->nodeValue);

					if(!is_null($pathFile))
						file_put_contents($pathFile, $CancelarNfseResposta->saveXML());

					return self::cancelarNfseResposta($CancelarNfseResposta);
				break;
				case "gerarNfse":

					$oReturn = $dom->getElementsByTagName("GerarNfseRetorno")->item(0);
					$oGerarNfseRetorno = new NFSeDocument();
					$oGerarNfseRetorno->loadXML($oReturn->nodeValue);

					if(!is_null($pathFile))
						file_put_contents($pathFile, $oGerarNfseRetorno->saveXML());

					return self::gerarNfseRetorno($oGerarNfseRetorno);
				break;
				case "enviarLoteRpsSincrono":

					$oReturn = $dom->getElementsByTagName("EnviarLoteRpsSincronoResposta")->item(0);
					$oLoteRpsResposta = new NFSeDocument();
					$oLoteRpsResposta->loadXML($oReturn->nodeValue);

					if(!is_null($pathFile))
						file_put_contents($pathFile, $oLoteRpsResposta->saveXML());

					return self::gerarLoteRpsResposta($oLoteRpsResposta);
				break;
				case "consultarNfseRps":

					$oReturn = $dom->getElementsByTagName("ConsultarNfseRpsResposta")->item(0);
					$oConsultarNfseRpsResposta = new NFSeDocument();
					$oConsultarNfseRpsResposta->loadXML($oReturn->nodeValue);

					if(!is_null($pathFile))
						file_put_contents($pathFile, $oConsultarNfseRpsResposta->saveXML());

					$return = array(
						'ListaMensagemRetorno' => self::retListaMensagem($oConsultarNfseRpsResposta)
					);

					$CompNfse = $oConsultarNfseRpsResposta->getElementsByTagName('CompNfse');

					if ($CompNfse->length == 1)
						$return['CompNfse'] = self::retInfNFSe($CompNfse->item(0), $oConsultarNfseRpsResposta);

					return $return;
				break;
				default:
					throw new \Exception("Retorno nao definido! Action(" . $action . ") Retorno: " . PHP_EOL . $return);
				break;
			}
		}
		else{
			$oMensagem = new NFSeSigepMensagemRetorno();
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
	private static function retCancelamento(NFSeDocument $oCancelarNfseResposta){

		$return = array(
			'NfseCancelamento' => array()
		);

		$RetCancelamento = $oCancelarNfseResposta->getElementsByTagName('RetCancelamento');
		if ($RetCancelamento->length > 0){
			$RetCancelamento = $RetCancelamento->item(0);

			$aNfseCancelamento = $RetCancelamento->getElementsByTagName('NfseCancelamento');
			for ($i = 0; $i < $aNfseCancelamento->length; $i++) {

				$NfseCancelamento = $RetCancelamento->getElementsByTagName('NfseCancelamento')->item($i);

				$Confirmacao = $NfseCancelamento->getElementsByTagName('Confirmacao')->item(0);

				$DataHora = $oCancelarNfseResposta->getValue($Confirmacao, "DataHora");
				$Pedido = $Confirmacao->getElementsByTagName('Pedido')->item(0);


				$InfPedidoCancelamento = $Pedido->getElementsByTagName('InfPedidoCancelamento')->item(0);

				$IdentificacaoNfse = $InfPedidoCancelamento->getElementsByTagName('IdentificacaoNfse')->item(0);

				$oIdentificacaoNfse = new NFSeSigepIdentificacaoNfse();

				$oIdentificacaoNfse->Numero = $oCancelarNfseResposta->getValue($IdentificacaoNfse, "Numero");

				$CpfCnpj = $IdentificacaoNfse->getElementsByTagName('CpfCnpj')->item(0);

				if($CpfCnpj->getElementsByTagName("Cnpj")->length == 1)
					$oIdentificacaoNfse->setCpfCnpj($oCancelarNfseResposta->getValue($CpfCnpj, "Cnpj"));
				else
					$oIdentificacaoNfse->setCpfCnpj($oCancelarNfseResposta->getValue($CpfCnpj, "Cpf"), 0);

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
	private static function cancelarNfseResposta(NFSeDocument $oCancelarNfseResposta){
		if ($oCancelarNfseResposta->getElementsByTagName('CancelarNfseResposta')->length == 1){
			return array(
				'ListaMensagemRetorno' => self::retListaMensagem($oCancelarNfseResposta),
				'RetCancelamento' => self::retCancelamento($oCancelarNfseResposta)
			);
		}
		else{
			return array('ListaMensagemRetorno' => self::retMsgForaEsperado());
		}
	}

	/**
	 *
	 * @param NFSeDocument $oGerarNfseRetorno
	 * @return array
	 */
	private static function gerarNfseRetorno(NFSeDocument $oGerarNfseRetorno){
		if ($oGerarNfseRetorno->getElementsByTagName('GerarNfseResposta')->length == 1){
			return array(
				'ListaMensagemRetorno' => self::retListaMensagem($oGerarNfseRetorno),
				'ListaNfse' => self::retListNFSe($oGerarNfseRetorno)
			);
		}
		else{
			return array('ListaMensagemRetorno' => self::retMsgForaEsperado());
		}
	}

	private static function gerarLoteRpsResposta(NFSeDocument $oDocument){
		if ($oDocument->getElementsByTagName('EnviarLoteRpsSincronoResposta')->length == 1){
			$oEnviarLoteRpsSincronoResposta = $oDocument->getElementsByTagName('EnviarLoteRpsSincronoResposta')->item(0);
			return array(
				'NumeroLote' => $oDocument->getValue($oEnviarLoteRpsSincronoResposta, "NumeroLote"),
				'DataRecebimento' => $oDocument->getValue($oEnviarLoteRpsSincronoResposta, "DataRecebimento"),
				'Protocolo' => $oDocument->getValue($oEnviarLoteRpsSincronoResposta, "Protocolo"),
				'ListaNfse' => self::retListNFSe($oDocument),
				'ListaMensagemRetorno' => self::retListaMensagem($oDocument),
				'ListaMensagemRetornoLote' => self::retListaMensagem($oDocument, null, 'ListaMensagemRetornoLote')
			);
		}
		else{
			return array('ListaMensagemRetorno' => self::retMsgForaEsperado());
		}
	}
}
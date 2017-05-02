<?php
namespace NFSe\issweb;

use NFSe\NFSeReturn;
use NFSe\NFSeDocument;
use PQD\PQDUtil;

class NFSeISSWebReturn extends NFSeReturn{

	/**
	 * @param NFSeDocument $oDocument
	 * @return array[NFSeISSWebMensagemRetorno]
	 */
	private static function retListaMensagem(NFSeDocument $oDocument, $contextNode = null, $listaMensagem = 'ListaMensagemRetorno'){
		$return = array();

		$xpath = new \DOMXPath($oDocument);
		$nSMain = $oDocument->documentElement->lookupPrefix(NFSeISSWeb::XMLNS);

		$ListaMensagemRetorno = $xpath->query($nSMain . ':'  . $listaMensagem, $contextNode);

		if($ListaMensagemRetorno->length == 1){

			$ListaMensagemRetorno = $ListaMensagemRetorno->item(0);

			$aMensagens = $xpath->query($nSMain . ':MensagemRetorno', $ListaMensagemRetorno);

			for ($i = 0; $i< $aMensagens->length; $i++){

				$oMensagem = new NFSeISSWebMensagemRetorno();
				$oMensagem->Codigo = $oDocument->getValue($aMensagens->item($i), 'Codigo');//codigo do erro
				$oMensagem->Mensagem = $oDocument->getValue($aMensagens->item($i), 'Mensagem');//mensagem de erro
				$oMensagem->Correcao = $oDocument->getValue($aMensagens->item($i), 'Codigo');//correcao

				$return[] = $oMensagem;
			}
		}
		/*
		else{
			$MensagemRetorno = $oDocument->getElementsByTagName("MensagemRetorno");
			if($MensagemRetorno->length > 0){

				$MensagemRetorno = $MensagemRetorno->item(0);
				$oMensagem = new NFSeISSWebMensagemRetorno();
				$oMensagem->Codigo = $oDocument->getValue($MensagemRetorno, 'Codigo');//codigo do erro
				$oMensagem->Mensagem = $oDocument->getValue($MensagemRetorno, 'Mensagem');//mensagem de erro
				$oMensagem->Correcao = $oDocument->getValue($MensagemRetorno, 'Correcao');//correcao

				$return[] = $oMensagem;
			}
		}
		*/

		return $return;
	}

	private static function retMsgForaEsperado(){

		$oMensagem = new NFSeISSWebMensagemRetorno();
		$oMensagem->Mensagem = "Retorno fora do formato esperado!";//mensagem de erro
		$oMensagem->Correcao = "Verificar XML Retornado!";//correcao

		return $oMensagem;
	}

	/**
	 *
	 * @param NFSeDocument $oDocument
	 * @return array[NFSeISSWebInfNFSe]
	 */
	private static function retListNFSe(NFSeDocument $oDocument){
		$xpath = new \DOMXPath($oDocument);

		$nSMain = $oDocument->lookupPrefix(NFSeISSWeb::XMLNS);

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

			$Nfse = $CompNfse->getElementsByTagName('Nfse')->item(0);

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


			$oNFSeISSWebInfNFSe = new NFSeISSWebInfNFSe();
			$oNFSeISSWebInfNFSe->Numero = $oDocument->getValue($InfNfse, "Numero");
			$oNFSeISSWebInfNFSe->CodigoVerificacao = $oDocument->getValue($InfNfse, "CodigoVerificacao");
			$oNFSeISSWebInfNFSe->DataEmissao = $oDocument->getValue($InfNfse, "DataEmissao");
			$oNFSeISSWebInfNFSe->StatusNfse = $oDocument->getValue($InfNfse, "StatusNfse");
			$oNFSeISSWebInfNFSe->NfseSubstituida = $oDocument->getValue($InfNfse, "NfseSubstituida");
			$oNFSeISSWebInfNFSe->OutrasInformacoes = $oDocument->getValue($InfNfse, "OutrasInformacoes");
			$oNFSeISSWebInfNFSe->Url = 'http://' . $oDocument->getValue($InfNfse, "UrlNfse");
			$oNFSeISSWebInfNFSe->ValorCredito = $oDocument->getValue($InfNfse, "ValorCredito");

			$oNFSeISSWebInfNFSe->IdentificacaoRps->Numero = $oDocument->getValue($IdentificacaoRps, "Numero");
			$oNFSeISSWebInfNFSe->IdentificacaoRps->Tipo = $oDocument->getValue($IdentificacaoRps, "Tipo");
			$oNFSeISSWebInfNFSe->DataEmissaoRps = $oDocument->getValue($Rps, "DataEmissao");

			$Valores = $Servico->getElementsByTagName("Valores")->item(0);

			$oNFSeISSWebInfNFSe->Servico->Valores->ValorServicos = $oDocument->getValue($Valores, "ValorServicos");
			$oNFSeISSWebInfNFSe->Servico->Valores->BaseCalculo = $oDocument->getValue($ValoresNfse, "BaseCalculo");
			$oNFSeISSWebInfNFSe->Servico->Valores->Aliquota = $oDocument->getValue($ValoresNfse, "Aliquota");
			$oNFSeISSWebInfNFSe->Servico->Valores->ValorIss = $oDocument->getValue($ValoresNfse, "ValorIss");
			$oNFSeISSWebInfNFSe->Servico->Valores->ValorLiquidoNfse = $oDocument->getValue($ValoresNfse, "ValorLiquidoNfse");

			$oNFSeISSWebInfNFSe->Servico->Valores->ValorDeducoes = $oDocument->getValue($Valores, "ValorDeducoes");
			$oNFSeISSWebInfNFSe->Servico->Valores->ValorPis = $oDocument->getValue($Valores, "ValorPis");
			$oNFSeISSWebInfNFSe->Servico->Valores->ValorCofins = $oDocument->getValue($Valores, "ValorCofins");
			$oNFSeISSWebInfNFSe->Servico->Valores->ValorInss = $oDocument->getValue($Valores, "ValorInss");
			$oNFSeISSWebInfNFSe->Servico->Valores->ValorIr = $oDocument->getValue($Valores, "ValorIr");
			$oNFSeISSWebInfNFSe->Servico->Valores->ValorCsll = $oDocument->getValue($Valores, "ValorCsll");
			$oNFSeISSWebInfNFSe->Servico->Valores->ValorIssRetido = $oDocument->getValue($Valores, "ValorIssRetido");

			$oNFSeISSWebInfNFSe->Servico->Valores->OutrasRetencoes = $oDocument->getValue($Valores, "OutrasRetencoes");
			$oNFSeISSWebInfNFSe->Servico->Valores->DescontoCondicionado = $oDocument->getValue($Valores, "DescontoCondicionado");
			$oNFSeISSWebInfNFSe->Servico->Valores->DescontoIncondicionado = $oDocument->getValue($Valores, "DescontoIncondicionado");

			$oNFSeISSWebInfNFSe->Servico->ItemListaServico = $oDocument->getValue($Servico, "ItemListaServico");
			$oNFSeISSWebInfNFSe->Servico->CodigoCnae = $oDocument->getValue($Servico, "CodigoCnae");
			$oNFSeISSWebInfNFSe->Servico->CodigoTributacaoMunicipio = $oDocument->getValue($Servico, "CodigoTributacaoMunicipio");
			$oNFSeISSWebInfNFSe->Servico->Discriminacao = $oDocument->getValue($Servico, "Discriminacao");
			$oNFSeISSWebInfNFSe->Servico->CodigoMunicipio = $oDocument->getValue($Servico, "CodigoMunicipio");
			$oNFSeISSWebInfNFSe->Servico->ExigibilidadeISS = $oDocument->getValue($Servico, "ExigibilidadeISS");

			if($Prestador->getElementsByTagName("Cnpj")->length == 1)
				$oNFSeISSWebInfNFSe->PrestadorServico->Identificacao->setCpfCnpj($oDocument->getValue($Prestador, "Cnpj"));
			else
				$oNFSeISSWebInfNFSe->PrestadorServico->Identificacao->setCpfCnpj($oDocument->getValue($Prestador, "Cpf"), 0);

			$oNFSeISSWebInfNFSe->PrestadorServico->Identificacao->InscricaoMunicipal = $oDocument->getValue($Prestador, "InscricaoMunicipal");

			$oNFSeISSWebInfNFSe->PrestadorServico->Endereco->TipoLogradouro = $oDocument->getValue($EnderecoPrestadorServico, "TipoLogradouro");
			$oNFSeISSWebInfNFSe->PrestadorServico->Endereco->Logradouro = $oDocument->getValue($EnderecoPrestadorServico, "Logradouro");
			$oNFSeISSWebInfNFSe->PrestadorServico->Endereco->Numero = $oDocument->getValue($EnderecoPrestadorServico, "Numero");
			$oNFSeISSWebInfNFSe->PrestadorServico->Endereco->Complemento = $oDocument->getValue($EnderecoPrestadorServico, "Complemento");
			$oNFSeISSWebInfNFSe->PrestadorServico->Endereco->Bairro = $oDocument->getValue($EnderecoPrestadorServico, "Bairro");
			$oNFSeISSWebInfNFSe->PrestadorServico->Endereco->CodigoMunicipio = $oDocument->getValue($EnderecoPrestadorServico, "CodigoMunicipio");
			$oNFSeISSWebInfNFSe->PrestadorServico->Endereco->CodigoPaisEstrangeiro = $oDocument->getValue($EnderecoPrestadorServico, "CodigoPaisEstrangeiro");
			$oNFSeISSWebInfNFSe->PrestadorServico->Endereco->EstadoPaisEstrangeiro = $oDocument->getValue($EnderecoPrestadorServico, "EstadoPaisEstrangeiro");
			$oNFSeISSWebInfNFSe->PrestadorServico->Endereco->CidadePaisEstrangeiro = $oDocument->getValue($EnderecoPrestadorServico, "CidadePaisEstrangeiro");
			$oNFSeISSWebInfNFSe->PrestadorServico->Endereco->Uf = $oDocument->getValue($EnderecoPrestadorServico, "Uf");
			$oNFSeISSWebInfNFSe->PrestadorServico->Endereco->Cep = $oDocument->getValue($EnderecoPrestadorServico, "Cep");

			if($IdentificacaoTomador->getElementsByTagName("Cnpj")->length == 1)
				$oNFSeISSWebInfNFSe->PrestadorServico->Identificacao->setCpfCnpj($oDocument->getValue($IdentificacaoTomador, "Cnpj"));
			else
				$oNFSeISSWebInfNFSe->PrestadorServico->Identificacao->setCpfCnpj($oDocument->getValue($IdentificacaoTomador, "Cpf"), 0);

			$oNFSeISSWebInfNFSe->TomadorServico->IdentificacaoTomador->InscricaoMunicipal = $oDocument->getValue($IdentificacaoTomador, "InscricaoMunicipal");

			$oNFSeISSWebInfNFSe->TomadorServico->RazaoSocial = $oDocument->getValue($Tomador, "RazaoSocial");

			$oNFSeISSWebInfNFSe->TomadorServico->Endereco->TipoLogradouro = $oDocument->getValue($EnderecoTomador, "TipoLogradouro");
			$oNFSeISSWebInfNFSe->TomadorServico->Endereco->Logradouro = $oDocument->getValue($EnderecoTomador, "Logradouro");
			$oNFSeISSWebInfNFSe->TomadorServico->Endereco->Numero = $oDocument->getValue($EnderecoTomador, "Numero");
			$oNFSeISSWebInfNFSe->TomadorServico->Endereco->Complemento = $oDocument->getValue($EnderecoTomador, "Complemento");
			$oNFSeISSWebInfNFSe->TomadorServico->Endereco->Bairro = $oDocument->getValue($EnderecoTomador, "Bairro");
			$oNFSeISSWebInfNFSe->TomadorServico->Endereco->CodigoMunicipio = $oDocument->getValue($EnderecoTomador, "CodigoMunicipio");
			$oNFSeISSWebInfNFSe->TomadorServico->Endereco->CodigoPaisEstrangeiro = $oDocument->getValue($EnderecoTomador, "CodigoPaisEstrangeiro");
			$oNFSeISSWebInfNFSe->TomadorServico->Endereco->EstadoPaisEstrangeiro = $oDocument->getValue($EnderecoTomador, "EstadoPaisEstrangeiro");
			$oNFSeISSWebInfNFSe->TomadorServico->Endereco->CidadePaisEstrangeiro = $oDocument->getValue($EnderecoTomador, "CidadePaisEstrangeiro");
			$oNFSeISSWebInfNFSe->TomadorServico->Endereco->Uf = $oDocument->getValue($EnderecoTomador, "Uf");
			$oNFSeISSWebInfNFSe->TomadorServico->Endereco->Cep = $oDocument->getValue($EnderecoTomador, "Cep");

			$oNFSeISSWebInfNFSe->TomadorServico->Contato->Ddd = $oDocument->getValue($ContatoTomador, "Ddd");
			$oNFSeISSWebInfNFSe->TomadorServico->Contato->Telefone = $oDocument->getValue($ContatoTomador, "Telefone");
			$oNFSeISSWebInfNFSe->TomadorServico->Contato->TipoTelefone = $oDocument->getValue($ContatoTomador, "TipoTelefone");
			$oNFSeISSWebInfNFSe->TomadorServico->Contato->Email = $oDocument->getValue($ContatoTomador, "Email");

			$return['CompNfse'][] = $oNFSeISSWebInfNFSe;
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
				$oReturn->createElement("fault", $fault);
				return $oReturn;
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
				default:
					throw new \Exception("Retorno nao definido! Action(" . $action . ") Retorno: " . PHP_EOL . $return);
				break;
			}
		}
		else
			$oReturn->createElement("fault", $fault);

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

				$oIdentificacaoNfse = new NFSeISSWebIdentificacaoNfse();

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
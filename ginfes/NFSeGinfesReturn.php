<?php
namespace NFSe\ginfes;

use NFSe\NFSeReturn;
use NFSe\NFSeDocument;
use PQD\PQDUtil;
use modulos\util\util;

class NFSeGinfesReturn extends NFSeReturn{

	/**
	 * @param NFSeDocument $oDocument
	 * @return array[NFSeGinfesMensagemRetorno]
	 */
	private static function retListaMensagem(NFSeDocument $oDocument){
		$return = array();

		$ListaMensagemRetorno = $oDocument->getElementsByTagName("ListaMensagemRetorno");
		if($ListaMensagemRetorno->length > 0){
			for ($i = 0; $i< $ListaMensagemRetorno->item(0)->childNodes->length; $i++){
				$mensagemRetorno = $ListaMensagemRetorno->item(0)->childNodes->item($i);

				$oMensagem = new NFSeGinfesMensagemRetorno();
				$oMensagem->Codigo = PQDUtil::utf8_decode($mensagemRetorno->childNodes->item(0)->nodeValue);//codigo do erro
				$oMensagem->Mensagem = PQDUtil::utf8_decode($mensagemRetorno->childNodes->item(1)->nodeValue);//mensagem de erro
				$oMensagem->Correcao = PQDUtil::utf8_decode($mensagemRetorno->childNodes->item(2)->nodeValue);//correcao

				$return[] = $oMensagem;
			}
		}
		else{
			$MensagemRetorno = $oDocument->getElementsByTagName("MensagemRetorno");
			if($MensagemRetorno->length > 0){

				$MensagemRetorno = $MensagemRetorno->item(0);
				$oMensagem = new NFSeGinfesMensagemRetorno();
				$oMensagem->Codigo = PQDUtil::utf8_decode($MensagemRetorno->childNodes->item(0)->nodeValue);//codigo do erro
				$oMensagem->Mensagem = PQDUtil::utf8_decode($MensagemRetorno->childNodes->item(1)->nodeValue);//mensagem de erro
				$oMensagem->Correcao = PQDUtil::utf8_decode($MensagemRetorno->childNodes->item(2)->nodeValue);//correcao

				$return[] = $oMensagem;
			}
		}

		return $return;
	}

	private static function retMsgForaEsperado(){

		$oMensagem = new NFSeGinfesMensagemRetorno();
		$oMensagem->Mensagem = "Retorno fora do formato esperado!";//mensagem de erro
		$oMensagem->Correcao = "Verificar XML Retornado!";//correcao

		return $oMensagem;
	}

	/**
	 *
	 * @param NFSeDocument $oDocument
	 * @return array[NFSeGinfesInfNFSe]
	 */
	private static function retListNFSe(NFSeDocument $oDocument, $nsPrefix = NFSeGinfes::XMLNS_CONS_LT_RPS_RES){
		$xpath = new \DOMXPath($oDocument);

		$nSTp = $oDocument->documentElement->lookupPrefix(NFSeGinfes::XMLNS_TIPOS);
		$nSMain = $oDocument->documentElement->lookupPrefix($nsPrefix);

		if(NFSeGinfes::XMLNS_CONS_NFSE_RPS_RES == $nsPrefix)
			$ListaNfse = $oDocument->getElementsByTagName("ConsultarNfseRpsResposta")->item(0);
		else
			$ListaNfse = $oDocument->getElementsByTagNameNS($nsPrefix, "ListaNfse")->item(0);

		$return = array();
		for ($i = 0; $i < $ListaNfse->childNodes->length; $i++){

			$CompNfse = $ListaNfse->childNodes->item($i);
			$Nfse = $CompNfse->childNodes->item($i);
			$InfNfse = $Nfse->childNodes->item(0);

			$oNFSeGinfesInfNFSe = new NFSeGinfesInfNFSe();
			$oNFSeGinfesInfNFSe->Numero = $InfNfse->childNodes->item(0)->nodeValue;
			$oNFSeGinfesInfNFSe->CodigoVerificacao = $InfNfse->childNodes->item(1)->nodeValue;
			$oNFSeGinfesInfNFSe->DataEmissao = $InfNfse->childNodes->item(2)->nodeValue;

			$index = 3;
			if (NFSeGinfes::XMLNS_CONS_LT_RPS_RES == $nsPrefix || NFSeGinfes::XMLNS_CONS_NFSE_RPS_RES == $nsPrefix){
				$oNFSeGinfesInfNFSe->IdentificacaoRps->Numero = $InfNfse->childNodes->item($index)->childNodes->item(0)->nodeValue;
				$oNFSeGinfesInfNFSe->IdentificacaoRps->Serie = $InfNfse->childNodes->item($index)->childNodes->item(1)->nodeValue;
				$oNFSeGinfesInfNFSe->IdentificacaoRps->Tipo = $InfNfse->childNodes->item($index)->childNodes->item(2)->nodeValue;

				$index++;

				$oNFSeGinfesInfNFSe->DataEmissaoRps = $InfNfse->childNodes->item($index++)->nodeValue;
			}

			$oNFSeGinfesInfNFSe->NaturezaOperacao = $InfNfse->childNodes->item($index++)->nodeValue;

			$oNFSeGinfesInfNFSe->RegimeEspecialTributacao = $InfNfse->childNodes->item($index++)->nodeValue;

			$oNFSeGinfesInfNFSe->OptanteSimplesNacional = $InfNfse->childNodes->item($index++)->nodeValue;

			$oNFSeGinfesInfNFSe->IncentivadorCultural = $InfNfse->childNodes->item($index++)->nodeValue;

			$oNFSeGinfesInfNFSe->Competencia = $InfNfse->childNodes->item($index++)->nodeValue;

			$NfseSubstituida = $xpath->query($nSTp . ':NfseSubstituida', $InfNfse)->item(0);

			if(!is_null($NfseSubstituida) && is_object($NfseSubstituida))
				$oNFSeGinfesInfNFSe->NfseSubstituida = $NfseSubstituida->childNodes->item(0)->nodeValue;

			$Servico = $xpath->query($nSTp . ':Servico', $InfNfse)->item(0);
			$Valores = $Servico->childNodes->item(0);

			$oNFSeGinfesInfNFSe->Servico->Valores->ValorServicos = $Valores->childNodes->item(0)->nodeValue;
			$oNFSeGinfesInfNFSe->Servico->Valores->ValorDeducoes = $Valores->childNodes->item(1)->nodeValue;
			$oNFSeGinfesInfNFSe->Servico->Valores->ValorPis = $Valores->childNodes->item(2)->nodeValue;
			$oNFSeGinfesInfNFSe->Servico->Valores->ValorCofins = $Valores->childNodes->item(3)->nodeValue;
			$oNFSeGinfesInfNFSe->Servico->Valores->ValorInss = $Valores->childNodes->item(4)->nodeValue;
			$oNFSeGinfesInfNFSe->Servico->Valores->ValorIr = $Valores->childNodes->item(5)->nodeValue;
			$oNFSeGinfesInfNFSe->Servico->Valores->ValorCsll = $Valores->childNodes->item(6)->nodeValue;
			$oNFSeGinfesInfNFSe->Servico->Valores->IssRetido = $Valores->childNodes->item(7)->nodeValue;
			$oNFSeGinfesInfNFSe->Servico->Valores->ValorIss = $Valores->childNodes->item(8)->nodeValue;
			$oNFSeGinfesInfNFSe->Servico->Valores->ValorIssRetido = $Valores->childNodes->item(9)->nodeValue;
			$oNFSeGinfesInfNFSe->Servico->Valores->OutrasRetencoes = $Valores->childNodes->item(10)->nodeValue;
			$oNFSeGinfesInfNFSe->Servico->Valores->BaseCalculo = $Valores->childNodes->item(11)->nodeValue;
			$oNFSeGinfesInfNFSe->Servico->Valores->Aliquota = $Valores->childNodes->item(12)->nodeValue;
			$oNFSeGinfesInfNFSe->Servico->Valores->ValorLiquidoNfse = $Valores->childNodes->item(13)->nodeValue;
			$oNFSeGinfesInfNFSe->Servico->Valores->DescontoCondicionado = $Valores->childNodes->item(14)->nodeValue;

			$oNFSeGinfesInfNFSe->Servico->ItemListaServico = $Servico->childNodes->item(1)->nodeValue;
			$oNFSeGinfesInfNFSe->Servico->CodigoTributacaoMunicipio = $Servico->childNodes->item(2)->nodeValue;
			$oNFSeGinfesInfNFSe->Servico->Discriminacao = $Servico->childNodes->item(3)->nodeValue;
			$oNFSeGinfesInfNFSe->Servico->CodigoMunicipio = $Servico->childNodes->item(4)->nodeValue;
			$oNFSeGinfesInfNFSe->ValorCredito = $xpath->query($nSTp . ':ValorCredito', $InfNfse)->item(0)->nodeValue;

			$PrestadorServico = $xpath->query($nSTp . ':PrestadorServico', $InfNfse)->item(0);
			$IdentificacaoPrestador = $PrestadorServico->childNodes->item(0);

			if($IdentificacaoPrestador->childNodes->item(0)->tagName == $nSTp . ":Cnpj")
				$oNFSeGinfesInfNFSe->PrestadorServico->Identificacao->setCpfCnpj($IdentificacaoPrestador->childNodes->item(0)->nodeValue);
			else
				$oNFSeGinfesInfNFSe->PrestadorServico->Identificacao->setCpfCnpj($IdentificacaoPrestador->childNodes->item(0)->nodeValue, 0);

			$oNFSeGinfesInfNFSe->PrestadorServico->Identificacao->InscricaoMunicipal = $IdentificacaoPrestador->childNodes->item(1)->nodeValue;

			$oNFSeGinfesInfNFSe->PrestadorServico->RazaoSocial = $PrestadorServico->childNodes->item(1)->nodeValue;

			$oNFSeGinfesInfNFSe->PrestadorServico->Endereco->Endereco = $PrestadorServico->childNodes->item(2)->childNodes->item(0)->nodeValue;
			$oNFSeGinfesInfNFSe->PrestadorServico->Endereco->Numero = $PrestadorServico->childNodes->item(2)->childNodes->item(1)->nodeValue;
			$oNFSeGinfesInfNFSe->PrestadorServico->Endereco->Complemento = $PrestadorServico->childNodes->item(2)->childNodes->item(2)->nodeValue;
			$oNFSeGinfesInfNFSe->PrestadorServico->Endereco->Bairro = $PrestadorServico->childNodes->item(2)->childNodes->item(3)->nodeValue;
			$oNFSeGinfesInfNFSe->PrestadorServico->Endereco->CodigoMunicipio = $PrestadorServico->childNodes->item(2)->childNodes->item(4)->nodeValue;
			$oNFSeGinfesInfNFSe->PrestadorServico->Endereco->Uf = $PrestadorServico->childNodes->item(2)->childNodes->item(5)->nodeValue;
			$oNFSeGinfesInfNFSe->PrestadorServico->Endereco->Cep = $PrestadorServico->childNodes->item(2)->childNodes->item(6)->nodeValue;


			$oNFSeGinfesInfNFSe->PrestadorServico->Contato->Telefone = $PrestadorServico->childNodes->item(3)->childNodes->item(0)->nodeValue;
			$oNFSeGinfesInfNFSe->PrestadorServico->Contato->Email = $PrestadorServico->childNodes->item(3)->childNodes->item(1)->nodeValue;

			$TomadorServico = $xpath->query($nSTp . ':TomadorServico', $InfNfse)->item(0);

			$IdentificacaoTomador = $PrestadorServico->childNodes->item(0);

			if($IdentificacaoTomador->childNodes->item(0)->tagName == $nSTp . ":Cnpj")
				$oNFSeGinfesInfNFSe->TomadorServico->IdentificacaoTomador->setCpfCnpj($IdentificacaoTomador->childNodes->item(0)->nodeValue);
			else
				$oNFSeGinfesInfNFSe->TomadorServico->IdentificacaoTomador->setCpfCnpj($IdentificacaoTomador->childNodes->item(0)->nodeValue, 0);

			$oNFSeGinfesInfNFSe->TomadorServico->IdentificacaoTomador->InscricaoMunicipal = $IdentificacaoTomador->childNodes->item(1)->nodeValue;

			$oNFSeGinfesInfNFSe->TomadorServico->RazaoSocial = $TomadorServico->childNodes->item(1)->nodeValue;

			$oNFSeGinfesInfNFSe->TomadorServico->Endereco->Endereco = $TomadorServico->childNodes->item(2)->childNodes->item(0)->nodeValue;
			$oNFSeGinfesInfNFSe->TomadorServico->Endereco->Numero = $TomadorServico->childNodes->item(2)->childNodes->item(1)->nodeValue;
			$oNFSeGinfesInfNFSe->TomadorServico->Endereco->Complemento = $TomadorServico->childNodes->item(2)->childNodes->item(2)->nodeValue;
			$oNFSeGinfesInfNFSe->TomadorServico->Endereco->Bairro = $TomadorServico->childNodes->item(2)->childNodes->item(3)->nodeValue;
			$oNFSeGinfesInfNFSe->TomadorServico->Endereco->CodigoMunicipio = $TomadorServico->childNodes->item(2)->childNodes->item(4)->nodeValue;
			$oNFSeGinfesInfNFSe->TomadorServico->Endereco->Uf = $TomadorServico->childNodes->item(2)->childNodes->item(5)->nodeValue;
			$oNFSeGinfesInfNFSe->TomadorServico->Endereco->Cep = $TomadorServico->childNodes->item(2)->childNodes->item(6)->nodeValue;

			$Contato = $xpath->query($nSTp . ':Contato', $TomadorServico)->item(0);

			if(!is_null($Contato) && is_object($Contato)){
				$Telefone = $xpath->query($nSTp . ':Telefone', $Contato)->item(0);
				if(!is_null($Telefone) && is_object($Telefone))
					$oNFSeGinfesInfNFSe->TomadorServico->Contato->Telefone = $Telefone->childNodes->item(0)->nodeValue;

					$Email = $xpath->query($nSTp . ':Email', $Contato)->item(0);
					if(!is_null($Email) && is_object($Email))
						$oNFSeGinfesInfNFSe->TomadorServico->Contato->Email = $Email->childNodes->item(0)->nodeValue;
			}

			$return[] = $oNFSeGinfesInfNFSe;
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

				case "RecepcionarLoteRpsV3":
					$oConsulta = $dom->getElementsByTagName("RecepcionarLoteRpsV3Response")->item(0);
					$domReturn = $oConsulta->getElementsByTagName("return");

					$oEnviarLoteResposta = new NFSeGinfesEnviarLoteRpsResposta();
					if($domReturn->length == 1 && !empty($domReturn->item(0)->nodeValue)){
						$oReturn->loadXML($domReturn->item(0)->nodeValue);

						$oEnviarLoteResposta->ListaMensagemRetorno = self::retListaMensagem($oReturn);
						if (count($oEnviarLoteResposta->ListaMensagemRetorno) == 0){

							if(!is_null($pathFile))
								file_put_contents($pathFile, $oReturn->saveXML());

							$EnviarLoteRpsResposta = $oReturn->childNodes->item(0);
							$oEnviarLoteResposta->NumeroLote = $EnviarLoteRpsResposta->childNodes->item(0)->nodeValue;//
							$oEnviarLoteResposta->DataRecebimento = $EnviarLoteRpsResposta->childNodes->item(1)->nodeValue;//
							$oEnviarLoteResposta->Protocolo = $EnviarLoteRpsResposta->childNodes->item(2)->nodeValue;//
						}
					}
					else
						$oEnviarLoteResposta->ListaMensagemRetorno[] = self::retMsgForaEsperado();

					return $oEnviarLoteResposta;
				break;
				case "ConsultarNfsePorRpsV3":
					$oConsulta = $dom->getElementsByTagName("ConsultarNfsePorRpsV3Response")->item(0);
					$domReturn = $oConsulta->getElementsByTagName("return");
					if($domReturn->length == 1 && !empty($domReturn->item(0)->nodeValue)){
						$oReturn->loadXML($domReturn->item(0)->nodeValue);
						$ListaMensagemRetorno = self::retListaMensagem($oReturn);

						if (count($ListaMensagemRetorno) == 0){

							if(!is_null($pathFile))
								file_put_contents($pathFile, $oReturn->saveXML());

							$aNFSe = self::retListNFSe($oReturn, NFSeGinfes::XMLNS_CONS_NFSE_RPS_RES);
							return $aNFSe[0];
						}
						else
							return $ListaMensagemRetorno;
					}
					else
						return array(self::retMsgForaEsperado());
				break;
				case "ConsultarLoteRpsV3":

					$oConsulta = $dom->getElementsByTagName("ConsultarLoteRpsV3Response")->item(0);
					$domReturn = $oConsulta->getElementsByTagName("return");

					if($domReturn->length == 1 && !empty($domReturn->item(0)->nodeValue)){
						$oReturn->loadXML($domReturn->item(0)->nodeValue);

						$ListaMensagemRetorno = self::retListaMensagem($oReturn);
						if (count($ListaMensagemRetorno) == 0){

							if(!is_null($pathFile))
								file_put_contents($pathFile, $oReturn->saveXML());

							return self::retListNFSe($oReturn);
						}
						else
							return $ListaMensagemRetorno;
					}
					else
						return array(self::retMsgForaEsperado());
				break;
				case "ConsultarNfseV3":

					$oConsulta = $dom->getElementsByTagName("ConsultarNfseV3Response")->item(0);
					$domReturn = $oConsulta->getElementsByTagName("return");

					$oReturn = new NFSeDocument();
					if($domReturn->length == 1 && !empty($domReturn->item(0)->nodeValue)){
						$oReturn->loadXML($domReturn->item(0)->nodeValue);

						$ListaMensagemRetorno = self::retListaMensagem($oReturn);
						if (count($ListaMensagemRetorno) == 0){

							if(!is_null($pathFile))
								file_put_contents($pathFile, $oReturn->saveXML());

							return self::retListNFSe($oReturn, NFSeGinfes::XMLNS_CONS_NFSE_RES);
						}
						else
							return $ListaMensagemRetorno;
					}
					else
						return array(self::retMsgForaEsperado());
				break;
				case "CancelarNfse":

					$oCancelarNfse = $dom->getElementsByTagName("CancelarNfseResponse")->item(0);
					$domReturn = $oCancelarNfse->getElementsByTagName("return");
					$oCancelarResposta = new NFSeGinfesCancelarResposta();

					if($domReturn->length == 1 && !empty($domReturn->item(0)->nodeValue)){
						$oReturn->loadXML($domReturn->item(0)->nodeValue);
						$oCancelarResposta->ListaMensagemRetorno = self::retListaMensagem($oReturn);

						if (count($oCancelarResposta->ListaMensagemRetorno) == 0){

							if(!is_null($pathFile))
								file_put_contents($pathFile, $oReturn->saveXML());

							$oCancelarResposta->Sucesso = $oReturn->childNodes->item(0)->childNodes->item(0)->nodeValue;
							$oCancelarResposta->DataHora = $oReturn->childNodes->item(0)->childNodes->item(1)->nodeValue;
						}
					}
					else
						$oCancelarResposta->ListaMensagemRetorno[] = self::retMsgForaEsperado();

					return $oCancelarResposta;
				break;
				default:
					throw new \Exception("Retorno não definido! Action(" . $action . ") Retorno: " . PHP_EOL . $return);
				break;
			}
		}
		else
			$oReturn->createElement("fault", $fault);

		return $oReturn;
	}
}
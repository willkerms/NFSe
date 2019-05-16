<?php
require_once 'TesteCase.php';

use NFSe\sigep\NFSeSigep;
use NFSe\sigep\NFSeSigepInfRps;
use NFSe\sigep\NFSeSigepReturn;

class TesteSigep extends TesteCase{

	public static function main(){
		parent::main();

		self::testeGerarNFSe(array(
			'cnpj' => '07393407000175',
			'inscMunicipal' => '1001322',

			'pfx' => 'D:\Dropbox\Matriz Tecnologia\projetos\Incinera\certificados incinera\2019\07393407000175.pfx',
			'pwdPFX' => "incinera2017",

			'sigep_usuario' => '07393407000175-1001322',
			'sigep_senha' => '29ipwrkx',
			'sigep_chavePrivada' => '2d7dca77bd693bf4',

			'pathCert' => self::$path . '/logs'
		));
	}

	private static function testeGerarNFSe($aConfig){
		$oSigep = new NFSeSigep($aConfig);

		$oRPS = new NFSeSigepInfRps();

		$oNFSe = json_decode(file_get_contents(self::$path . '/nfse.json'));

		//$oRPS->DataEmissao = preg_replace('/\..*/', "", str_replace(' ', 'T', $oNFSe->emissaoRPS));
		$oRPS->DataEmissao = preg_replace('/\..*/', "", str_replace(' ', 'T', date('Y-m-d H:i:s')));
		$oRPS->NaturezaOperacao = $oNFSe->natOperacao;
		$oRPS->RegimeEspecialTributacao = $oNFSe->regimeEspTrib;
		$oRPS->OptanteSimplesNacional = $oNFSe->simplesNacional;
		$oRPS->IncentivoFiscal = $oNFSe->icentivadorCultural;
		switch ($oNFSe->statusRPS){
			case '1':
				$oRPS->Status = 'CO';
			break;
			case '2':
				$oRPS->Status = 'CA';
			break;
		}

		$oRPS->IdentificacaoRps->Numero = $oNFSe->rpsNum;

		switch ($oNFSe->rpsTipo){
			case '1':
				$oRPS->IdentificacaoRps->Tipo = 'R1';
			break;
			case '2':
				$oRPS->IdentificacaoRps->Tipo = 'R2';
			break;
			case '3':
				$oRPS->IdentificacaoRps->Tipo = 'R3';
			break;
		}

		if(!is_null($oNFSe->idNfseSubstituida)){
			$oRPS->RpsSubstituido->Numero = $oNFSe->rpsNumSubstituto;
			switch ($oNFSe->rpsTipoSubstituto){
				case '1':
					$oRPS->RpsSubstituido->Tipo = 'R1';
				break;
				case '2':
					$oRPS->RpsSubstituido->Tipo = 'R2';
				break;
				case '3':
					$oRPS->RpsSubstituido->Tipo = 'R3';
				break;
			}
		}

		$oRPS->Servico->ItemListaServico = $oNFSe->servico->codTpServico;
		$oRPS->Servico->CodigoCnae = $oNFSe->codigoCNAE;
		$oRPS->Servico->CodigoTributacaoMunicipio = $oNFSe->codTribMunicipio;
		$oRPS->Servico->Discriminacao = utf8_encode(preg_replace("/&([a-z])[a-z]+;/i","$1", strtoupper(htmlentities(str_replace("'", " ", $oNFSe->discriminacao), ENT_QUOTES, "ISO-8859-1"))));
		$oRPS->Servico->CodigoMunicipio = $oNFSe->codIBGEMunicipio;

		$oRPS->Servico->Valores->ValorServicos = $oNFSe->vlrServico;
		$oRPS->Servico->Valores->ValorDeducoes = $oNFSe->vlrDeducoes;
		$oRPS->Servico->Valores->ValorPis = $oNFSe->vlrPis;
		$oRPS->Servico->Valores->ValorCofins = $oNFSe->vlrConfins;
		$oRPS->Servico->Valores->ValorInss = $oNFSe->vlrInss;
		$oRPS->Servico->Valores->ValorIr = $oNFSe->vlrIr;
		$oRPS->Servico->Valores->ValorCsll = $oNFSe->vlrCsll;
		$oRPS->Servico->Valores->ValorIss = $oNFSe->vlrIss;

		if($oNFSe->issRetido == 1)
			$oRPS->Servico->Valores->ValorIssRetido = $oNFSe->vlrIssRetido;

		$oRPS->Servico->Valores->OutrasRetencoes = $oNFSe->vlrOutrasRetencoes;
		$oRPS->Servico->Valores->BaseCalculo = $oNFSe->baseCalculo;
		$oRPS->Servico->Valores->Aliquota = $oNFSe->aliquota * 100;
		$oRPS->Servico->Valores->ValorLiquidoNfse = $oNFSe->vlrLiquidoNfse;//Sistema calcula sozinho
		$oRPS->Servico->Valores->DescontoCondicionado = $oNFSe->vlrDescCondicionado;
		$oRPS->Servico->Valores->DescontoIncondicionado = $oNFSe->vlrDescIncondicionado;

		$oRPS->Tomador->IdentificacaoTomador->InscricaoMunicipal = $oNFSe->inscMunicipalTomador;
		$oRPS->Tomador->IdentificacaoTomador->setCpfCnpj($oNFSe->cpfCnpjTomador, $oNFSe->tpTomador);
		$oRPS->Tomador->RazaoSocial = utf8_encode(preg_replace("/&([a-z])[a-z]+;/i","$1", strtoupper(htmlentities(str_replace("'", " ", $oNFSe->razaoSocialTomador), ENT_QUOTES, "ISO-8859-1"))));

		$aEndereco = preg_split("[ ]", $oNFSe->enderecoTomador);
		if (count($aEndereco) > 0){
			$tipoLogradouro = strtoupper($aEndereco[0]);

			switch ($tipoLogradouro){
				case 'ÁREA':
					//case 'ÁREA ESPECIAL':
				case 'AVENIDA':
				case 'BLOCO':
				case 'CHÁCARA':
				case 'COLÔNIA':
				case 'CONDOMÍNIO':
					//case 'CONDOMÍNIO RESIDENCIAL':
				case 'CONJUNTO':
					//case 'ENTRE QUADRA':
				case 'ESTAÇÃO':
				case 'ESTRADA':
				case 'MÓDULO':
				case 'NÚCLEO':
					//case 'NÚCLEO RURAL':
				case 'PRAÇA':
				case 'QUADRA':
				case 'RESIDENCIAL':
				case 'RODOVIA':
				case 'RUA':
				case 'SETOR':
				case 'TRECHO':
				case 'VIA':
				case 'VILA':
					$oRPS->Tomador->Endereco->TipoLogradouro = utf8_encode(preg_replace("/&([a-z])[a-z]+;/i","$1", strtoupper(htmlentities(str_replace("'", " ", $tipoLogradouro), ENT_QUOTES, "ISO-8859-1"))));
					array_shift($aEndereco);
					$oRPS->Tomador->Endereco->Logradouro = utf8_encode(preg_replace("/&([a-z])[a-z]+;/i","$1", strtoupper(htmlentities(str_replace("'", " ", join(" ", $aEndereco)), ENT_QUOTES, "ISO-8859-1"))));
					break;
				default:
					$oRPS->Tomador->Endereco->Logradouro = utf8_encode(preg_replace("/&([a-z])[a-z]+;/i","$1", strtoupper(htmlentities(str_replace("'", " ", join(" ", $aEndereco)), ENT_QUOTES, "ISO-8859-1"))));
			}
		}

		$oRPS->Tomador->Endereco->Numero = utf8_encode(preg_replace("/&([a-z])[a-z]+;/i","$1", strtoupper(htmlentities(str_replace("'", " ", $oNFSe->numeroEndTomador), ENT_QUOTES, "ISO-8859-1"))));
		$oRPS->Tomador->Endereco->Complemento = utf8_encode(preg_replace("/&([a-z])[a-z]+;/i","$1", strtoupper(htmlentities(str_replace("'", " ", $oNFSe->complementoTomador), ENT_QUOTES, "ISO-8859-1"))));
		$oRPS->Tomador->Endereco->Bairro = utf8_encode(preg_replace("/&([a-z])[a-z]+;/i","$1", strtoupper(htmlentities(str_replace("'", " ", $oNFSe->bairroTomador), ENT_QUOTES, "ISO-8859-1"))));
		$oRPS->Tomador->Endereco->CodigoMunicipio = $oNFSe->codIBGEMunTomador;
		$oRPS->Tomador->Endereco->Uf = $oNFSe->ufTomador;
		$oRPS->Tomador->Endereco->Cep = $oNFSe->cepTomador;

		if (!is_null($oNFSe->telefoneTomador)){
			$oRPS->Tomador->Contato->Ddd = '0' . substr($oNFSe->telefoneTomador, 0, 2);
			$oRPS->Tomador->Contato->Telefone = substr($oNFSe->telefoneTomador, 2);
		}
		$oRPS->Tomador->Contato->email = $oNFSe->emailTomador;

		$oRPS->Prestador->Cnpj = $aConfig['cnpj'];
		$oRPS->Prestador->InscricaoMunicipal = $aConfig['inscMunicipal'];

		$oSigep->gerarNfse($oRPS);
	}
}
TesteSigep::main();
<?php
namespace NFSe\generico;

use NFSe\NFSeReturn;
use NFSe\NFSeDocument;
use PQD\PQDUtil;
use NFSe\generico\nfseNacional\NFSeGenericoInfNFSe as NFSeGenericoInfNFSeNacional;

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

		$aTags = $this->oGenerico->getConfig('tagMensagensRetorno', []);
		$tagListaMensagem = PQDUtil::retDefault($aTags, 'tagListaMensagens', $listaMensagem);
		$tagMensagem = PQDUtil::retDefault($aTags, 'tagMensagem', 'MensagemRetorno');

		$ListaMensagemRetorno = $oDocument->documentElement->getElementsByTagName($tagListaMensagem);

		if($ListaMensagemRetorno->length == 1) {

			$ListaMensagemRetorno = $ListaMensagemRetorno->item(0);

			$aMensagens = $ListaMensagemRetorno->getElementsByTagName($tagMensagem);

			for ($i = 0; $i< $aMensagens->length; $i++){

				$oMensagem = new NFSeGenericoMensagemRetorno();
				$oMensagem->Codigo = $oDocument->getValue($aMensagens->item($i), 'Codigo');//codigo do erro
				$oMensagem->Mensagem = $oDocument->getValue($aMensagens->item($i), 'Mensagem');//mensagem de erro
				$oMensagem->Correcao = $oDocument->getValue($aMensagens->item($i), 'Correcao');//correcao

				if(!empty($oDocument->getValue($aMensagens->item($i), 'IdDPS')))
					$oMensagem->IdDPS = $oDocument->getValue($aMensagens->item($i), 'IdDPS');

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

	private function retInfNFSeNacional(\DOMElement $oCompNfse, NFSeDocument $oDocument) {

		$NFSe       = $oCompNfse->getElementsByTagName('NFSe')->item(0);
		$infNFSe    = $NFSe->getElementsByTagName('infNFSe')->item(0);
		$emit       = $infNFSe->getElementsByTagName('emit')->item(0);
		$enderNac   = !is_null($emit) ? $emit->getElementsByTagName('enderNac')->item(0) : null;
		$valoresNFSe = $infNFSe->getElementsByTagName('valores')->item(0);

		$DPS        = $infNFSe->getElementsByTagName('DPS')->item(0);
		$infDPS     = !is_null($DPS) ? $DPS->getElementsByTagName('infDPS')->item(0) : null;
		$prest      = !is_null($infDPS) ? $infDPS->getElementsByTagName('prest')->item(0) : null;
		$regTrib    = !is_null($prest) ? $prest->getElementsByTagName('regTrib')->item(0) : null;
		$toma       = !is_null($infDPS) ? $infDPS->getElementsByTagName('toma')->item(0) : null;
		$endToma    = !is_null($toma) ? $toma->getElementsByTagName('end')->item(0) : null;
		$endNacToma = !is_null($endToma) ? $endToma->getElementsByTagName('endNac')->item(0) : null;
		$serv       = !is_null($infDPS) ? $infDPS->getElementsByTagName('serv')->item(0) : null;
		$locPrest   = !is_null($serv) ? $serv->getElementsByTagName('locPrest')->item(0) : null;
		$cServ      = !is_null($serv) ? $serv->getElementsByTagName('cServ')->item(0) : null;
		$valoresDPS = !is_null($infDPS) ? $infDPS->getElementsByTagName('valores')->item(0) : null;
		$vServPrest = !is_null($valoresDPS) ? $valoresDPS->getElementsByTagName('vServPrest')->item(0) : null;
		$trib       = !is_null($valoresDPS) ? $valoresDPS->getElementsByTagName('trib')->item(0) : null;
		$tribMun    = !is_null($trib) ? $trib->getElementsByTagName('tribMun')->item(0) : null;
		$tribFed    = !is_null($trib) ? $trib->getElementsByTagName('tribFed')->item(0) : null;
		$piscofins  = !is_null($tribFed) ? $tribFed->getElementsByTagName('piscofins')->item(0) : null;
		$totTrib    = !is_null($trib) ? $trib->getElementsByTagName('totTrib')->item(0) : null;

		$oNFSe = new NFSeGenericoInfNFSeNacional();

		$oNFSe->Id            = $infNFSe->getAttribute('Id');
		$oNFSe->xLocEmi       = $oDocument->getValue($infNFSe, 'xLocEmi');
		$oNFSe->xLocPrestacao = $oDocument->getValue($infNFSe, 'xLocPrestacao');
		$oNFSe->nNFSe         = $oDocument->getValue($infNFSe, 'nNFSe');
		$oNFSe->cLocIncid     = $oDocument->getValue($infNFSe, 'cLocIncid');
		$oNFSe->xLocIncid     = $oDocument->getValue($infNFSe, 'xLocIncid');
		$oNFSe->xTribNac      = $oDocument->getValue($infNFSe, 'xTribNac');
		$oNFSe->verAplic      = $oDocument->getValue($infNFSe, 'verAplic');
		$oNFSe->ambGer        = $oDocument->getValue($infNFSe, 'ambGer');
		$oNFSe->tpEmis        = $oDocument->getValue($infNFSe, 'tpEmis');
		$oNFSe->cStat         = $oDocument->getValue($infNFSe, 'cStat');
		$oNFSe->dhProc        = $oDocument->getValue($infNFSe, 'dhProc');
		$oNFSe->nDFSe         = $oDocument->getValue($infNFSe, 'nDFSe');
		$oNFSe->xOutInf       = $oDocument->getValue($infNFSe, 'xOutInf');

		$oNFSe->emit->CNPJ  = $oDocument->getValue($emit, 'CNPJ');
		$oNFSe->emit->IM    = $oDocument->getValue($emit, 'IM');
		$oNFSe->emit->xNome = $oDocument->getValue($emit, 'xNome');
		$oNFSe->emit->xFant = $oDocument->getValue($emit, 'xFant');
		$oNFSe->emit->fone  = $oDocument->getValue($emit, 'fone');
		$oNFSe->emit->email = $oDocument->getValue($emit, 'email');

		$oNFSe->emit->end->xLgr              = $oDocument->getValue($enderNac, 'xLgr');
		$oNFSe->emit->end->nro               = $oDocument->getValue($enderNac, 'nro');
		$oNFSe->emit->end->xCpl              = $oDocument->getValue($enderNac, 'xCpl');
		$oNFSe->emit->end->xBairro           = $oDocument->getValue($enderNac, 'xBairro');
		$oNFSe->emit->end->endNacEndExt->cMun = $oDocument->getValue($enderNac, 'cMun');
		$oNFSe->emit->end->endNacEndExt->CEP  = $oDocument->getValue($enderNac, 'CEP');

		$oNFSe->valores->vBC        = $oDocument->getValue($valoresNFSe, 'vBC');
		$oNFSe->valores->pAliqAplic = $oDocument->getValue($valoresNFSe, 'pAliqAplic');
		$oNFSe->valores->vISSQN     = $oDocument->getValue($valoresNFSe, 'vISSQN');
		$oNFSe->valores->vLiq       = $oDocument->getValue($valoresNFSe, 'vLiq');

		$oNFSe->DPS->tpAmb    = $oDocument->getValue($infDPS, 'tpAmb');
		$oNFSe->DPS->dhEmi    = $oDocument->getValue($infDPS, 'dhEmi');
		$oNFSe->DPS->verAplic = $oDocument->getValue($infDPS, 'verAplic');
		$oNFSe->DPS->serie    = $oDocument->getValue($infDPS, 'serie');
		$oNFSe->DPS->nDPS     = $oDocument->getValue($infDPS, 'nDPS');
		$oNFSe->DPS->dCompet  = $oDocument->getValue($infDPS, 'dCompet');
		$oNFSe->DPS->tpEmit   = $oDocument->getValue($infDPS, 'tpEmit');
		$oNFSe->DPS->cLocEmi  = $oDocument->getValue($infDPS, 'cLocEmi');

		$oNFSe->DPS->prest->CNPJ  = $oDocument->getValue($prest, 'CNPJ');
		$oNFSe->DPS->prest->IM    = $oDocument->getValue($prest, 'IM');
		$oNFSe->DPS->prest->fone  = $oDocument->getValue($prest, 'fone');
		$oNFSe->DPS->prest->email = $oDocument->getValue($prest, 'email');

		$oNFSe->DPS->prest->regTrib->opSimpNac  = $oDocument->getValue($regTrib, 'opSimpNac');
		$oNFSe->DPS->prest->regTrib->regEspTrib = $oDocument->getValue($regTrib, 'regEspTrib');

		$oNFSe->DPS->toma->CNPJ   = $oDocument->getValue($toma, 'CNPJ');
		$oNFSe->DPS->toma->xNome  = $oDocument->getValue($toma, 'xNome');
		$oNFSe->DPS->toma->email  = $oDocument->getValue($toma, 'email');

		$oNFSe->DPS->toma->end->xLgr               = $oDocument->getValue($endToma, 'xLgr');
		$oNFSe->DPS->toma->end->nro                = $oDocument->getValue($endToma, 'nro');
		$oNFSe->DPS->toma->end->xCpl               = $oDocument->getValue($endToma, 'xCpl');
		$oNFSe->DPS->toma->end->xBairro            = $oDocument->getValue($endToma, 'xBairro');
		$oNFSe->DPS->toma->end->endNacEndExt->cMun  = $oDocument->getValue($endNacToma, 'cMun');
		$oNFSe->DPS->toma->end->endNacEndExt->CEP   = $oDocument->getValue($endNacToma, 'CEP');

		$oNFSe->DPS->serv->locPrest->cLocPrestacao = $oDocument->getValue($locPrest, 'cLocPrestacao');
		$oNFSe->DPS->serv->cServ->cTribNac         = $oDocument->getValue($cServ, 'cTribNac');
		$oNFSe->DPS->serv->cServ->xDescServ        = $oDocument->getValue($cServ, 'xDescServ');
		$oNFSe->DPS->serv->cServ->cNBS             = $oDocument->getValue($cServ, 'cNBS');

		$oNFSe->DPS->valores->vServPrest->vServ = $oDocument->getValue($vServPrest, 'vServ');

		$oNFSe->DPS->valores->trib->tribMun->tribISSQN  = $oDocument->getValue($tribMun, 'tribISSQN');
		$oNFSe->DPS->valores->trib->tribMun->tpRetISSQN = $oDocument->getValue($tribMun, 'tpRetISSQN');

		$oNFSe->DPS->valores->trib->tribFed->piscofins->CST           = $oDocument->getValue($piscofins, 'CST');
		$oNFSe->DPS->valores->trib->tribFed->piscofins->tpRetPisCofins = $oDocument->getValue($piscofins, 'tpRetPisCofins');

		$oNFSe->DPS->valores->trib->totTrib->indTotTrib = $oDocument->getValue($totTrib, 'indTotTrib');

		return $oNFSe;
	}
	
	private function retInfNFSe(\DOMElement $oCompNfse, NFSeDocument $oDocument) {

		if($oCompNfse->getElementsByTagName('xLocEmi')->length > 0)
			return $this->retInfNFSeNacional($oCompNfse, $oDocument);

		$aConfig = $this->oGenerico->getConfig($this->oGenerico->getIsHomologacao() ? 'homologacao': 'producao', array());

		if( $oCompNfse->getElementsByTagName('Nfse')->length == 0 )
			return null;

		$Nfse = $oCompNfse->getElementsByTagName('Nfse')->item(0);

		$InfNfse 					= $Nfse->getElementsByTagName('InfNfse')->item(0);
		$ValoresNfse 				= $Nfse->getElementsByTagName('ValoresNfse')->item(0);
		$EnderecoPrestadorServico 	= $Nfse->getElementsByTagName('EnderecoPrestadorServico')->item(0);
		$DeclaracaoPrestacaoServico = $Nfse->getElementsByTagName('DeclaracaoPrestacaoServico')->item(0);

		$InfDeclaracaoPrestacaoServico = $DeclaracaoPrestacaoServico->getElementsByTagName('InfDeclaracaoPrestacaoServico');
		$InfDeclaracaoPrestacaoServico = $InfDeclaracaoPrestacaoServico->length == 0 ? $Nfse->getElementsByTagName('DeclaracaoPrestacaoServico') : $InfDeclaracaoPrestacaoServico;
		$InfDeclaracaoPrestacaoServico = $InfDeclaracaoPrestacaoServico->item(0);

		//Rps e opciional
		$Rps = $InfDeclaracaoPrestacaoServico->getElementsByTagName("Rps");
		$Rps = $Rps->length == 0 ? $Nfse->getElementsByTagName("DeclaracaoPrestacaoServico") : $Rps;
		$Rps = $Rps->item(0);

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
			'{@cpfCnpj}' => $this->oGenerico->getConfig('cpfCnpj', ''),
			'{@inscMunicipal}' => $this->oGenerico->getConfig('insMunicipal', ''),
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
		$oReturnDocument->loadXML(trim($xml), LIBXML_NOERROR | LIBXML_NOWARNING);

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
			$dom->loadXML(trim($return), LIBXML_NOERROR | LIBXML_NOWARNING);
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
					$aConfig = $this->oGenerico->getConfig('metodos');
					$configGerarNfse = PQDUtil::retDefault($aConfig, $metodo);
					$tagResposta = PQDUtil::retDefault($configGerarNfse['tagMap'], 'tagResposta', 'GerarNfseResposta');
					return $this->gerarNfseRetorno($oDocument, $tagResposta);
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
	private function gerarNfseRetorno(NFSeDocument $oGerarNfseRetorno, $tagResposta = 'GerarNfseResposta') {

		$return = array();
		if ($oGerarNfseRetorno->getElementsByTagName($tagResposta)->length == 1) {

			$return = array(
				'ListaMensagemRetorno' => $this->retListaMensagem($oGerarNfseRetorno, null, $this->oGenerico->getConfig('tagMensagensReturn', 'ListaMensagemRetorno')),
				'ListaNfse' => $this->retListNFSe($oGerarNfseRetorno)
			);

			$aConfig = $this->oGenerico->getConfig('templates', array());

			//Prefeitura de Goiânia envia mensagem de sucesso com código L000-NORMAL quando deu certo 
			if( $aConfig['folder'] == 'prefGoiania-v1' && count($return['ListaMensagemRetorno']) == 1 && $return['ListaMensagemRetorno'][0]->Codigo == 'L000' )
				$return['ListaMensagemRetorno'] = [];
		} 
		else
			$return = array('ListaMensagemRetorno' => array($this->retMsgForaEsperado()));

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

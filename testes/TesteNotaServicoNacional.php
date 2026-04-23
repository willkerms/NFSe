<?php
require_once 'TesteCase.php';

use NFSe\generico\NFSeGenerico;
use NFSe\generico\NFSeGenericoInfDPS;

class TesteNotaServicoNacional extends TesteCase{

	public static function main(){
		parent::main();

		self::testeGerarNFSe(json_decode(file_get_contents(__DIR__ . '/testeConfig.json'), true));
	}

	private static function testeGerarNFSe($aConfig){
		$oNotaNacional = new NFSeGenerico($aConfig);

		$oDPS = new NFSeGenericoInfDPS();

		$oDPS->tpAmb = 1;
		$oDPS->dhEmi = 1;
		$oDPS->verAplic = 1;
		$oDPS->serie = 1;
		$oDPS->nDPS = 1;
		$oDPS->dCompet = 1;
		$oDPS->tpEmit = 1;
		$oDPS->cMotivoEmisTI = 1;
		$oDPS->chNFSeRej = 1;
		$oDPS->cLocEmi = 1;
		
		/*
		$oDPS->subst->chSubstda = '' ;
		$oDPS->subst->cMotivo = '' ;
		$oDPS->subst->xMotivo = '' ;
		*/

		/*
		$oDPS->prest-> ;
		$oDPS->toma-> ;
		$oDPS->interm-> ;
		$oDPS->serv-> ;
		$oDPS->valores-> ;
		$oDPS->IBSCBS-> ;
		*/

		$oNotaNacional->gerarNfse($oDPS);
	}
}
TesteNotaServicoNacional::main();
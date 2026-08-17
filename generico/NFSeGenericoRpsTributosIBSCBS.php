<?php
namespace NFSe\generico;

/**
 * Tributos IBS/CBS do RPS (padrão ABRASF)
 *
 * @since 2026-08-14
 *
*/
class NFSeGenericoRpsTributosIBSCBS {

	/**
	 * Grupo de informações relacionadas ao IBS e à CBS
	 *
	 * @var NFSeGenericoRpsSitClasIBSCBS
	*/
	public $gIBSCBS;

	public function __construct() {
		$this->gIBSCBS = new NFSeGenericoRpsSitClasIBSCBS();
	}
}

<?php
namespace NFSe\generico;

/**
 * Valores do grupo IBS/CBS do RPS (padrão ABRASF)
 *
 * @since 2026-08-14
 *
*/
class NFSeGenericoRpsIBSCBSValores {

	/**
	 * Grupo de informações relacionados aos tributos IBS e CBS
	 *
	 * @var NFSeGenericoRpsTributosIBSCBS
	*/
	public $trib;

	public function __construct() {
		$this->trib = new NFSeGenericoRpsTributosIBSCBS();
	}
}

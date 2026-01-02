<?php
namespace NFSe\generico;

/**
 *
 * @since 2025-12-30
 * @author Norton Almeida Pontes
 *
*/
class NFSeGenericoInfoTributosIBSCBS {

	/**
	 * Grupo de informações relacionadas ao IBS e à CBS
	 * 
	 * @var NFSeGenericoInfoTributosSitClas
	*/
	public $gIBSCBS;

	public function __construct() {
		$this->gIBSCBS = new NFSeGenericoInfoTributosSitClas();
	}
}
<?php
namespace NFSe\generico;

/**
 *
 * @since 2025-12-30
 * @author Norton Almeida Pontes
 *
*/
class NFSeGenericoIBSCBSValores {

	/**
	 *
	 * Grupo de informações relativas a valores incluídos neste documento e recebidos por motivo de estarem relacionadas 
	 * a operações de terceiros, objeto de reembolso, repasse ou ressarcimento pelo recebedor, já tributados e aqui referenciados
	 * 
	 * @var NFSeGenericoInfoReeRepRes
	*/
	public $gReeRepRes;

	/**
	 * Grupo de informações relacionados aos tributos IBS e CBS
	 *
	 * @var NFSeGenericoInfoTributosIBSCBS
	*/
	public $trib;

	public function __construct() {
		$this->gReeRepRes = new NFSeGenericoInfoReeRepRes();
		$this->trib 	  = new NFSeGenericoInfoTributosIBSCBS();
	}

}
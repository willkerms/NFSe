<?php
namespace NFSe\generico;

/**
 *
 * @since 2025-12-30
 * @author Norton Almeida Pontes
 *
*/
class NFSeGenericoDPSValores {

	/**
	 * @var NFSeGenericoServPrest
	*/
	public $vServPrest;

	/**
	 * @var NFSeGenericoDescCondIncond
	*/
	public $vDescCondIncond;

	/**
	 * @var NFSeGenericoInfoDedRed
	*/
	public $vDedRed;

	/**
	 * @var NFSeGenericoTributacao
	*/
	public $trib;

	public function __construct() {
		$this->vServPrest 		= new NFSeGenericoServPrest();
		$this->vDescCondIncond 	= new NFSeGenericoDescCondIncond();
		$this->vDedRed 			= new NFSeGenericoInfoDedRed();
		$this->trib 			= new NFSeGenericoTributacao();
	}
}
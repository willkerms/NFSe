<?php
namespace NFSe\generico;

/**
 *
 * @since 2025-12-30
 * @author Norton Almeida Pontes
 *
*/
class NFSeGenericoTribFederal {

	/**
	 * Grupo de informações dos tributos PIS/COFINS
	 * 
	 * @var NFSeGenericoPisCofins
	*/
	public $piscofins;

	/**
	 * Valor monetário do CP(R$).
	 * 
	 * @var 
	*/
	public $vRetCP;

	/**
	 * Valor monetário do IRRF (R$).
	 * 
	 * @var 
	*/
	public $vRetIRRF;

	/**
	 * Valor monetário do CSLL (R$).
	 * 
	 * @var 
	*/
	public $vRetCSLL;

	public function __construct(){
		$this->piscofins = new NFSeGenericoPisCofins();
	}
}
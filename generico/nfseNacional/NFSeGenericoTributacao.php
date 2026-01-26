<?php
namespace NFSe\generico\nfseNacional;

/**
 *
 * @since 2025-12-30
 * @author Norton Almeida Pontes
 *
*/
class NFSeGenericoTributacao {

	/**
	 * Grupo de informações relacionados ao Imposto Sobre Serviços de Qualquer Natureza - ISSQN
	 * 
	 * @var NFSeGenericoTribMunicipal 
	*/
	public $tribMun;

	/**
	 * Grupo de informações de outros tributos relacionados ao serviço prestado
	 * 
	 * @var NFSeGenericoTribFederal 
	*/
	public $tribFed;

	/**
	 * Grupo de informações para totais aproximados dos tributos relacionados ao serviço prestado
	 * 
	 * @var NFSeGenericoTribTotal 
	*/
	public $totTrib;

	public function __construct() {
		$this->tribMun = new NFSeGenericoTribMunicipal();
		$this->tribFed = new NFSeGenericoTribFederal();
		$this->totTrib = new NFSeGenericoTribTotal();
	}
}
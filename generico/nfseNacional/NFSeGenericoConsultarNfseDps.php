<?php
namespace NFSe\generico\nfseNacional;

/**
 *
 * @since 2026-04-02
 * @author Norton Almeida Pontes
 *
*/
class NFSeGenericoConsultarNfseDps{


	/**
	 * Identificação do id DPS
	 * 
	 * @var $IdentificacaoDps
	*/
	public $IdentificacaoDps;

	/**
	 * 
	 * @var NFSeGenericoPrestadorConsultarNfseDps
	*/
	public $Prestador;



	public function __construct() {
		$this->Prestador = new NFSeGenericoPrestadorConsultarNfseDps();
	}
	
}

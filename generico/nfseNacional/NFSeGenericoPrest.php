<?php
namespace NFSe\generico\nfseNacional;

/**
 *
 * @since 2025-12-29
 * @author Norton Almeida Pontes
 *
*/
class NFSeGenericoPrest extends NFSeGenericoInfoPessoa {

	/**
	 * Grupo de informações relativas aos regimes de tributação do prestador de serviços
	 * 
	 * @var NFSeGenericoPrestRegTrib 
	*/
	public $regTrib;

	public function __construct() {
		parent::__construct();
		$this->regTrib = new NFSeGenericoPrestRegTrib();
	}

}
<?php
namespace NFSe\ginfes;

/**
 * 
 * @since 2016-02-10
 * @author Willker Moraes Silva
 *
 */
class NFSeGinfesServico {
	
	public $Valores;
	public $ItemListaServico;
	public $CodigoCnae;
	public $CodigoTributacaoMunicipio;
	public $Discriminacao;
	public $CodigoMunicipio;
	
	public function __construct(){
		$this->Valores = new NFSeGinfesValores();
	}
}
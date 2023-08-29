<?php
namespace NFSe\generico;

class NFSeGenericoConsultarLote{
	
	public $Protocolo;
	
	public $Prestador;
	
	public function __construct(){
		
		$this->Prestador 				= new NFSeGenericoPrestador();
	}

}

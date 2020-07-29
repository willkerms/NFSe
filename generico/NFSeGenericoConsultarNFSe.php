<?php
namespace NFSe\generico;

class NFSeGenericoConsultarNFSe{
	
	public $NumeroNfse;
	
	public $PeriodoEmissao;
	
	public $Tomador;
	
	public $IntermediarioServico;
	
	public function __construct(){
		
		$this->PeriodoEmissao 		= new NFSeGenericoPeriodoEmissao();
		$this->Tomador 				= new NFSeGenericoIdentificacaoTomador();
		$this->IntermediarioServico = new NFSeGenericoIntermediarioServico();

	}

}

<?php
namespace NFSe\ginfes;

/**
 * 
 * @since 2016-02-10
 * @author Willker Moraes Silva
 *
 */
class NFSeGinfesTomador{
	
	public $IdentificacaoTomador;
	
	public $RazaoSocial;
	
	public $Endereco;
	
	public $Contato;
	
	public function __construct(){
		$this->IdentificacaoTomador = new NFSeGinfesIdentificacaoTomador();
		$this->Endereco = new NFSeGinfesEndereco();
		$this->Contato = new NFSeGinfesContato();
	}
}
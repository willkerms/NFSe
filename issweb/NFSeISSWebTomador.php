<?php
namespace NFSe\issweb;

/**
 *
 * @since 2016-02-10
 * @author Willker Moraes Silva
 *
 */
class NFSeISSWebTomador{

	public $IdentificacaoTomador;

	public $NifTomador;

	public $RazaoSocial;

	public $Endereco;

	public $Contato;

	public function __construct(){
		$this->IdentificacaoTomador = new NFSeISSWebIdentificacaoTomador();
		$this->Endereco = new NFSeISSWebEndereco();
		$this->Contato = new NFSeISSWebContato();
	}
}
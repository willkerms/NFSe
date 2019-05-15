<?php
namespace NFSe\sigep;

/**
 *
 * @since 2016-02-10
 * @author Willker Moraes Silva
 *
 */
class NFSeSigepTomador{

	public $IdentificacaoTomador;

	public $NifTomador;

	public $RazaoSocial;

	public $Endereco;

	public $Contato;

	public function __construct(){
		$this->IdentificacaoTomador = new NFSeSigepIdentificacaoTomador();
		$this->Endereco = new NFSeSigepEndereco();
		$this->Contato = new NFSeSigepContato();
	}
}
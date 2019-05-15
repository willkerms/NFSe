<?php
namespace NFSe\sigep;

/**
 *
 * @since 2016-02-10
 * @author Willker Moraes Silva
 *
 */
class NFSeSigepPrestadorServico{

	public $Identificacao;

	public $RazaoSocial;

	public $Endereco;

	public $Contato;

	public function __construct(){
		$this->Identificacao = new NFSeSigepIdentificacao();
		$this->Endereco = new NFSeSigepEndereco();
		$this->Contato = new NFSeSigepContato();
	}
}
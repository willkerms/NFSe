<?php
namespace NFSe\issweb;

/**
 *
 * @since 2016-02-10
 * @author Willker Moraes Silva
 *
 */
class NFSeISSWebPrestadorServico{

	public $Identificacao;

	public $RazaoSocial;

	public $Endereco;

	public $Contato;

	public function __construct(){
		$this->Identificacao = new NFSeISSWebIdentificacao();
		$this->Endereco = new NFSeISSWebEndereco();
		$this->Contato = new NFSeISSWebContato();
	}
}
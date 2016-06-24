<?php
namespace NFSe\ginfes;

/**
 *
 * @since 2016-02-10
 * @author Willker Moraes Silva
 *
 */
class NFSeGinfesPrestadorServico{

	public $Identificacao;

	public $RazaoSocial;

	public $Endereco;

	public $Contato;

	public function __construct(){
		$this->Identificacao = new NFSeGinfesIdentificacao();
		$this->Endereco = new NFSeGinfesEndereco();
		$this->Contato = new NFSeGinfesContato();
	}
}
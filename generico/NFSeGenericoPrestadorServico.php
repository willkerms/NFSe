<?php
namespace NFSe\generico;

/**
 *
 * @since 2016-02-10
 * @author Willker Moraes Silva
 *
 */
class NFSeGenericoPrestadorServico {

	public $Identificacao;

	public $RazaoSocial;

	public $Endereco;

	public $Contato;

	public function __construct() {

		$this->Identificacao = new NFSeGenericoIdentificacao();
		$this->Endereco = new NFSeGenericoEndereco();
		$this->Contato = new NFSeGenericoContato();

	}

}

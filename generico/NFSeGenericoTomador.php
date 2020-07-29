<?php
namespace NFSe\generico;

/**
 *
 * @since 2016-02-10
 * @author Willker Moraes Silva
 *
 */
class NFSeGenericoTomador {

	public $IdentificacaoTomador;

	public $NifTomador;

	public $RazaoSocial;

	public $Endereco;

	public $Contato;

	public function __construct() {

		$this->IdentificacaoTomador = new NFSeGenericoIdentificacaoTomador();
		$this->Endereco = new NFSeGenericoEndereco();
		$this->Contato = new NFSeGenericoContato();
		
	}
	
}

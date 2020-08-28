<?php
namespace NFSe\generico;

/**
 *
 * @since 2016-02-10
 * @author Willker Moraes Silva
 *
 */
class NFSeGenericoIntermediarioServico{

	/**
	 * @var NFSeGenericoIdentificacao
	 */
	public $IdentificacaoIntermediario;

	/**
	 *
	 * @var string
	 */
	public $RazaoSocial;

	/**
	 *
	 * @var string
	 */
	public $CodigoMunicipio;

	public function __construct(){
		$this->IdentificacaoIntermediario = new NFSeGenericoIdentificacao();
	}
}
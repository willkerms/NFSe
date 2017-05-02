<?php
namespace NFSe\issweb;

/**
 *
 * @since 2016-02-10
 * @author Willker Moraes Silva
 *
 */
class NFSeISSWebContato{

	public $Telefone;

	public $Ddd;

	/**
	 * Tipo do Telefone
	 *
	 * CE|CO|RE
	 *
	 * @var string
	 */
	public $TipoTelefone = 'CO';

	public $Email;
}
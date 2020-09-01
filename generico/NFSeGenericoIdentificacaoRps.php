<?php
namespace NFSe\generico;

/**
 *
 * @since 2016-02-10
 * @author Willker Moraes Silva
 *
 */
class NFSeGenericoIdentificacaoRps{

	public $Numero;

	/**
	 * 1 - Recibo Provisório de Serviço
	 * 2 - Nota Fiscal Conjugada
	 * 3 - Cupom
	 *
	 * @var string
	 */
	public $Tipo = 1;

	public $Serie;
	
}

<?php
namespace NFSe\generico;

/**
 *
 * @since 2025-12-29
 * @author Norton Almeida Pontes
 *
*/
class NFSeGenericoFornecDoc {

	/**
	 * Número de CNPJ
	 * 
	 * @var $CNPJ
	 */
	public $CNPJ;

	/**
	 * Número de CPF
	 * 
	 * @var $CPF
	 */
	public $CPF;

	/**
	 * NIF - Número de Identificação Fiscal fornecido por órgão de administração tributária no exterior
	 * 
	 * @var $NIF
	 */
	public $NIF;

	/**
	 * Motivo para não informação do NIF:
	 * 1 - Dispensado do NIF;
	 * 2 - Não exigência do NIF;
	 * 
	 * @var $cNaoNIF
	 */
	public $cNaoNif;

	/**
	 * Nome/Nome Empresarial
	 * 
	 * @var $xNome
	*/
	public $xNome;
}
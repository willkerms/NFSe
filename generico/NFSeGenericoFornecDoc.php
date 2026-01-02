<?php
namespace NFSe\generico;

/**
 *
 * @since 2025-12-29
 * @author Norton Almeida Pontes
 *
*/
class NFSeGenericoFornecDoc extends NFSeGenericoInfoPessoa {

	/**
	 * Número de CNPJ
	 * ou
	 * Número de CPF
	 * ou
	 * NIF - Número de Identificação Fiscal fornecido por órgão de administração tributária no exterior
	 * ou
	 * Motivo para não informação do NIF:
	 * 1 - Dispensado do NIF;
	 * 2 - Não exigência do NIF;
	 * 
	 * @var $CNPJ | $CPF | $NIF | $cNaoNIF
	 */
	public $CnpjCpfNifCNaoNif;

	/**
	 * Nome/Nome Empresarial
	 * 
	 * @var $xNome
	*/
	public $xNome;
}
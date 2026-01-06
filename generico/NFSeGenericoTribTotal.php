<?php
namespace NFSe\generico;

/**
 *
 * @since 2025-12-30
 * @author Norton Almeida Pontes
 *
*/
class NFSeGenericoTribTotal {

	/**
	 * Valor monetário total aproximado dos tributos, em conformidade com o artigo 1o da Lei no 12.741/2012
	 * 
	 * @var NFSeGenericoVTotTrib
	*/
	public $vTotTrib;

	/**
	 * Valor percentual total aproximado dos tributos, em conformidade com o artigo 1o da Lei no 12.741/2012
	 * 
	 * @var NFSeGenericoPTotTrib
	*/
	public $pTotTrib;

	/**
	 * Indicador de informação de valor total de tributos. Possui valor fixo igual a zero (indTotTrib=0).
	 * Não informar nenhum valor estimado para os Tributos (Decreto 8.264/2014).
	 * 0 - Não;
	 * 
	 * @var $indTotTrib
	*/
	public $indTotTrib;

	/**
	 * Valor percentual aproximado do total dos tributos da alíquota do Simples Nacional (%)
	 * 
	 * @var $pTotTribSN
	*/
	public $pTotTribSN;
}
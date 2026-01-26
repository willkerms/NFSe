<?php
namespace NFSe\generico\nfseNacional;

/**
 *
 * @since 2025-12-30
 * @author Norton Almeida Pontes
 *
*/
class NFSeGenericoPisCofins {

	/**
	 * 00 - Nenhum;      
	 * 01 - Operação Tributável com Alíquota Básica;
	 * 02 - Operação Tributável com Alíquota Diferenciada;
	 * 03 - Operação Tributável com Alíquota por Unidade de Medida de Produto;
	 * 04 - Operação Tributável monofásica - Revenda a Alíquota Zero;
	 * 05 - Operação Tributável por Substituição Tributária;
	 * 06 - Operação Tributável a Alíquota Zero;
	 * 07 - Operação Tributável da Contribuição;
	 * 08 - Operação sem Incidência da Contribuição;
	 * 09 - Operação com Suspensão da Contribuição;
	 * 
	 * @var $CST 
	*/
	public $CST;

	/**
	 * Valor da Base de Cálculo do PIS/COFINS (R$).
	 * 
	 * @var $vBCPisCofins 
	*/
	public $vBCPisCofins;

	/**
	 * Valor da Alíquota do PIS (%).
	 * 
	 * @var $pAliqPis 
	*/
	public $pAliqPis;

	/**
	 * Valor da Alíquota da COFINS (%).
	 * 
	 * @var $pAliqCofins 
	*/
	public $pAliqCofins;

	/**
	 * Valor monetário do PIS (R$).
	 * 
	 * @var $vPis 
	*/
	public $vPis;

	/**
	 * Valor monetário do COFINS (R$).
	 * 
	 * @var $vCofins 
	*/
	public $vCofins;

	/**
	 * 1 - Retido;
	 * 2 - Não Retido;
	 * 
	 * @var $tpRetPisCofins 
	*/
	public $tpRetPisCofins;
}
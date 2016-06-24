<?php
namespace NFSe\ginfes;

/**
 * 
 * @since 2016-02-10
 * @author Willker Moraes Silva
 *
 */
class NFSeGinfesValores{
	
	public $ValorServicos = 0;
	public $ValorDeducoes = 0;
	public $ValorPis = 0;
	public $ValorCofins = 0;
	public $ValorInss = 0;
	public $ValorIr = 0;
	public $ValorCsll = 0;
	
	/**
	 * 1 - Sim
	 * 2 - Não
	 * 
	 * @var number
	 */
	public $IssRetido = 2;
	public $ValorIss = 0;
	public $ValorIssRetido = 0;
	public $OutrasRetencoes = 0;
	public $BaseCalculo = 0;
	public $Aliquota = 0;
	public $ValorLiquidoNfse = 0;
	public $DescontoIncondicionado = 0;
	public $DescontoCondicionado = 0;
}
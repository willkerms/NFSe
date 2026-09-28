<?php
namespace NFSe\templates;

use NFSe\generico\NFSeGenericoInfRps;

/**
 * RPS da Elotech: campos que só existem no layout elotech-pr-v2-03.
 * O NFSeGenerico::retXMLRps monta o {@Campo} e o {@ifCampo} de cada propriedade desta classe; a condição do {@ifCampo} vem do @if (sem @if: !empty).
 *
 * @since 2026-09-25
 * @author Victor Hugo Benatti
 */
class ElotechInfRps extends NFSeGenericoInfRps {

	/**
	 * @field(name=RetidoPis)
	 * @if($oRps->Servico->Valores->ValorPis > 0)
	 * Indicador de retenção do PIS: 1 - Sim; 2 - Não
	 */
	public $RetidoPis = 2;

	/**
	 * @field(name=RetidoCofins)
	 * @if($oRps->Servico->Valores->ValorCofins > 0)
	 * Indicador de retenção do COFINS: 1 - Sim; 2 - Não
	 */
	public $RetidoCofins = 2;

	/**
	 * @field(name=RetidoInss)
	 * @if($oRps->Servico->Valores->ValorInss > 0)
	 * Indicador de retenção do INSS: 1 - Sim; 2 - Não
	 */
	public $RetidoInss = 2;

	/**
	 * @field(name=RetidoIr)
	 * @if($oRps->Servico->Valores->ValorIr > 0)
	 * Indicador de retenção do IR: 1 - Sim; 2 - Não
	 */
	public $RetidoIr = 2;

	/**
	 * @field(name=RetidoCsll)
	 * @if($oRps->Servico->Valores->ValorCsll > 0)
	 * Indicador de retenção da CSLL: 1 - Sim; 2 - Não
	 */
	public $RetidoCsll = 2;

	/**
	 * @field(name=RetidoOutrasRetencoes)
	 * @if($oRps->Servico->Valores->OutrasRetencoes > 0)
	 * Indicador de retenção das outras retenções: 1 - Sim; 2 - Não
	 */
	public $RetidoOutrasRetencoes = 2;

	/**
	 * @field(name=CSTPisCofins)
	 * CST do PIS/COFINS (2 dígitos)
	 */
	public $CSTPisCofins;
}

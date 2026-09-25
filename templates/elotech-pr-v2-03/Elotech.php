<?php
namespace NFSe\templates;

use NFSe\generico\NFSeGenericoInfRps;

/**
 * RPS da Elotech: campos que só existem no layout elotech-pr-v2-03.
 * O NFSeGenerico::retXMLRps monta o {@Campo} e o {@ifCampo} de cada propriedade desta classe.
 *
 * @since 2026-09-25
 * @author Victor Hugo Benatti
 */
class Elotech extends NFSeGenericoInfRps {

	/**
	 * Indicadores de retenção dos tributos federais: 1 - Sim; 2 - Não
	 */
	public $RetidoPis = 2;
	public $RetidoCofins = 2;
	public $RetidoInss = 2;
	public $RetidoIr = 2;
	public $RetidoCsll = 2;
	public $RetidoOutrasRetencoes = 2;

	/**
	 * CST do PIS/COFINS (2 dígitos)
	 */
	public $CSTPisCofins;
}

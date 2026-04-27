<?php
namespace NFSe\generico\nfseNacional;

/**
 *
 * @since 2026-04-26
 * @author Willker Moraes Silva
 *
*/
class NFSeGenericoInfNFSeValores {

	/**
	 * Valor monetário (R$) de dedução/redução da base de cálculo (BC) do ISSQN.
	 *
	 * @var $vCalcDR
	*/
	public $vCalcDR;

	/**
	 * Tipo Benefício Municipal (BM):
	 * 1 - Isenção;
	 * 2 - Redução da BC em 'ppBM' %;
	 * 3 - Redução da BC em R$ 'vInfoBM';
	 * 4 - Alíquota Diferenciada de 'aliqDifBM' %;
	 *
	 * @var $tpBM
	*/
	public $tpBM;

	/**
	 * Valor monetário (R$) do percentual de redução da base de cálculo (BC) do ISSQN
	 * devido a um benefício municipal (BM).
	 *
	 * @var $vCalcBM
	*/
	public $vCalcBM;

	/**
	 * Valor da Base de Cálculo do ISSQN (R$)
	 * vBC = vServ - descIncond - (vDR ou vCalcDR + vCalcReeRepRes) - (vRedBCBM ou VCalcBM)
	 *
	 * @var $vBC
	*/
	public $vBC;

	/**
	 * Alíquota aplicada sobre a base de cálculo para apuração do ISSQN.
	 *
	 * @var $pAliqAplic
	*/
	public $pAliqAplic;

	/**
	 * Valor do ISSQN (R$) = vBC x pAliqAplic
	 *
	 * @var $vISSQN
	*/
	public $vISSQN;

	/**
	 * Valor total de retenções = Σ(CP + IRRF + CSLL + ISSQN* + (PIS + COFINS)**)
	 *
	 * @var $vTotalRet
	*/
	public $vTotalRet;

	/**
	 * Valor líquido = Valor do serviço - Desconto condicionado - Desconto incondicionado
	 * - Valores retidos (CP, IRRF, CSLL)* - Valores, se retidos (ISSQN, PIS, COFINS)**
	 *
	 * @var $vLiq
	*/
	public $vLiq;

}

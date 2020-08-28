<?php
namespace NFSe\generico;

/**
 * @since 2020-08-27
 * @author Willker Moraes Silva
 */
class NFSeGenericoDeducao {

	/**
 	 * 1 – Materiais;
	 * 2 – Subempreitada de mão de obra;
	 * 3 – Serviços;
	 * 4 – Produção externa;
	 * 5 – Alimentação e bebidas/frigobar;
	 * 6 – Reembolso de despesas;
	 * 7 – Repasse consorciado;
	 * 8 – Repasse plano de saúde;
	 * 99 – Outras deduções)
	 */
	public $TipoDeducao;

	public $DescricaoDeducao;

	/**
	 * @var NFSeGenericoIdentificacaoDocumentoDeducao
	 */
	public $IdentificacaoDocumentoDeducao;

	/**
	 * @var NFSeGenericoIdentificacaoFornecedor
	 */
	public $DadosFornecedor;

	public $DataEmissao;

	public $ValorDedutivel;

	public $ValorUtilizadoDeducao;

	public function __construct(){

		$this->IdentificacaoDocumentoDeducao = new NFSeGenericoIdentificacaoDocumentoDeducao();
		$this->DadosFornecedor = new NFSeGenericoIdentificacaoFornecedor();
	}
}
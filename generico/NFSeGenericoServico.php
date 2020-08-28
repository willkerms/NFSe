<?php
namespace NFSe\generico;

/**
 * @since 2016-02-10
 * @author Willker Moraes Silva
 *
 */
class NFSeGenericoServico {

	/**
	 * @var NFSeGenericoValores
	 */
	public $Valores;

	/**
	 * 1 - Sim
	 * 2 - Não
	 * 
	 * @var number
	 */
	public $IssRetido = 2;

	/**
	 * @var string
	 */
	public $ResponsavelRetencao;

	/**
	 * @var string
	 */
	public $ItemListaServico;

	/**
	 * @var string
	 */
	public $CodigoCnae;

	/**
	 * @var string
	 */
	public $CodigoTributacaoMunicipio;

	/**
	 * @var string
	 */
	public $CodigoNbs;

	/**
	 * @var string
	 */
	public $Discriminacao;

	/**
	 * @var string
	 */
	public $CodigoMunicipio;

	/**
	 * @var string
	 */
	public $CodigoPais;

	/**
	 * Exigibilidade do ISS da NFS-e
	 * 1 - Exigivel; 
	 * 2 - Nao incidencia; 
	 * 3 - Isencao; 
	 * 4 - Exportacao; 
	 * 5 - Imunidade; 
	 * 6 - Exigibilidade Suspensa por Decisao Judicial; 
	 * 7 - Exigibilidade Suspensa por Processo Administrativo
	 *
	 * @var number
	 */
	public $ExigibilidadeISS = 1;

	/**
	 * @var string
	 */
	public $IdentifNaoExigibilidade;

	/**
	 * @var string
	 */
	public $MunicipioIncidencia;

	/**
	 * @var string
	 */
	public $NumeroProcesso;

	public function __construct(){
		$this->Valores = new NFSeGenericoValores();
	}

}

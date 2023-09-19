<?php
namespace NFSe\generico;

class NFSeGenericoConsultarUrlNfse{
	
	/**
	 * @var NFSeGenericoPrestador
	 */
	public $Prestador;

	/**
	 * @var NFSeGenericoIdentificacaoTomador
	 */
	public $Tomador;

	/**
	 * @var NFSeGenericoIdentificacaoRps
	 */
	public $IdentificacaoRps;

	/**
	 * @var int
	 */
	public $NumeroNfse;
	
	/**
	 * @var string
	 */
	public $DataInicialEmissao;
	
	/**
	 * @var string
	 */
	public $DataFinalEmissao;
	
	/**
	 * @var string
	 */
	public $DataInicialCompetencia;	
	
	/**
	 * @var string
	 */
	public $DataFinalCompetencia;

	/**
	 * @var NFSeGenericoIdentificacao
	 */
	public $Intermediario;

	/**
	 * @var int
	 */
	public $Pagina = 1;
	
	public function __construct(){
		
		$this->Prestador = new NFSeGenericoPrestador();
		
		$this->Tomador = new NFSeGenericoIdentificacaoTomador();
		
		$this->IdentificacaoRps = new NFSeGenericoIdentificacaoRps();

		$this->Intermediario = new NFSeGenericoIdentificacao();
	}

}

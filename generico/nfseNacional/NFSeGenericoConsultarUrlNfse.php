<?php
namespace NFSe\generico\nfseNacional;

class NFSeGenericoConsultarUrlNfse{
	
	/**
	 * @var NFSeGenericoInfoPessoa
	 */
	public $Prestador;

	/**
	 * @var NFSeGenericoInfoPessoa
	 */
	public $Tomador;

	/**
	 * @var NFSeGenericoConsultarNfseDps
	 */
	public $IdentificacaoDps;

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
	 * @var NFSeGenericoInfoPessoa
	 */
	public $Intermediario;

	/**
	 * @var int
	 */
	public $Pagina = 1;
	
	public function __construct(){
		
		$this->Prestador = new NFSeGenericoPrestadorConsultarNfseDps();
		
		$this->Tomador = new NFSeGenericoPrestadorConsultarNfseDps();
		
		$this->IdentificacaoDps = new NFSeGenericoConsultarNfseDps();

		$this->Intermediario = new NFSeGenericoPrestadorConsultarNfseDps();
	}
}

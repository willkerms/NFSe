<?php
namespace NFSe\generico\nfseNacional;

/**
 *
 * @since 2025-12-30
 * @author Norton Almeida Pontes
 *
*/
class NFSeGenericoTribMunicipal  {

	/**
	 * 1 - Operação tributável;
	 * 2 - Imunidade;
	 * 3 - Exportação de serviço;
	 * 4 - Não Incidência;
	 * 
	 * @var $tribISSQN  
	*/
	public $tribISSQN;

	/**
	 * Código do país onde se verficou o resultado da prestação do serviço para o caso de Exportação de Serviço.(Tabela de Países ISO)
	 * 
	 * @var $cPaisResult  
	*/
	public $cPaisResult;

	/**
	 * 0 - Imunidade (tipo não informado na nota de origem);
	 * 1 - Patrimônio, renda ou serviços, uns dos outros (CF88, Art 150, VI, a);
	 * 2 - Templos de qualquer culto (CF88, Art 150, VI, b);
	 * 3 - Patrimônio, renda ou serviços dos partidos políticos, inclusive suas fundações, das entidades sindicais dos trabalhadores, das instituições de educação e de assistência social, sem fins lucrativos, atendidos os requisitos da lei (CF88, Art 150, VI, c);
	 * 4 - Livros, jornais, periódicos e o papel destinado a sua impressão (CF88, Art 150, VI, d);
	 * 5 - Fonogramas e videofonogramas musicais produzidos no Brasil contendo obras musicais ou literomusicais de autores brasileiros e/ou obras em geral interpretadas por artistas brasileiros bem como os suportes materiais ou arquivos digitais que os contenham, salvo na etapa de replicação industrial de mídias ópticas de leitura a laser.   (CF88, Art 150, VI, e);
	 * 
	 * @var $tpImunidade  
	*/
	public $tpImunidade;

	/**
	 * Informações para a suspensão da Exigibilidade do ISSQN
	 * 
	 * @var NFSeGenericoExigSuspensa
	*/
	public $exigSusp;

	/**
	 * 1 - Operação tributável;
	 * 2 - Exportação de serviço;
	 * 3 - Não Incidência;
	 * 4 - Imunidade;
	 * 
	 * @var NFSeGenericoBeneficioMunicipal
	*/
	public $BM;

	/**
	 * 1 - Não Retido;
	 * 2 - Retido pelo Tomador;
	 * 3 - Retido pelo Intermediario;
	 * 
	 * @var $tpRetISSQN  
	*/
	public $tpRetISSQN;

	/**
	 * Valor da alíquota (%) do serviço prestado relativo ao município sujeito ativo (município de incidência) do ISSQN.
	 * Se o município de incidência pertence ao Sistema Nacional NFS-e a alíquota estará parametrizada e, portanto,
	 * será fornecida pelo sistema.
	 * Se o município de incidência não pertence ao Sistema Nacional NFS-e a alíquota não estará parametrizada e,
	 * por isso, deverá ser fornecida pelo emitente.
	 * 
	 * @var $pAliq  
	*/
	public $pAliq;

	public function __construct() {
		$this->exigSusp = new NFSeGenericoExigSuspensa();
		$this->BM 		= new NFSeGenericoBeneficioMunicipal();
	}
}
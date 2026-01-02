<?php
namespace NFSe\generico;

/**
 *
 * @since 2025-12-29
 * @author Norton Almeida Pontes
 *
*/
class NFSeGenericoPrestRegTrib {

	/**
	 * 1 - Não Optante;
	 * 2 - Optante - Microempreendedor Individual (MEI);
	 * 3 - Optante - Microempresa ou Empresa de Pequeno Porte (ME/EPP);
	 * 
	 * @var $opSimpNac 
	*/
	public $opSimpNac;

	/**
	 * 1 – Regime de apuração dos tributos federais e municipal pelo SN;
	 * 2 – Regime de apuração dos tributos federais pelo SN e ISSQN  por fora do SN conforme respectiva legislação municipal do tributo;
	 * 3 – Regime de apuração dos tributos federais e municipal por fora do SN conforme respectivas legilações federal e municipal de cada tributo;
	 * 
	 * @var $regApTribSN 
	*/
	public $regApTribSN;

	/**
	 * 0 - Nenhum;
	 * 1 - Ato Cooperado (Cooperativa);
	 * 2 - Estimativa;
	 * 3 - Microempresa Municipal;
	 * 4 - Notário ou Registrador;
	 * 5 - Profissional Autônomo;
	 * 6 - Sociedade de Profissionais;
	 * 
	 * @var $regEspTrib 
	*/
	public $regEspTrib;


}
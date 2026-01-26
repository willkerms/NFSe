<?php
namespace NFSe\generico\nfseNacional;

/**
 *
 * @since 2025-12-30
 * @author Norton Almeida Pontes
 *
*/
class NFSeGenericoBeneficioMunicipal {


	/**
	 * 
	 * Identificador do benefício parametrizado pelo município.
	 * Trata-se de um identificador único que foi gerado pelo Sistema Nacional no momento 
	 * em que o município de incidência do ISSQN incluiu o benefício no sistema.
	 * 
	 * Critério de formação do número de identificação de parâmetros municipais:
	 * 7 dígitos - posição 1 a 7: número identificador do Município, conforme código IBGE;
	 * 2 dígitos - posições 8 e 9 : número identificador do tipo de parametrização 
	 * (01-legislação, 02-regimes especiais, 03-retenções, 04-outros benefícios);
	 * 5 dígitos - posição 10 a 14 : número sequencial definido pelo sistema quando do
	 * registro específico do parâmetro dentro do tipo de parametrização no sistema;
	 * 
	 * @var
	*/
	public $nBM;

	/**
	 * Valor monetário informado pelo emitente para redução da base de cálculo (BC) do ISSQN devido a um Benefício Municipal (BM).
	 * 
	 * @var $vRedBCBM
	*/
	public $vRedBCBM;

	/**
	 * Valor percentual informado pelo emitente para redução da base de cálculo (BC) do ISSQN devido a um Benefício Municipal (BM).
	 * 
	 * @var $pRedBCBM
	*/
	public $pRedBCBM;
}
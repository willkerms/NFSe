<?php
namespace NFSe\generico;

/**
 *
 * @since 2025-12-30
 * @author Norton Almeida Pontes
 *
*/
class NFSeGenericoObra {

	/**
	 * Inscrição imobiliária fiscal (código fornecido pela Prefeitura Municipal para a identificação da obra ou para fins de recolhimento do IPTU)
	 * 
	 * @var $inscImobFisc
	*/
	public $inscImobFisc;

	/**
	 * Número de identificação da obra.
	 * Cadastro Nacional de Obras (CNO) ou Cadastro Específico do INSS (CEI).
	 * ou
	 * Código do Cadastro Imobiliário Brasileiro - CIB.
	 * ou
	 * Grupo de informações do endereço da obra do serviço prestado
	 * 
	 * @var NFSeGenericoEndObra | $cObra | $cCIB
	*/
	public $cObraCCIBEnd;
}
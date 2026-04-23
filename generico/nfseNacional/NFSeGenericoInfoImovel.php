<?php
namespace NFSe\generico\nfseNacional;

/**
 *
 * @since 2025-12-29
 * @author Norton Almeida Pontes
 *
*/
class NFSeGenericoInfoImovel {

	/**
	 * Inscrição imobiliária fiscal (código fornecido pela Prefeitura Municipal para a identificação da obra ou para fins de recolhimento do IPTU)
	 * 
	 * @var $inscImobFisc
	*/
	public $inscImobFisc;

	/**
	 * Código do Cadastro Imobiliário Brasileiro - CIB
	 * 
	 * @var $cCIB
	*/
	public $cCIB;

	/**
	 * Grupo de informações do endereço da obra do serviço prestado
	 * 
	 * @var NFSeGenericoEnderecoSimples
	*/
	public $end;

}
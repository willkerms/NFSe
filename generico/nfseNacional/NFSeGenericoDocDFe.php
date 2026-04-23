<?php
namespace NFSe\generico\nfseNacional;

/**
 *
 * @since 2025-12-30
 * @author Norton Almeida Pontes
 *
*/
class NFSeGenericoDocDFe {
	
	/**
	 * Documento fiscal a que se refere a chaveDfe que seja um dos documentos do Repositório Nacional
	 * 
	 * @var $tipoChaveDFe 
	*/
	public $tipoChaveDFe;

	/**
	 * Descrição da DF-e a que se refere a chaveDfe que seja um dos documentos do Repositório Nacional
	 * Deve ser preenchido apenas quando "tipoChaveDFe = 9 (Outro)"
	 * 
	 * @var $xTipoChaveDFe 
	*/
	public $xTipoChaveDFe;

	/**
	 * Chave do Documento Fiscal eletrônico do repositório nacional referenciado para os casos de operações já tributadas
	 * 
	 * @var $chaveDFe 
	*/
	public $chaveDFe;
}
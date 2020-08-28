<?php
namespace NFSe\generico;

/**
 *
 * @since 2020-08-27
 * @author Willker Moraes Silva
 *
 */
class NFSeGenericoIdentificacaoDocumentoDeducao{

	/**
	 * 0 - NFSe
	 * 1 - NFe
	 * 2 - Outros Documentos
	 */
	public $tpDocumento = 0;

	
	public $CodigoMunicipioGerador;

	public $NumeroNfse;

	public $CodigoVerificacao;


	public $NumeroNfe;

	public $UfNfe;

	public $ChaveAcessoNfe;


	public $IdentificacaoDocumento;
}

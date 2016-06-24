<?php
namespace NFSe;

class NFSeReturn {

	/**
	 * checkForFault
	 * Verifica se a mensagem de retorno Ã© uma FAULT
	 * Normalmente essas falhas ocorrem devido a falhas internas
	 * nos servidores da SEFAZ
	 * 
	 * Função retirada do projeto nfephp-org
	 * @link https://github.com/nfephp-org
	 * @param NFePHP\Common\Dom\Dom $dom
	 * @return string
	 */
	protected static function checkForFault(NFSeDocument $dom) {

		$fault = $dom->getElementsByTagName('Fault')->item(0);
		$reason = '';
		if (isset($fault)) 
			$reason = $fault->getElementsByTagName('Text')->item(0)->nodeValue;
		
		return $reason;
	}
}
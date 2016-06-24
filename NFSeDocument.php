<?php
namespace NFSe;

class NFSeDocument extends \DOMDocument {

	public function __construct() {

		parent::__construct("1.0", "UTF-8");
		$this->registerNodeClass("DOMElement", 'NFSe\NFSeElement');
	}
}
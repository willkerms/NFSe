<?php
namespace NFSe;

/**
 *
 * @since 2016-01-18
 * @author Willker Moraes Silva
 */
class NFSe {

	private $certPrivKey;

	private $certPubKey;

	private $certKey;

	private $sslProtocol = 0;

	/**
	 *
	 * @var \SoapClient
	 */
	private $soapClient;

	private $deleteTempFiles = false;

	private $pathTempFiles = null;

	/**
	 * @param string $certPrivKey
	 * @param string $certPubKey
	 * @param string $certKey
	 */
	public function __construct($certPrivKey, $certPubKey, $certKey) {
		$this->certKey = $certKey;
		$this->certPrivKey = $certPrivKey;
		$this->certPubKey = $certPubKey;
	}

	/**
	 * signXML
	 * Assinador TOTALMENTE baseado em PHP para arquivos XML
	 * este assinador somente utiliza comandos nativos do PHP para assinar
	 * os arquivos XML
	 *
	 * Função retirada do projeto nfephp-org
	 * @link https://github.com/nfephp-org
	 *
	 * @param string $docxml
	 *        	String contendo o arquivo XML a ser assinado
	 * @param string $tagid
	 *        	TAG do XML que devera ser assinada
	 * @param string $appendTag
	 *        	: tag onde será "pendurada" a assinatura
	 * @param string $ns
	 *        	: namespace utilizado, normalmente "p1"
	 * @return mixed false se houve erro ou string com o XML assinado
	 */
	public function signXML($docxml, $tagid = '', $appendTag = false, $ns = '', $firstChild = false, $createNS = true, $aSerach = array("\r\n", "\n", "\r", "\t"), $aReplace = "") {

		if ($tagid == '') {
			$msg = "Uma tag deve ser indicada para que seja assinada!!";
			throw new \Exception($msg);
		}
		if ($docxml == '') {
			$msg = "Um xml deve ser passado para que seja assinado!!";
			throw new \Exception($msg);
		}
		// obter o chave privada para a ssinatura
		$fp = fopen($this->certPrivKey, "r");
		$priv_key = fread($fp, 8192);
		fclose($fp);
		$pkeyid = openssl_get_privatekey($priv_key);

		// limpeza do xml com a retirada dos CR, LF e TAB
		$docxml = str_replace($aSerach, $aReplace, $docxml);
		// carrega o documento no DOM
		$xmldoc = new NFSeDocument();
		$xmldoc->preservWhiteSpace = false; // elimina espaços em branco
		$xmldoc->formatOutput = false;
		// muito importante deixar ativadas as opçoes para limpar os espacos em branco
		// e as tags vazias
		if ($xmldoc->loadXML($docxml, LIBXML_NOBLANKS | LIBXML_NOEMPTYTAG | LIBXML_NOERROR)) {
			$root = $xmldoc->documentElement;
		}
		else {
			$msg = "Erro ao carregar XML, provavel erro na passagem do parâmetro docXML!!";
			throw new \Exception($msg);
		}

		// extrair a tag com os dados a serem assinados
		$node = $xmldoc->getElementsByTagName($tagid)->item(0);
		$id = trim($node->getAttribute("Id"));

		$id = $id == "" ? trim($node->getAttribute("id")) : $id;//Tem alguns webService que a tag id é minusculo

		$idnome = preg_replace('/[^0-9]/', '', $id);
		// extrai os dados da tag para uma string
		$dados = $node->C14N(false, false, NULL, NULL);
		// calcular o hash dos dados
		$hashValue = hash('sha1', $dados, true);
		// converte o valor para base64 para serem colocados no xml
		$digValue = base64_encode($hashValue);
		// monta a tag da assinatura digital
		$Signature = $createNS ? $xmldoc->createElementNS("http://www.w3.org/2000/09/xmldsig#", 'Signature') : $xmldoc->createElement($ns . 'Signature');
		if (! $appendTag) {
			$root->appendChild($Signature);
		}
		else {
			$appendNode = $xmldoc->getElementsByTagName($appendTag)->item(0);
			$appendNode->appendChild($Signature);
		}
		$SignedInfo = $xmldoc->createElement($ns . 'SignedInfo');
		$Signature->appendChild($SignedInfo);
		// Cannocalization
		$newNode = $xmldoc->createElement($ns . 'CanonicalizationMethod');
		$SignedInfo->appendChild($newNode);
		$newNode->setAttribute('Algorithm', "http://www.w3.org/TR/2001/REC-xml-c14n-20010315");
		// SignatureMethod
		$newNode = $xmldoc->createElement($ns . 'SignatureMethod');
		$SignedInfo->appendChild($newNode);
		$newNode->setAttribute('Algorithm', "http://www.w3.org/2000/09/xmldsig#rsa-sha1");
		// Reference
		$Reference = $xmldoc->createElement($ns . 'Reference');
		$SignedInfo->appendChild($Reference);
		if (empty($id)) {
			$Reference->setAttribute('URI', '');
		}
		else {
			$Reference->setAttribute('URI', '#' . $id);
		}
		// Transforms
		$Transforms = $xmldoc->createElement($ns . 'Transforms');
		$Reference->appendChild($Transforms);
		// Transform
		$newNode = $xmldoc->createElement($ns . 'Transform');
		$Transforms->appendChild($newNode);
		$newNode->setAttribute('Algorithm', "http://www.w3.org/2000/09/xmldsig#enveloped-signature");
		// Transform
		$newNode = $xmldoc->createElement($ns . 'Transform');
		$Transforms->appendChild($newNode);
		$newNode->setAttribute('Algorithm', "http://www.w3.org/TR/2001/REC-xml-c14n-20010315");
		// DigestMethod
		$newNode = $xmldoc->createElement($ns . 'DigestMethod');
		$Reference->appendChild($newNode);
		$newNode->setAttribute('Algorithm', "http://www.w3.org/2000/09/xmldsig#sha1");
		// DigestValue
		$newNode = $xmldoc->createElement($ns . 'DigestValue', $digValue);
		$Reference->appendChild($newNode);
		// extrai os dados a serem assinados para uma string
		$dados = $SignedInfo->C14N(false, false, NULL, NULL);
		// inicializa a variavel que irá receber a assinatura
		$signature = '';
		// executa a assinatura digital usando o resource da chave privada
		$resp = openssl_sign($dados, $signature, $pkeyid);
		// codifica assinatura para o padrao base64
		$signatureValue = base64_encode($signature);
		// SignatureValue
		$newNode = $xmldoc->createElement($ns . 'SignatureValue', $signatureValue);
		$Signature->appendChild($newNode);
		// KeyInfo
		$KeyInfo = $xmldoc->createElement($ns . 'KeyInfo');
		$Signature->appendChild($KeyInfo);
		// X509Data
		$X509Data = $xmldoc->createElement($ns . 'X509Data');
		$KeyInfo->appendChild($X509Data);
		// carrega o certificado sem as tags de inicio e fim
		$cert = $this->cleanCerts(file_get_contents($this->certPubKey));
		// X509Certificate
		$newNode = $xmldoc->createElement($ns . 'X509Certificate', $cert);
		$X509Data->appendChild($newNode);
		// grava na string o objeto DOM
		$docxml = $xmldoc->saveXML($firstChild ? $xmldoc->firstChild : null);

		if(version_compare(PHP_VERSION, '8.0') < 0){
			// libera a memoria
			openssl_free_key($pkeyid);
		}

		// retorna o documento assinado
		return $docxml;
	}


	/**
	 * loadPfx
	 * Carrega um novo certificado no formato PFX
	 * Isso deverá ocorrer a cada atualização do certificado digital, ou seja,
	 * pelo menos uma vez por ano, uma vez que a validade do certificado
	 * é anual.
	 * Será verificado também se o certificado pertence realmente ao CNPJ
	 * indicado na instanciação da classe, se não for um erro irá ocorrer e
	 * o certificado não será convertido para o formato PEM.
	 * Em caso de erros, será retornado false e o motivo será indicado no
	 * parâmetro error da classe.
	 * Os certificados serão armazenados como <CNPJ>-<tipo>.pem
	 * 
	 * @param string $pfxContent arquivo PFX
	 * @param string $password Senha de acesso ao certificado PFX
	 * @param boolean $createFiles se true irá criar os arquivos pem das chaves digitais, caso contrario não
	 * @param bool $ignoreValidity
	 * @return bool
	 */
	public function loadPfx( $pfxContent = '', $password = '', $createFiles = true, $ignoreValidity = false, $pathFiles = '', $nameFiles = '') {
			if ($password == '') {
				throw new \Exception(
					"A senha de acesso para o certificado pfx não pode ser vazia."
					);
			}
			//carrega os certificados e chaves para um array denominado $x509certdata
			$x509certdata = array();
			if (!openssl_pkcs12_read($pfxContent, $x509certdata, $password)) {
				throw new \Exception("O certificado não pode ser lido!! Senha errada ou arquivo corrompido ou formato inválido!!");
			}
			$this->pfxCert = $pfxContent;
			if (!$ignoreValidity) {
				//verifica sua data de validade
				if (! $this->validCerts($x509certdata['cert'])) {
					throw new \Exception($this->error);
				}
			}

			//monta o path completo com o nome da chave privada
			$nameFiles .= !empty($nameFiles) ? '_': '';
			$priKeyFile = $pathFiles.$nameFiles.'priKEY.pem';
			//monta o path completo com o nome da chave publica
			$pubKeyFile =  $pathFiles.$nameFiles.'pubKEY.pem';
			//monta o path completo com o nome do certificado (chave publica e privada) em formato pem
			$certKeyFile = $pathFiles.$nameFiles.'certKEY.pem';
			//$this->zRemovePemFiles();
			if ($createFiles) {
				file_put_contents($priKeyFile, $x509certdata['pkey']);
				file_put_contents($pubKeyFile, $x509certdata['cert']);
				file_put_contents($certKeyFile, $x509certdata['pkey']."\r\n".$x509certdata['cert']);
			}
			//$this->pubKey=$x509certdata['cert'];
			//$this->priKey=$x509certdata['pkey'];
			//$this->certKey=$x509certdata['pkey']."\r\n".$x509certdata['cert'];
			return $x509certdata;
	}

	/**
	 * Cria um diretório
	 *
	 * @param string $path
	 * @return boolean
	 */
	public function mkdir($path){
		if(!is_dir($path)){

			if(!mkdir($path, 0777, true)){
				return false;
			}
			else{
				return true;
			}
		}
		else
			return true;
	}

	protected function createTempFiles($pfx, $pwd, $nameFiles = '', array &$aReturn = array()){

		$this->pathTempFiles = dirname(__FILE__) . '/temp/';
		$this->mkdir($this->pathTempFiles);
		$this->loadPfx(file_get_contents($pfx), $pwd, true, isset($aReturn["isHomologacao"]) && $aReturn["isHomologacao"] === true, $this->pathTempFiles, $nameFiles);

		$aReturn['privKey'] = $this->pathTempFiles . $nameFiles . '_priKEY.pem';
		$aReturn['pubKey'] = $this->pathTempFiles . $nameFiles . '_pubKEY.pem';
		$aReturn['certKey'] = $this->pathTempFiles . $nameFiles . '_certKEY.pem';

		$this->deleteTempFiles = true;

		return $aReturn;
	}

	/**
	 * Verifica a data de validade do certificado digital
	 * e compara com a data de hoje.
	 * Caso o certificado tenha expirado o mesmo será removido das
	 * pastas e o método irá retornar false.
	 * @param string $pubKey chave publica
	 * @return boolean
	 */
	private function validCerts($pubKey)
	{
		if (! $data = openssl_x509_read($pubKey)) {
			//o dado não é uma chave válida
			throw new \Exception("A chave passada está corrompida ou não é uma chave. Obtenha s chaves corretas!!");
		}

		$certData = openssl_x509_parse($data);
		// reformata a data de validade;
		$ano = substr($certData['validTo'], 0, 2);
		$mes = substr($certData['validTo'], 2, 2);
		$dia = substr($certData['validTo'], 4, 2);
		//obtem o timestamp da data de validade do certificado
		$dValid = gmmktime(0, 0, 0, $mes, $dia, $ano);
		// obtem o timestamp da data de hoje
		$dHoje = gmmktime(0, 0, 0, date("m"), date("d"), date("Y"));
		// compara a data de validade com a data atual
		$this->expireTimestamp = $dValid;
		if ($dHoje > $dValid)
			throw  new \Exception("Data de validade vencida! [Valido até $dia/$mes/$ano]");

		return true;
	}

	/**
	 *
	 * Função retirada do projeto nfephp-org
	 * @link https://github.com/nfephp-org
	 *
	 * @param string $cert
	 */
	public function cleanCerts($cert) {
		// inicializa variavel
		$data = '';
		// carrega o certificado em um array usando o LF como referencia
		$arCert = explode("\n", $cert);
		foreach ($arCert as $curData) {
			// remove a tag de inicio e fim do certificado
			if (strncmp($curData, '-----BEGIN CERTIFICATE', 22) != 0 && strncmp($curData, '-----END CERTIFICATE', 20) != 0) {
				// carrega o resultado numa string
				$data .= trim($curData);
			}
		}
		return $data;
	}

	/**
	 *
	 * Função retirada do projeto nfephp-org
	 * @link https://github.com/nfephp-org
	 *
	 * @param string $url
	 * @param string $data
	 * @param array $parametros
	 * @param number $port
	 * @param array $proxy
	 */
	public function curl($url, $data = '', array $parametros = null, $port = 443, array $proxy = null) {

		// incializa cURL
		$oCurl = curl_init();
		// setting da seção soap
		if (! is_null($proxy)) {
			curl_setopt($oCurl, CURLOPT_HTTPPROXYTUNNEL, 1);
			curl_setopt($oCurl, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
			curl_setopt($oCurl, CURLOPT_PROXY, $proxy['ip'] . ':' . $proxy['port']);
			if (isset($proxy['pass'])) {
				curl_setopt($oCurl, CURLOPT_PROXYUSERPWD, $proxy['user'] . ':' . $proxy['pass']);
				curl_setopt($oCurl, CURLOPT_PROXYAUTH, CURLAUTH_BASIC);
			} // fim if senha proxy
		} // fim if aProxy
		  // força a resolusão de nomes com IPV4 e não com IPV6, isso
		  // pode acelerar temporáriamente as falhas ou demoras decorrentes de
		  // ambiente mal preparados como os da SEFAZ GO, porém pode causar
		  // problemas no futuro quando os endereços IPV4 deixarem de ser usados
		curl_setopt($oCurl, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
		curl_setopt($oCurl, CURLOPT_CONNECTTIMEOUT, 10);
		curl_setopt($oCurl, CURLOPT_URL, $url);
		curl_setopt($oCurl, CURLOPT_VERBOSE, 1);
		// curl_setopt($oCurl, CURLOPT_HEADER, 1);
		// caso não seja setado o protpcolo SSL o php deverá determinar
		// o protocolo correto durante o handshake.
		// NOTA : poderão haver alguns problemas no futuro se algum serividor não
		// estiver bem configurado e não passar o protocolo correto durante o handshake
		// nesse caso será necessário setar manualmente o protocolo correto
		// curl_setopt($oCurl, CURLOPT_SSLVERSION, 0); //default
		// curl_setopt($oCurl, CURLOPT_SSLVERSION, 1); //TLSv1
		// curl_setopt($oCurl, CURLOPT_SSLVERSION, 2); //SSLv2
		// curl_setopt($oCurl, CURLOPT_SSLVERSION, 3); //SSLv3
		// curl_setopt($oCurl, CURLOPT_SSLVERSION, 4); //TLSv1.0
		// curl_setopt($oCurl, CURLOPT_SSLVERSION, 5); //TLSv1.1
		// curl_setopt($oCurl, CURLOPT_SSLVERSION, 6); //TLSv1.2
		// se for passado um padrão diferente de zero (default) como protocolo ssl
		// esse novo padrão deverá se usado
		if ($this->sslProtocol !== 0) {
			curl_setopt($oCurl, CURLOPT_SSLVERSION, $this->sslProtocol);
		}
		curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, 2);
		curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, 0);
		if ($port == 443) {
			curl_setopt($oCurl, CURLOPT_PORT, 443);
			curl_setopt($oCurl, CURLOPT_SSLCERT, $this->certPubKey);
			curl_setopt($oCurl, CURLOPT_SSLKEY, $this->certPrivKey);
		}
		else {
			$agent = 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 1.0.3705; .NET CLR 1.1.4322)';
			curl_setopt($oCurl, CURLOPT_USERAGENT, $agent);
		}
		curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);

		if ($data != '') {
			curl_setopt($oCurl, CURLOPT_POST, 1);
			curl_setopt($oCurl, CURLOPT_POSTFIELDS, $data);
		}

		if (! is_null($parametros))
			curl_setopt($oCurl, CURLOPT_HTTPHEADER, $parametros);

			// inicia a conexão
		$resposta = curl_exec($oCurl);

		// obtem as informações da conexão
		$info = curl_getinfo($oCurl);
		// carrega os dados para debug
		// $this->zDebug($info, $data, $resposta);
		// $this->errorCurl = curl_error($oCurl);
		echo curl_error($oCurl);
		// fecha a conexão
		curl_close($oCurl);
		// retorna resposta
		return $resposta;
	}

	public function getSoap($wsdl, array $options = null){

		$options = is_null($options) ? array() : $options;

		$options['features'] = !isset($options['features']) ? SOAP_SINGLE_ELEMENT_ARRAYS : $options['features'];
		$options['local_cert'] = !isset($options['local_cert']) ? $this->certKey : $options['local_cert'];

		if(IS_DEVELOPMENT){
			$options['trace'] = !isset($options['trace']) ? 1 : $options['trace'];
			$options['exceptions'] = !isset($options['exceptions']) ? true : $options['exceptions'];
			$options['cache_wsdl'] = !isset($options['cache_wsdl']) ? WSDL_CACHE_NONE : $options['cache_wsdl'];
		}

		return $this->soapClient = new \SoapClient($wsdl, $options);
	}

	public function soap($wsdl, $url, $action, $data, $version = '1.1'){
		$options = $version == "1.2" ? array('soap_version' => SOAP_1_2) : array('soap_version' => SOAP_1_1);
		return $this->getSoap($wsdl, $options)->__doRequest($data, $url, $action, $version);
	}

	/**
	 * @return number $sslProtocol
	 */
	public function getSslProtocol() {

		return $this->sslProtocol;
	}

	/**
	 * @return \SoapClient $soapClient
	 */
	public function getLastSoap() {

		return $this->soapClient;
	}

	/**
	 * @param number $sslProtocol
	 */
	public function setSslProtocol($sslProtocol) {
		$this->sslProtocol = $sslProtocol;
	}

	public function __destruct(){
		if ($this->deleteTempFiles){

			$aFiles = glob($this->pathTempFiles . '*');

			foreach ($aFiles as $file)
				unlink($file);
		}
	}
}
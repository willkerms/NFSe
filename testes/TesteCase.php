<?php
define("IS_DEVELOPMENT", true);

class TesteCase{

	public static $path;

	public static function main(){
		self::$path = pathinfo(__FILE__, PATHINFO_DIRNAME);

		if(!is_dir(self::$path . '/logs'))
			mkdir(self::$path . '/logs');

		self::setAutoLoad();
	}

	private static function setAutoLoad(){

		spl_autoload_extensions(".php");
		spl_autoload_register(function($class){
			$path = self::$path . "/../../";
			$class = str_replace('\\', '/', $class);
			$found = stream_resolve_include_path( $path . $class . ".php");

			if($found !== false)
				require_once $found;
		});
	}
}
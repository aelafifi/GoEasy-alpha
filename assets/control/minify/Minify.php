<?php

foreach (glob(dirname(__FILE__) . '/*.php') as $__FN) {
	if ($__FN != __FILE__) {
		require_once $__FN;
	}
}

require_once dirname(__FILE__) . '/../Less/Autoloader.php';
Less_Autoloader::register();

class Minify {

	public static function js($contents) {
		return JSMinPlus::minify($contents);
	}

	public static function css($contents, $use_filters=true) {
		# Solving Some Issue
		$contents = preg_replace('/(@.*?)\{/', '$1 {', $contents);
		return CssMin::minify($contents, [
			'ConvertLevel3AtKeyframes' => true,
			'ConvertLevel3Properties'  => true
		]);
	}

	public static function flip($contents) {
		$flipper = new CssFlipper($contents);
		return $flipper->flip();
	}

	public static function less($contents) {
		$parser = new Less_Parser(['compress' => true]);
		$parser->parse($contents);
		$css = $parser->getCss();
		return $css;
	}

	public static function less_file($filepath) {
		$parser = new Less_Parser(array(
			'compress' => true,
			'import_callback' => function($evald) {
				return array( $evald->currentFileInfo['rootpath'] . $evald->path->value, '' );
			}
		));
		$parser->parseFile($filepath);
		return $parser->getCss();
	}

}

?>
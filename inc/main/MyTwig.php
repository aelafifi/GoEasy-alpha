<?php

class MyTwig {

	private static $functions = array();
	private static $filters   = array();

	public static function getTwig($root) {
		$loader = new Twig_Loader_Filesystem($root);
		$twig = new Twig_Environment($loader, array(
			'debug' => true
		));
		$twig->addExtension(new Twig_Extension_Debug());
		$twig->addExtension(new Twig_Extension_StringLoader());

		$twig->addExtension(new Twig_Extension_Custom_MyExtension());

		$twig->addExtension(new Twig_Extension_Custom_Jasny_DateExtension());
		$twig->addExtension(new Twig_Extension_Custom_Jasny_ArrayExtension());
		$twig->addExtension(new Twig_Extension_Custom_Jasny_TextExtension());
		$twig->addExtension(new Twig_Extension_Custom_Jasny_PcreExtension());

		foreach ((array) self::$functions as $key => $value) {
			$twig->addFunction(new Twig_SimpleFunction($key, $value));
		}

		foreach ((array) self::$filters as $key => $value) {
			$twig->addFilter(new Twig_SimpleFilter($key, $value));
		}

		return $twig;
	}

	public static function render($root, $file, $data=[]) {
		$twig = self::getTwig($root);
		return $twig->render($file, $data);
	}

	public static function renderBlock($root, $file, $block, $data=[]) {
		$twig = self::getTwig($root);
		$template = $twig->loadTemplate($file);
		return $template->renderBlock($block, $data);
	}

	public static function addFunction($name, $function) {
		self::$functions[$name] = $function;
	}

	public static function addFilter($name, $filter) {
		self::$filters[$name] = $filter;
	}

}

?>
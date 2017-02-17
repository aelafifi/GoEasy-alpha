<?php

class _AssetsController {

	function __construct($filename, $ext, $mime=null, $category=null) {
		chdir("..");
		// call by extension
		if (method_exists($this, $ext)) {
			return call_user_func_array([$this, $ext], func_get_args());
		}
		// call by category
		if (!is_null($category) && method_exists($this, $category)) {
			return call_user_func_array([$this, $category], func_get_args());
		}
		readfile($filename);
	}

	function css($filename) {
		include_once dirname(__FILE__) . "/minify/Minify.php";
		$css = (substr($filename, -8) == ".min.css") ?
				file_get_contents($filename) :
				Minify::css(file_get_contents($filename));
		echo isset($_GET['flip']) ? Minify::flip($css) : $css;
	}

	function less($filename) {
		include_once dirname(__FILE__) . "/minify/Minify.php";
		header("Content-Type: text/css");
		$css = Minify::css(Minify::less_file($filename));
		echo isset($_GET['flip']) ? Minify::flip($css) : $css;
	}

	function js($filename) {
		include_once dirname(__FILE__) . "/minify/Minify.php";
		if (substr($filename, -7) == ".min.js") {
			echo file_get_contents($filename);
		} else {
			echo Minify::js(file_get_contents($filename));
		}
	}

	function bc($filename) {
		require_once dirname(__FILE__) . '/barcode.php';
		barcode("", substr($filename, 0, -3), 20, "horizontal", "code128", true);
	}

}

?>
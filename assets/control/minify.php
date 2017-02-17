<?php

class Minify {

	static function html_minify($html) {
		require_once dirname(__FILE__) . '/minify/htmlmin.php';
		return Minify_HTML::minify($html, array(
			'jsMinifier' => 'Minify::js_minify',
			'cssMinifier' => 'Minify::css_minify' ));
	}

	static function js_minify($js) {
		require_once dirname(__FILE__) . '/minify/JSMinPlus.php';
		return JSMinPlus::minify($js);
	}

	static function css_minify($css, $css3=true) {
		require_once dirname(__FILE__) . '/minify/CssMin.php';
		$filters = $css3 ? array(
				'ConvertLevel3AtKeyframes' => true,
				'ConvertLevel3Properties' => true
			) : array();
		# Solving Some Issue
		$css = preg_replace('/(@.*?)\{/', '$1 {', $css);
		return CssMin::minify($css, $filters);
	}

	static function css_flip($css) {
		return preg_replace_callback("/(\{)([^{}]*?)(\})/", function($m) {
			return $m[1] . preg_replace_callback("/(^\s*|\s*;\s*)([\s\S]*?\S+?[\s\S]*?)(?=\s*$|\s*;\s*)/", function($n) {
				list($attr, $delim, $value) = preg_split("/(\s*:\s*)/", $n[2], 2, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
				$attr = preg_replace_callback("/^(\w+-)?(right|left)((-\w+)?)$/", function($m) {
					return $m[1] . strtr($m[2], ["right" => "left", "left" => "right"]) . $m[3];
				}, $attr);
				$value = preg_replace_callback("/^([\d\w%]+)(\s+)([\d\w%]+)(\s+)([\d\w%]+)(\s+)([\d\w%]+)(\s*)(!important)?$/", function($m) {
					if ($m[3] != $m[7]) {
						return $m[1] . $m[2] . $m[7] . $m[4] . $m[5] . $m[6] . $m[3] . $m[8];
					}
					return $m[0];
				}, $value);
				if ($attr == "direction") {
					$value = strtr($value, ["rtl" => "ltr", "ltr" => "rtl"]);
				} elseif (0 === strpos($value, "right") || 0 === strpos($value, "left")) {
					$value = strtr($value, ["right" => "left", "left" => "right"]);
				}
				return $n[1] . $attr . $delim . $value;
			}, $m[2]) . $m[3];
		}, $css);
	}

	private static function get_parser($readfile_function) {
		require_once dirname(__FILE__) . '/Less/Autoloader.php';
		Less_Autoloader::register();
		$options = array(
				'compress' => true,
				'readfile_function' => $readfile_function
			);
		return new Less_Parser($options);
	}

	static function less_parse($less, $readfile_function="file_get_contents") {
		$parser = self::get_parser($readfile_function);
		$parser->parse($less);
		return $parser->getCss();
	}

	static function less_parseFile($filename, $readfile_function="file_get_contents") {
		$parser = self::get_parser($readfile_function);
		$parser->parseFile($filename);
		return $parser->getCss();
	}

}

?>
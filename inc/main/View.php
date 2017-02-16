<?php

class View
{

	private static $minify_html = true;

	public static $twig_dirs = ['View'];

	public static function placehold($data) {
		$data = preg_replace('/@lorembr/', "Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod<br/>" .
			"tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam,<br/>" .
			"quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo<br/>" .
			"consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse<br/>" .
			"cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non<br/>" .
			"proident, sunt in culpa qui officia deserunt mollit anim id est laborum.", $data);
		$data = preg_replace('/@lorem/', "Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod\n" .
			"tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam,\n" .
			"quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo\n" .
			"consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse\n" .
			"cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non\n" .
			"proident, sunt in culpa qui officia deserunt mollit anim id est laborum.", $data);
		$data = preg_replace('/@line3x/', 'Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod', $data);
		$data = preg_replace('/@line/', 'Lorem ipsum dolor sit amet', $data);
		$data = preg_replace('/@(\d+x\d+)/', 'http://placehold.it/$1', $data);
		$data = preg_replace('/~\//', $_GET['base_url'] . '/', $data);
		return $data;
	}

	public static function minifyHtml($contents) {
		return Minify_HTML::minify($contents, [
			'jsMinifier'  => function($contents) {
				return JSMinPlus::minify($contents);
			},
			'cssMinifier' => function($contents) {
				$parser = new Less_Parser(['compress' => true]);
				$parser->parse($contents);
				$less2css = $parser->getCss();
				$minified_css = CssMin::minify($less2css, [
					'ConvertLevel3AtKeyframes' => true,
					'ConvertLevel3Properties'  => true
				]);
				return $minified_css;
			}
		]);
	}

	static function render_get($file, $data=[]) {
		$code = MyTwig::render(self::$twig_dirs, $file, (array) $data);
		$code = self::placehold($code);
		if ( self::$minify_html ) {
			$code = self::minifyHtml($code);
		}
		return $code;
	}

	static function render($file, $data=[]) {
		echo self::render_get($file, $data);
	}

	static function getRenderBlock($file, $block, $data=[]) {
		$code = MyTwig::renderBlock(self::$twig_dirs, $file, $block, (array) $data);
		$code = self::placehold($code);
		if ( self::$minify_html ) {
			$code = self::minifyHtml($code);
		}
		return $code;
	}

	static function renderBlock($file, $block, $data=[]) {
		echo self::getRenderBlock($file, $block, $data);
	}

	public static function renderForAjax($file, $data = [])
	{
		if (Request::is_ajax()) {
			return self::renderBlock($file, 'ajax', $data);
		}
		return self::render($file, $data);
	}

	static function render_only($file, $data=[]) {
		@ob_get_clean();
		exit(self::render_get($file, $data));
	}

	static function mail($from, $to, $subject, $view, $data=[]) {
		$headers  = "Content-Type: text/html; charset=UTF-8\r\n";
		$headers .= "MIME-Version: 1.0\r\n";
		if ( $from ) {
			$headers .= "From: " . strip_tags($from) . "\r\n";
			$headers .= "Reply-To: " . strip_tags($from) . "\r\n";
		}
		$message = self::render_get($view, $data);
		return mail($to, $subject, $message, $headers);
	}

}

?>
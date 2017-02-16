<?php

class Error {

	function __construct($arg=null) {
		self::err_404($arg);
	}

	static function __callStatic($class, $args) {
		self::err_404();
	}

	static function err_404($arg=null) {
		Data::set("err_arg", $arg);
		if ( Request::is_ajax() ) {
			exit('Some Error Occurred');
		} else {
			View::render_only('404.html');
		}
	}

}

?>
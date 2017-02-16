<?php

class Request {

	static $data;
	static $headers;

	static function __callStatic($name, $args) {
		return @self::$data[$name];
	}

	static function headers() {
		if ( !function_exists('getallheaders') ) {
			$headers = array();
			foreach ($_SERVER as $name => $value) {
				if (substr($name, 0, 5) == 'HTTP_') {
					$headers[str_replace(' ', '-',
					ucwords(strtolower(str_replace(
						'_', ' ', substr($name, 5)))))] = $value;
				}
			}
			return $headers;
		}
		return getallheaders();
	}

}

Request::$data['is_ajax'] = Request::$data['ajax'] =
	strtolower(@$_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
Request::$data['is_post'] = strtolower(@$_SERVER['REQUEST_METHOD']) == 'post';
Request::$data['is_get']  = strtolower(@$_SERVER['REQUEST_METHOD']) == 'get';
Request::$data['headers'] = Request::$headers = Request::headers();
Data::set('request', Request::$data);

?>
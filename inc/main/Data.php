<?php

class Data {

	private static $session_path = null;

	public static $data = [];

	public static function get($key=null) {
		if (is_null($key)) {
			return (array) self::$data;
		}
		return @self::$data[$key];
	}

	public static function set($key, $value) {
		return (self::$data[$key] = $value);
	}

	public static function extend($arr) {
		self::$data = array_merge((array) self::$data, (array) $arr);
	}

	public static function del($key) {
		unset(self::$data[$key]);
	}

	private static function getAppKey($key) {
		return 'key_' . md5($base_url . $key);
	}

	public static function session($key, $value=null) {
		$key = self::getAppKey($key);
		if (session_id() == '') {
			if (self::$session_path) {
				session_save_path(self::$session_path);
			}
			session_start();
		}
		if (func_num_args() == 1) {
			return @$_SESSION[$key];
		} elseif (is_null($value)) {
			unset($_SESSION[$key]);
		} else {
			return ($_SESSION[$key] = $value);
		}
	}

	const SECONDS = 1;
	const MINUTES = 60;
	const HOURS   = 3600;
	const DAYS    = 86400;
	const WEEKS   = 604800;
	const MONTHS  = 2592000;
	const YEARS   = 31104000;

	public static function cookie($key, $value=null, $exp=self::MONTHS) {
		$key = self::getAppKey($key);
		if (func_num_args() == 1) {
			return @$_COOKIE[$key];
		} elseif (is_null($value)) {
			unset($_COOKIE[$key]);
			setcookie($key, '', time() - self::HOURS);
		} else {
			return setcookie($key, $value, $exp);
		}
	}

}

$_SERVER;	// init data
Data::extend($GLOBALS);
Data::set('base_url', $_GET['base_url']);
Data::set('current_url', $_GET['current_url']);

?>
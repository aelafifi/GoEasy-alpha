<?php

class Fetch {

	static $separator = ',';

	static function _split($str, $sep) {
		$array = explode($sep, $str);
		$filtered_array = array_filter($array);
		$trimmed_array = array_map('trim', $filtered_array);
		return $trimmed_array;
	}

	static function all($keys, $array=null) {
		$array = is_null($array) ? $_REQUEST : $array;
		$keys = self::_split($keys, self::$separator);
		$ret = array();
		foreach ($keys as $key) {
			$ret[$key] = @$array[$key];
		}
		return $ret;
	}

	static function strict($keys, $array=null) {
		$array = is_null($array) ? $_REQUEST : $array;
		$keys = self::_split($keys, self::$separator);
		$ret = array();
		foreach ($keys as $key) {
			if ( isset($array[$key]) ) {
				$ret[$key] = $array[$key];
			}
		}
		return $ret;
	}

}

?>
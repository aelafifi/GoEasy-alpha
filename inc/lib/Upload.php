<?php

class Upload {

	// static $dirs = array(
	// 	'up' => 'www/assets/up',
	// 	'career' => 'www/assets/career',
	// );

	// static function __callStatic($name, $args) {
	// 	list($func, $dir) = explode('_', $name);
	// 	$args[] = self::$dirs[$dir];
	// 	return call_user_func_array(__CLASS__ . '::' . $func, $args);
	// }

	private static newName($name) {
		// 
	}

	static function one($key, $dir) {
		if ( !isset($_FILES[$key]) || $_FILES[$key]['error'] != 0 ) {
			return false;
		}
		$file = $_FILES[$key];
		$filename = uniqid();
		if ( preg_match('/\.[^\.]+$/', $file['name'], $match) ) {
			$filename .= $match[0];
		}
		$m = @move_uploaded_file($file['tmp_name'], $dir . '/' . $filename);
		return $m ? $filename : false;
	}

	static function normalize($key) {
		$args = array_merge(array(null), $_FILES[$key]);
		$files = call_user_func_array('array_map', $args);
		return array_map(function($item) {
			return array_combine(explode(',', 'name,type,tmp_name,error,size'), $item);
		}, $files);
	}

	static function many($key, $dir) {
		if ( !isset($_FILES[$key]) ) {
			return false;
		}
		$data = array();
		foreach (self::normalize($key) as $file) {
			if ( $file['error'] != 0 ) {
				$data[] = false;
				continue;
			}
			$filename = uniqid();
			if ( preg_match('/\.[^\.]+$/', $file['name'], $match) ) {
				$filename .= $match[0];
			}
			$m = @move_uploaded_file($file['tmp_name'], $dir . '/' . $filename);
			$data[] = $m ? $filename : false;
		}
		return $data;
	}

}

?>
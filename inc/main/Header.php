<?php

class Header {

	static function loc($path='') {
		exit( header('Location: ' . (
			$_GET['base_url'] ?: ''
		) . '/' . $path) );
	}

	static function refresh($s, $url=null) {
		header('Refresh: ' . $s . ( $url ?
			'; url=' . ($_GET['base_url'] ?: '') . '/' . $url : ''
		));
	}

	static function ctype($path) {
		header_remove('Content-Type');
		header('Content-Type: ' . $path);
	}

	static function download($filename=null) {
		header('Content-Disposition: attachment' . ( $filename ?
			'; filename="' . $filename . '"' : ''
		));
	}

	static function x($key, $value) {
		$key = str_replace(" ", "-", ucwords(str_replace("-", " ", $key)));
		if (is_null($value)) {
			return header_remove($key);
		}
		return header("X-" . $key . ": " . $value);
	}

}

?>
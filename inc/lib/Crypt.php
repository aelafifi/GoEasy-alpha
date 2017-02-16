<?php

class Crypt {

	static function hash() {
		return md5(implode('0', array_map('sha1', func_get_args())));
	}

	static function token($size=64) {
		$token = bin2hex(mcrypt_create_iv($size, MCRYPT_RAND));
		return substr($token, 0, $size);
	}

	static function encode($str, $key=null) {
		$key = md5($key ? $key : $str);
		$iv = mcrypt_create_iv(32, MCRYPT_RAND);
		$str = base64_encode($str);
		$ciphertext = mcrypt_encrypt(MCRYPT_RIJNDAEL_256,
			$key, $str, MCRYPT_MODE_CBC, $iv);
		return bin2hex($iv . $ciphertext);
	}

	static function decode($str, $key) {
		$key = md5($key);
		$str = hex2bin($str);
		$iv = substr($str, 0, 32);
		$str = substr($str, 32);
		$ret = mcrypt_decrypt(MCRYPT_RIJNDAEL_256,
			$key, $str, MCRYPT_MODE_CBC, $iv);
		return base64_decode($ret);
	}

	static function compare($str, $key) {
		return self::decode($str, $key) == $key;
	}

}

?>
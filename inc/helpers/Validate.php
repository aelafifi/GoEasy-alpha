<?php

trait ValidationRules_UnderTest
{

	public function mysql_date($key)
	{
		if ( !isset($_REQUEST[$key]) ) {
			return true;
		}
		return preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $_REQUEST[$key]);
	}

	public function db_record($key, $table, $col)
	{
		if ( !isset($_REQUEST[$key]) ) {
			return true;
		}
		return MySQL::table($table, $col)->one($_REQUEST[$key]);
	}

	public function db_unique($key, $table, $col)
	{
		if ( !isset($_REQUEST[$key]) ) {
			return true;
		}
		return !MySQL::table($table, $col)->one($_REQUEST[$key]);
	}

	public function latlng($key)
	{
		if ( !isset($_REQUEST[$key]) || strlen($_REQUEST[$key]) == 0 ) {
			return true;
		}
		return preg_match('/^[+-]?\d*\.?\d+,[+-]?\d*\.?\d+$/', $_REQUEST[$key]);
	}

}

class ValidationRules
{

	private $pass = [];

	public function getPassedData()
	{
		return $this->pass;
	}

	public function required($key)
	{
		return isset($_REQUEST[$key])
			&& strlen($_REQUEST[$key]) > 0;
	}

	public function email($key)
	{
		return !$this->required($key)
			|| filter_var($_REQUEST[$key], FILTER_VALIDATE_EMAIL);
	}

	public function url($key)
	{
		return !$this->required($key)
			|| filter_var($_REQUEST[$key], FILTER_VALIDATE_URL);
	}

	public function minlength($key, $len)
	{
		return !$this->required($key)
			|| strlen($_REQUEST[$key]) >= $len;
	}

	public function maxlength($key, $len)
	{
		return !$this->required($key)
			|| strlen($_REQUEST[$key]) <= $len;
	}

	public function lengthrange($key, $min, $max)
	{
		return !$this->required($key)
			|| (
				$this->minlength($key, $min) &&
				$this->maxlength($key, $max)
			);
	}

	public function equalto($key1, $key2)
	{
		return !$this->required($key1)
			|| $_REQUEST[$key1] == $_REQUEST[$key2];
	}

	public function notequalto($key1, $key2)
	{
		return !$this->required($key1)
			|| $_REQUEST[$key1] != $_REQUEST[$key2];
	}

	public function digits($key)
	{
		return !$this->required($key)
			|| preg_match('/^[0-9]+$/', $_REQUEST[$key]);
	}

	public function number($key)
	{
		return !$this->required($key)
			|| preg_match('/^[-+]?[0-9]*\.?[0-9]+$/', $_REQUEST[$key]);
	}

	public function minvalue($key, $value)
	{
		return !$this->required($key)
			|| (double) $_REQUEST[$key] >= $value;
	}

	public function maxvalue($key, $value)
	{
		return !$this->required($key)
			|| (double) $_REQUEST[$key] <= $value;
	}

	public function valuerange($key, $min, $max)
	{
		return !$this->required($key)
			|| (
				$this->minvalue($key, $min) &&
				$this->maxvalue($key, $max)
			);
	}

	private function passFile($key, $i)
	{
		foreach ($_FILES[$key] as $x => $value) {
			$this->pass['file_' . $x] = $value[$i];
		}
	}

	public function file_required($key)
	{
		if (!isset($_FILES[$key])) {
			return false;
		}
		if (is_array($_FILES[$key]['error'])) {
			return $this->file_required_multi($key);
		}
		$this->pass = $_FILES[$key];
		return $_FILES[$key]['error'] == 0;
	}

	private function file_required_multi($key)
	{
		foreach ($_FILES[$key]['error'] as $i => $error_code) {
			$this->passFile($key, $i);
			if ($error_code != 0) {
				return false;
			}
		}
		return true;
	}

	private function match_types($my_type, $up_type)
	{
		list($my0, $my1) = explode("/", $my_type);
		list($up0, $up1) = explode("/", $up_type);
		if ($my1 == '*') {
			return $my0 == $up0;
		} else {
			return $my_type == $up_type;
		}
	}

	public function file_type($key, $type)
	{
		if (!$this->file_required($key)) {
			return true;
		}
		if (is_array($_FILES[$key]['type'])) {
			return $this->file_type_multi($key, $type);
		}
		$this->pass = $_FILES[$key];
		return $this->match_types($type, $_FILES[$key]['type']);
	}

	private function file_type_multi($key, $type)
	{
		foreach ($_FILES[$key]['type'] as $i => $file_type) {
			$this->passFile($key, $i);
			if (!$this->match_types($type, $file_type)) {
				return false;
			}
		}
		return true;
	}

	public function file_maxsize($key, $size)
	{
		if (!$this->file_required($key)) {
			return true;
		}
		if (is_array($_FILES[$key]['size'])) {
			return $this->file_maxsize_multi($key, $size);
		}
		$this->pass = $_FILES[$key];
		return $_FILES[$key]['size'] <= $size;
	}

	private function file_maxsize_multi($key, $size)
	{
		foreach ($_FILES[$key]['size'] as $i => $file_size) {
			$this->passFile($key, $i);
			if ($file_size > $size) {
				return false;
			}
		}
		return true;
	}

	use ValidationRules_UnderTest;

}

class Validate
{

	private $ret;
	private $pass = [];

	public static function __callStatic($name, $args)
	{
		$rules_validator = new ValidationRules;
		$validate_controller = new self;
		$validate_controller->ret = call_user_func_array([$rules_validator, $name], $args);
		$validate_controller->pass = $rules_validator->getPassedData();
		foreach ($args as $i => $value) {
			$validate_controller->pass['arg' . $i] = $value;
		}
		return $validate_controller;
	}

	public function __call($name, $args)
	{
		if ($this->ret) {
			return $this;
		}
		$rules_validator = new ValidationRules;
		$this->ret = call_user_func_array([$rules_validator, $name], $args);
		$this->pass = $rules_validator->getPassedData();
		foreach ($args as $i => $value) {
			$this->pass['arg' . $i] = $value;
		}
		return $this;
	}

	public function message($messageText, $validator=null)
	{
		if (!isset($this)) {
			return self::error($messageText, $validator);
		}
		if (!$this->ret) {
			$arr = $this->pass;
			$messageText = preg_replace_callback("/%(\w+)/", function($match) use($arr) {
				return isset($arr[$match[1]]) ? $arr[$match[1]] : $match[0];
			}, $messageText);
			self::error($messageText, $this);
		}
	}

	public static function error($messageText, $validator=null)
	{
		if (!is_null($validator)) {
			Header::x('Validate-Key', $validator->pass['arg0']);
		}
		echo $messageText;
		exit();
	}

}

?>
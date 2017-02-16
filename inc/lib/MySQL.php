<?php

class MySQL {

	private $table, $flag;

	private static $hostname = "localhost";
	private static $username = "root";
	private static $password = "";
	private static $database = "insan";
	private static $charset  = "utf8";

	protected static function conn() {
		static $conn;
		if (!$conn) {
			$hostname = self::$hostname;
			$database = self::$database;
			$charset  = self::$charset;
			$username = self::$username;
			$password = self::$password;
			$conn = new PDO("mysql:host={$hostname};dbname={$database};"
				. "charset={$charset}", $username, $password,
				[ PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'" ]);
			$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		}
		return $conn;
	}

	static function stmt($sql) {
		$opts = array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL);
		return self::conn()->prepare($sql, $opts);
	}

	static function execute($query, $args=null) {
		$args = (!is_null($args) and !is_array($args)) ?
			array_slice(func_get_args(), 1) :
			(array) $args;
		$stmt = self::stmt($query);
		$stmt->execute($args);
		return $stmt;
	}

	static function query() {
		$stmt = call_user_func_array('self::execute', func_get_args());
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}

	static function table($table) {
		return new self($table);
	}

	function __construct($table) {
		$this->table = $table;
	}

	function one($array=null) {
		$where = MySQL_help_functions::where($array);
		$query = "SELECT * FROM `{$this->table}` {$where} LIMIT 1";
		return @array_shift(self::query($query, array_values((array) $array)));
	}

	function rnd($array=null) {
		$where = MySQL_help_functions::where($array);
		$query = "SELECT * FROM `{$this->table}` {$where} ORDER BY RAND() LIMIT 1";
		return @array_shift(self::query($query, array_values((array) $array)));
	}

	function all($where=null, $order=null) {
		$where_str = MySQL_help_functions::where($where);
		$order_str = MySQL_help_functions::order($order);
		$query = "SELECT * FROM `{$this->table}` {$where_str} {$order_str}";
		return self::query($query, array_values((array) $where));
	}

	function not($where=null, $order=null) {
		$where = MySQL_help_functions::where_not($where);
		$order = MySQL_help_functions::order($order);
		$query = "SELECT * FROM `{$this->table}` {$where} {$order}";
		return self::query($query, array_values((array) $where));
	}

	function count($array=null) {
		$where = MySQL_help_functions::where($array);
		$query = "SELECT COUNT(*) `count` FROM `{$this->table}` {$where}";
		$first_row = @array_shift(self::query($query, array_values((array) $array)));
		return $first_row['count'];
	}

	function insert($data=null) {
		$cols = MySQL_help_functions::map_implode('`%s`', ', ', array_keys((array) $data));
		$vals = implode(', ', array_fill(0, count((array) $data), '?'));
		$query = "INSERT INTO `{$this->table}` ({$cols}) VALUES ({$vals})";
		self::execute($query, array_values((array) $data));
		return self::conn()->lastInsertId();
	}

	function insert_many($data=null) {
		call_user_func_array('array_map', array_merge(array(null), $data));
		$cols = MySQL_help_functions::map_implode('`%s`', ', ', array_keys(reset($data)));
		$vals = implode(', ', array_fill(0, count(reset($data)), '?'));
		$query = "INSERT INTO `{$this->table}` ({$cols}) VALUES ({$vals})";
		$stmt = self::execute($query);
		foreach ($data as $item) {
			$stmt->execute(array_values((array) $item));
		}
		return self::conn()->lastInsertId();
	}

	function update($array=null, $data=null) {
		$where = MySQL_help_functions::where($array);
		$set = MySQL_help_functions::map_implode('`%s` = ?', ', ', array_keys((array) $data));
		$query = "UPDATE `{$this->table}` SET {$set} {$where}";
		$args = array_merge(array_values((array) $data), array_values((array) $array));
		return self::execute($query, $args)->rowCount();
	}

	function delete($array=null) {
		$where = MySQL_help_functions::where($array);
		$query = "DELETE FROM `{$this->table}` {$where}";
		return self::execute($query, array_values((array) $array));
	}

	function uporin($array=null, $data=null) {
		if ($this->one($array)) {
			return $this->update($array, $data);
		}
		return $this->insert(array_replace($array, $data));
	}

}

class MySQL_help_functions {

	static function map_implode($str, $glue) {
		$func = function() use($str) {
			return vsprintf($str, func_get_args());
		};
		$args = array_slice(func_get_args(), 2);
		$array = call_user_func_array('array_map', array_merge((array) $func, $args));
		return implode($glue, $array);
	}

	static function where($array) {
		$where = self::map_implode('`%s` = ?', ' AND ', array_keys((array) $array));
		return $where ? "WHERE {$where}" : "";
	}

	static function where_not($array) {
		$where = self::map_implode('`%s` != ?', ' AND ', array_keys((array) $array));
		return $where ? "WHERE {$where}" : "";
	}

	static function order($array) {
		$order = array();
		foreach ((array) $array as $item) {
			if (!is_array($item)) {
				$item = array($item, 'ASC');
			}
			$order[] = vsprintf('`%s` %s', $item);
		}
		return $order ? "ORDER BY " . implode(', ', $order) : "";
	}

}

?>
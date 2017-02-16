<?php

class App {

	private static $aliases = [
		// 'from' => 'to'
	];

	private static function aliass($rt) {
		foreach (self::$aliases as $key => $value) {
			if (strpos($rt, $key) === 0 && (strlen($rt) == strlen($key) || $rt[strlen($key)] == '/')) {
				$rt = $value . substr($rt, strlen($key));
				break;
			}
		}
		return $rt;
	}

	static function display() {
		$rt = self::aliass(trim($_GET['rt'], "/"));
		$route_parts = array_filter(explode("/", $rt), 'strlen');
		$class = null;
		$tests = [];
		array_splice($route_parts, 0, 0, 'Controller');
		for ($i=count($route_parts)-1; $i>0; $i--) {
			$tests[] = array_slice($route_parts, 0, $i+1);
		}
		$tests[] = array_merge($route_parts, ['index']);
		$tests[] = ['Controller', 'PageNotFound'];
		foreach ($tests as $test) {
			$c_name = str_replace(" ", "_", ucwords(implode(" ", $test)));
			if (class_exists($c_name)) {
				$class = $c_name;
				break;
			}
		}
		if (is_null($class)) {
			return new Controller_PageNotFound;
		}
		$ctrl = new $class;
		$args = array_slice($route_parts, count($test));
		$method_name = array_shift($args);
		$main_method = 'main';
		if (strtolower($_SERVER['REQUEST_METHOD']) == 'post') {
			$method_name .= '_submit';
			$main_method .= '_submit';
		}
		if (method_exists($ctrl, $method_name) || method_exists($ctrl, '__call')) {
			// 
		} elseif (method_exists($ctrl, $main_method)) {
			$args = array_merge([$method_name], $args);
			$method_name = $main_method;
		} else {
			throw new Exception("Error Processing Request", 1);
		}
		return call_user_func_array([$ctrl, $method_name], $args);
	}

}

?>
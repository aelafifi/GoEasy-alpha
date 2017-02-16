<?php

spl_autoload_register(function($class) {
	$prefix = dirname(__FILE__) . '/' . str_replace(['_', "\0"], ['/', ''], $class);
	if (is_file($file = $prefix . '.php') || is_file($file = $prefix . '/__init.php')) {
		require $file;
	}
});

spl_autoload_register(function($class) {
	$array1 = glob(dirname(__FILE__) . "/inc/*/{$class}.php");
	$class2 = str_replace("_", "/", $class);
	$array2 = glob(dirname(__FILE__) . "/inc/{$class2}.php");
	$array = array_merge($array1, $array2);
	if ($array) {
		require_once $array[0];
	}
});

?>
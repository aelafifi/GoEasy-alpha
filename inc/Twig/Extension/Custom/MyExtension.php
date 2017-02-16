<?php

class Twig_Extension_Custom_MyExtension extends Twig_Extension {

	public function getFunctions() {
		return array(
			new Twig_SimpleFunction('php_*__*', function() {
				$arg_list = func_get_args();
				$class = array_shift($arg_list);
				$method = array_shift($arg_list);
				return call_user_func_array("$class::$method", $arg_list);
			}),
			new Twig_SimpleFunction('php_class_*', function() {
				$arg_list = func_get_args();
				$class = array_shift($arg_list);
				$r = new ReflectionClass($class);
				return $r->newInstanceArgs($arg_list);
			}),
			new Twig_SimpleFunction('php_*', function() {
				$arg_list = func_get_args();
				$function = array_shift($arg_list);
				return call_user_func_array($function, $arg_list);
			})
		);
	}

	public function getFilters() {
		return array(
			new Twig_SimpleFilter('strftime', function($time, $format) {
				setlocale(LC_TIME, 'ar_EG.utf8');
				return strftime($format, strtotime($time));
			}),
			new Twig_SimpleFilter('php', function($val) {
				return eval('return '.$val.';');
			})
		);
	}

	public function getGlobals() {
		return (array) Data::$data;
	}

	public function getName() {
		return 'MyExtension';
	}

}

?>
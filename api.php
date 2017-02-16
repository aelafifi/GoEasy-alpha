<?php

function nextId() {
	return 'i' . base_convert(
		hexdec(uniqid()) +
		intval(rand(1e16, 1e17-1)),
	10, 36);
}

function arrayQuery($array, $options=null)
{

	$defaults = [
		'groupBy'      => null,
		'select'       => null,
		'orderBy'      => null,
		'preserveKeys' => true,
		'selectOne'    => false
	];

	$options = array_merge($defaults, (array) $options);

	if ($options['orderBy']) {
		usort($array, function($a, $b) use($options) {
			foreach ((array) $options['orderBy'] as $key) {
				if ($not = ($key[0] == '^')) {
					$key = substr($key, 1);
				}
				if ($a[$key] > $b[$key]) {
					return $not ? -1 : 1;
				}
				else
				if ($a[$key] < $b[$key]) {
					return $not ? 1 : -1;
				}
			}
			return 0;
		});
	}

	if (!is_null($options['groupBy']) && !is_array($options['groupBy'])) {
		$options['selectOne'] = true;
	}
	$recordIsValue = (!is_null($options['select']) && !is_array($options['select']));
	$ret_array = [];
	foreach ($array as $record) {
		$selected = [];
		if ($recordIsValue) {
			$selected = $record[$options['select']];
		} else {
			if (is_null($options['select'])) {
				$selected = $record;
			} else {
				foreach ((array) $options['select'] as $i => $key) {
					$selected[$key] = $record[$key];
				}
			}
			if (!$options['preserveKeys']) {
				$selected = array_values($selected);
			}
		}
		if (is_null($options['groupBy'])) {
			$ret_array[] = $selected;
			// continue;
		} else {
			$Ks = array_map(function($i) {
				return "[\$record[" . json_encode($i) . "]]";
			}, (array) $options['groupBy']);
			$m = $options['selectOne'] ? "" : "[]";
			eval("\$ret_array" . implode("", $Ks) . "$m = \$selected;\n");
		}
	}
	return $ret_array;
}

function arrayQ($array, $groupBy=null, $select=null, $str_query='', $options=[])
{
	$ret_options = [
		'select' => $select,
		'groupBy' => $groupBy
	];
	$query_array = explode(";", $str_query);
	foreach ($query_array as $item) {
		if (preg_match("/^\s*(\^?)\s*(\w+)\s*$/", $item, $match)) {
			$ret_options[$match[2]] = !$match[1];
		} else {
			if (preg_match("/^\s*orderBy\s+(.*?)\s*$/", $item, $match)) {
				$order = explode(",", $match[1]);
				foreach ($order as &$item2) {
					if (preg_match("/^\s*(\^?)\s*(\w+?)\s*(asc|desc)?\s*$/i", $item2, $match)) {
						if ($match[1] || strtolower(@$match[3]) == 'desc') {
							$item2 = '^' . $match[2];
						} else {
							$item2 = $match[2];
						}
					} else {
						$item2 = null;
					}
				}
				$ret_options['orderBy'] = array_filter($order);
			}
		}
	}
	return arrayQuery($array, array_merge($ret_options, $options));
}

?>
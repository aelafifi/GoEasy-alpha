<?php

class CssFlipper
{

	private $css;

	public function __construct($css) {
		$this->css = $css;
	}

	public function flipBackgroundPosition($value) {
		$reLeft	= "/\bleft\b/";
		$reRight = "/\bright\b/";
		if (preg_match($reLeft, $value)) {
			$value = preg_replace($reLeft, 'right', $value);
		} elseif (preg_match($reRight, $value)) {
			$value = preg_replace($reRight, 'left', $value);
		}

		$elements = preg_split("/\s+/", $value);

		if (!$elements) {
			return $value;
		}

		$flipPercentage = function($value) {
			$rePct = "/^[+-]?\d*(?:\.\d+)?(?:[Ee][+-]?\d+)?%/";
			if (preg_match($rePct, $value)) {
				return (100 - floatval($value)) . '%';
			}
			return $value;
		};

		if (count($elements) == 1) {
			$value = $flipPercentage($elements[0]);
		} elseif (count($elements) == 2) {
			$value = $flipPercentage($elements[0]) . ' ' . $elements[1];
		}

		return $value;
	}

	public function flipBorderRadius($value) {
		$elements = preg_split("/\s*\/\s*/", $value);

		if (!$elements) {
			return $value;
		}

		$flipCorners = function($value) {
			$elements = preg_split("/\s+/", $value);

			if (!$elements) {
				return $value;
			}

			switch (count($elements)) {
				// 5px 10px 15px 20px => 10px 5px 20px 15px
				case 4:
					return implode(' ', [$elements[1], $elements[0], $elements[3], $elements[2]]);

				// 5px 10px 20px => 10px 5px 10px 20px
				case 3:
					return implode(' ', [$elements[1], $elements[0], $elements[1], $elements[2]]);

				// 5px 10px => 10px 5px
				case 2:
					return implode(' ', [$elements[1], $elements[0]]);
			}

			return $value;
		};

		switch (count($elements)) {
			// 1px 2px 3px 4px => 2px 1px 4px 3px
			case 1:
				return $flipCorners($elements[0]);

			// 1px / 2px 3px => 1px / 3px 2px
			// 1px 2px / 3px 4px => 2px 1px / 4px 3px
			// etc...
			case 2:
				return $flipCorners($elements[0]) . ' / ' . $flipCorners($elements[1]);
		}

		return $value;
	}

	public function flipBoxShadow($value) {
		$value = preg_replace_callback("/\([^)]*\)/", function($match) {
			return preg_replace("/,/", "_C_", $match[0]);
		}, $value);
		$value = preg_split("/\s*,\s*/", $value);
		$value = array_map(function($item) {
			$elements = preg_split("/\s+/", $item);

			if (!$elements) {
				return $item;
			}

			$inset = ($elements[0] == 'inset') ? array_shift($elements) . ' ' : '';
			preg_match("/^([-+]?\d+)(\w*)$/", $elements[0], $property);

			if (!$property) {
				return $item;
			}

			return $inset . implode(' ', array_merge(
				[(-1 * $property[1]) . $property[2]],
				array_splice($elements, 1)
			));
		}, $value);
		$value = implode(", ", $value);
		$value = preg_replace("/_C_/", ',', $value);
		return $value;
	}

	public function flipDirection($value) {
		return preg_match("/ltr/", $value) ? 'rtl' : (
			preg_match("/rtl/", $value) ? 'ltr' : $value
		);
	}

	// function flipProperty($prop) {
		// 	$PROPERTIES = [
		// 		'border-left' => 'border-right',
		// 		'border-bottom-right-radius' => 'border-bottom-left-radius',
		// 		'border-bottom-left-radius' => 'border-bottom-right-radius',
		// 		'border-top-right-radius' => 'border-top-left-radius',
		// 		'border-top-left-radius' => 'border-top-right-radius',
		// 		'border-left-color' => 'border-right-color',
		// 		'border-left-style' => 'border-right-style',
		// 		'border-left-width' => 'border-right-width',
		// 		'border-right' => 'border-left',
		// 		'border-right-color' => 'border-left-color',
		// 		'border-right-width' => 'border-left-width',
		// 		'border-right-style' => 'border-left-style',
		// 		'left' => 'right',
		// 		'margin-left' => 'margin-right',
		// 		'margin-right' => 'margin-left',
		// 		'padding-left' => 'padding-right',
		// 		'padding-right' => 'padding-left',
		// 		'right' => 'left'
		// 	];
		//  	$normalizedProperty = strtolower($prop);
		//  	return isset($PROPERTIES[$normalizedProperty]) ? $PROPERTIES[$normalizedProperty] : $prop;
	// }

	public function flipProperty($prop) {
		if (preg_match("/(?<=^|-|\s)left(?=$|-|\s)/", $prop)) {
			return preg_replace("/(?<=^|-|\s)left(?=$|-|\s)/", 'right', $prop);
		}
		if (preg_match("/(?<=^|-|\s)right(?=$|-|\s)/", $prop)) {
			return preg_replace("/(?<=^|-|\s)right(?=$|-|\s)/", 'left', $prop);
		}
		return $prop;
	}

	public function flipLeftRight($value) {
		return preg_match("/left/", $value) ? 'right' : (
			preg_match("/right/", $value) ? 'left' : $value
		);
	}

	public function flipQuad($value) {
		// Tokenize any rgb[a]/hsl[a] colors before flipping.
		$colors = [];
		preg_match("/(?:rgb|hsl)a?\([^\)]*\)/", $value, $matches);

		if ($matches) {
			foreach ($matches as $i => $color) {
				$colors[$i] = $color;
				$value = str_replace($color, '_C' . $i . '_', $value);
			}
		}

		$elements = preg_split("/\s+/", $value);

		if ($elements && count($elements) == 4) {
			// 1px 2px 3px 4px => 1px 4px 3px 2px
			$value = implode(' ', [$elements[0], $elements[3], $elements[2], $elements[1]]);
		}

		if ($colors) {
			// Replace any tokenized colors.
			return preg_replace_callback("/_C(\d+)_/", function($match) use($colors) {
				return $colors[$match[1]];
			}, $value);
		}

		return $value;
	}

	public function flipTransition($value) {
		$parts = preg_split("/\s*,\s*/", $value);

		$parts_map = array_map(function($part) {
			$RE_PROP = "/^\s*([a-zA-z\-]+)/";
			if (preg_match($RE_PROP, $part, $matches)) {
				$prop = $matches[1];
				$newProp = flipProperty($prop);
				$part = implode($newProp, explode($prop, $part, 2));
			}
			return $part;
		}, $parts);


		return implode(", ", $parts_map);
	}


	public function flipValueOf($prop, $value) {
		$VALUES = [
		  'background-position' => 'flipBackgroundPosition',
		  'background-position-x' => 'flipBackgroundPosition',
		  'border-radius' => 'flipBorderRadius',
		  'border-color' => 'flipQuad',
		  'border-style' => 'flipQuad',
		  'border-width' => 'flipQuad',
		  'box-shadow' => 'flipBoxShadow',
		  'clear' => 'flipLeftRight',
		  'direction' => 'flipDirection',
		  'float' => 'flipLeftRight',
		  'margin' => 'flipQuad',
		  'padding' => 'flipQuad',
		  'text-align' => 'flipLeftRight',
		  'transition' => 'flipTransition',
		  'transition-property' => 'flipTransition'
		];

		$RE_IMPORTANT = "/\s*!important/";
		$RE_PREFIX    = "/^-[a-zA-Z]+-/";

		// find normalized property name (removing any vendor prefixes)
		$normalizedProperty = trim(strtolower($prop));
		if (preg_match($RE_PREFIX, $normalizedProperty)) {
			$normalizedPropertyArray = preg_split($RE_PREFIX, $normalizedProperty);
			$normalizedProperty = $normalizedPropertyArray[1];
		}

		$flipFn = isset($VALUES[$normalizedProperty]) ? $VALUES[$normalizedProperty] : false;

		if (!$flipFn) {
			return $value;
		}

		preg_match($RE_IMPORTANT, $value, $important);
		$newValue = call_user_func([$this, $flipFn], trim(preg_replace($RE_IMPORTANT, '', $value)), $prop);

		if ($important && !preg_match($RE_IMPORTANT, $newValue)) {
			$newValue .= $important[0];
		}

		return $newValue;
	}

	public function flip() {
		return preg_replace_callback("/(\{)([^{}]*?)(\})/s", function($matches) {
			return  $matches[1] .
					$this->flipBlock($matches[2]) .
					$matches[3];
		}, $this->css);
	}

	public function flipBlock($block) {
		return preg_replace_callback("/(?<=^|;)(\s*)(.+?)(\s*:\s*)(.+?)(?=\s*$|\s*;)/s", function($matches) {
			return  $matches[1] .
					$this->flipProperty($matches[2]) .
					$matches[3] .
					$this->flipValueOf($matches[2], $matches[4]);
		}, $block);
	}

}



// $css = "@media all {
// 	a {
// 		left: 0px !important;
// 		direction: ltr !important;
// 		padding-left: 12px !important;
// 		margin: 1 2 3 4 !important;
// 		border-width: 1 2 3 4;
// 		border-radius: 1 2 3 4;
// 	}
// }
// ";

// $flipper = new CssFlipper($css);
// echo $flipper->flip();

?>
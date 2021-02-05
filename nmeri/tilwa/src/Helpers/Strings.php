<?php

	namespace Tilwa\Helpers;

	class Strings {
		
		public static function nameCleanUp ($name) {
	 		
	 		return preg_replace_callback("/-|_|~/", function ($match) {
				
				if ($match[0] == '-') return ' ';

				elseif ($match[0] == '_')  return '-';

				return '_';
			}, $name);
	 	}

	 	// converts a string to a format callable as a method i.e. camelcase or snake cased
	 	public static function nameDirty ($name, $dirtMode) {
	 		
	 		return preg_replace_callback('/\b(\s)|(-)(\w)?/', function($a) use ($dirtMode, $name) {

		 		if ($dirtMode == 'dash-case') { // won't have any effect on strings containing underscores

		 			if (!empty($a[1])) return '-';

		 			if (!empty($a[2])) return '_' . $a[3];
		 		}

		 		elseif ($dirtMode == 'camel-case') {

		 			if (!empty($a[3])) return strtoupper($a[3]);
		 		}

		 	}, $name);
	 	}

	 	public static function kebab ($string) {
	 		
	 		return preg_replace_callback('/([a-z]+)([A-Z])/', function($m) {

				  return $m[1] . '-' . strtolower($m[2]);
				}, $string);
	 	}
	}

?>
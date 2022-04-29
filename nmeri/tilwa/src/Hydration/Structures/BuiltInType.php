<?php
	namespace Tilwa\Hydration\Structures;
	
	class BuiltInType {

		public function getDefaultValue (string $typeName) {

			$initial = null;

			settype($initial, $typeName);

			return $initial;
		}
	}
?>
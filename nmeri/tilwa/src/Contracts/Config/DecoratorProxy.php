<?php
	namespace Tilwa\Contracts\Config;

	interface DecoratorProxy extends ConfigMarker {

		public function generatedClassesLocation ():string;

		public function getConfigClient ():object;
	}
?>
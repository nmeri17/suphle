<?php
	namespace Tilwa\Contracts\Config;

	interface DecoratorProxy {

		public function generatedClassesLocation ():string;

		public function getConfigClient ():object;
	}
?>
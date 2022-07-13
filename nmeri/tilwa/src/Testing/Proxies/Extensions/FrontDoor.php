<?php
	namespace Tilwa\Testing\Proxies\Extensions;

	use Tilwa\Modules\ModuleHandlerIdentifier;

	use Tilwa\Hydration\Container;

	class FrontDoor extends ModuleHandlerIdentifier {

		public function __construct (array $descriptors) {

			$this->descriptors = $descriptors;

			parent::__construct();
		}
		
		public function getModules ():array {

			return $this->descriptors;
		}
	}
?>
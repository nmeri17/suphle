<?php
	namespace Tilwa\Testing\Proxies\Extensions;

	use Tilwa\App\{ModuleHandlerIdentifier, Container};

	class FrontDoor extends ModuleHandlerIdentifier {

		public function __construct (array $descriptors) {

			$this->descriptors = $descriptors;

			parent::__construct();
		}
		
		protected function getModules():array {

			return $this->descriptors;
		}

		protected function firstContainer ():Container {

			return $this->container;
		}
	}
?>
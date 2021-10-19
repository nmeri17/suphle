<?php
	namespace Tilwa\Testing\Proxies\Extensions;

	use Tilwa\App\ModuleHandlerIdentifier;

	class FrontDoor extends ModuleHandlerIdentifier {

		public function __construct (array $descriptors) {

			$this->descriptors = $descriptors;
		}
		
		protected function getModules():array {

			return $this->descriptors;
		}
	}
?>
<?php
	namespace Suphle\Testing\Proxies\Extensions;

	use Suphle\Modules\ModuleHandlerIdentifier;

	use Suphle\Hydration\Container;

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
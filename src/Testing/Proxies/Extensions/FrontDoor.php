<?php
	namespace Suphle\Testing\Proxies\Extensions;

	use Suphle\Modules\ModuleHandlerIdentifier;

	use Suphle\Hydration\Container;

	class FrontDoor extends ModuleHandlerIdentifier {

		public function __construct (array $descriptors) {

			$this->descriptorInstances = $descriptors;

			parent::__construct();
		}
		
		public function getModules ():array {

			return $this->descriptorInstances;
		}
		
		public function setModules (array $descriptors, bool $isSecond = false):void {

			$this->descriptorInstances = $descriptors;

			$this->container = $descriptors[0]->getContainer();
			if ($isSecond)

				var_dump(27, spl_object_hash($this->container), spl_object_hash($this->firstContainer())
			);
		}
		
		public function getScopedDescriptors ():array {

			return $this->scopedDescriptors;
		}
	}
?>
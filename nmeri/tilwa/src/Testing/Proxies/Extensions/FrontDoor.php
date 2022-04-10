<?php
	namespace Tilwa\Testing\Proxies\Extensions;

	use Tilwa\Modules\ModuleHandlerIdentifier;

	use Tilwa\Hydration\Container;

	use Tilwa\Events\ModuleLevelEvents;

	class FrontDoor extends ModuleHandlerIdentifier {

		private $eventParent;

		public function __construct (array $descriptors, ModuleLevelEvents $eventParent = null) {

			$this->descriptors = $descriptors;

			$this->eventParent = $eventParent;

			parent::__construct();
		}
		
		public function getModules ():array {

			return $this->descriptors;
		}

		public function firstContainer ():Container {

			return $this->container;
		}

		protected function getEventConnector ():ModuleLevelEvents {

			return $this->eventParent ?? parent::getEventConnector();
		}
	}
?>
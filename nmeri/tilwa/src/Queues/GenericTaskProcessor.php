<?php
	namespace Tilwa\Queues;

	use Tilwa\Modules\ModuleDescriptor;

	use Tilwa\Contracts\Queues\Adapter;

	/**
	 * Intended for use from the CLI, to be ran as a daemon for listening to and processing incoming tasks
	*/
	class GenericTaskProcessor {

		private $descriptor, $container;

		public function __construct (ModuleDescriptor $descriptor) {

			$this->descriptor = $descriptor;

			$this->container = $this->descriptor->getContainer();
		}

		public function bootModule ():self {

			$this->descriptor->warmModuleContainer();

			$this->descriptor->prepareToRun();

			return $this;
		}

		public function beginProcessing ():void {

			$this->container->getClass(Adapter::class)->processTasks();
		}
	}
?>
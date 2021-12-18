<?php
	namespace Tilwa\Queues;

	use Tilwa\App\ModuleDescriptor;

	class GenericTaskProcessor {

		private $descriptor, $container;

		public function __construct (ModuleDescriptor $descriptor) {

			$this->descriptor = $descriptor;

			$this->container = $this->descriptor->getContainer();
		}

		public function bootModule ():self {

			$this->descriptor->absorbConfigs();

			$this->container->provideSelf();

			$this->descriptor->prepareToRun();

			return $this;
		}

		public function proxyAdapterManager ():void {

			$this->container->getClass(AdapterManager::class)

			->beginProcessing();
		}
	}
?>
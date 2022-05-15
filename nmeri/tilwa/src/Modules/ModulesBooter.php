<?php
	namespace Tilwa\Modules;

	use Tilwa\Events\ModuleLevelEvents;

	use Tilwa\Contracts\Modules\DescriptorInterface;

	class ModulesBooter {

		private $modules, $eventManager;

		public function __construct (array $modules, ModuleLevelEvents $eventManager) {

			$this->modules = $modules;

			$this->eventManager = $eventManager;
		}
		
		public function getModules ():array {

			return $this->modules;
		}

		public function bootAll ():self {

			foreach ($this->modules as $descriptor) {

				$descriptor->warmModuleContainer(); // We're setting these to be able to attach events soon after

				$descriptor->getContainer()->whenTypeAny()->needsAny([

					DescriptorInterface::class => $descriptor,

					get_called_class() => $this
				]);
			}

			$this->eventManager->bootReactiveLogger();

			return $this;
		}

		/**
		 * Without this, trying to extract things like Auth/ModuleFile won't be possible since they rely on user-land bound concretes
		*/
		public function prepareFirstModule ():void {

			current($this->modules)->prepareToRun();
		}
	}
?>
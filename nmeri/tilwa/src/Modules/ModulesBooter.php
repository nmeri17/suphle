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
		
		public function boot ():void {

			foreach ($this->modules as $descriptor) {

				$descriptor->warmModuleContainer(); // We're setting these to be able to attach events soon after

				$descriptor->getContainer()->whenTypeAny()->needsAny([

					DescriptorInterface::class => $descriptor
				]);
			}

			$this->eventManager->bootReactiveLogger();
		}
	}
?>
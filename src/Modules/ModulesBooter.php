<?php
	namespace Suphle\Modules;

	use Suphle\Modules\Structures\ActiveDescriptors;

	use Suphle\Events\ModuleLevelEvents;

	use Suphle\Contracts\{Modules\DescriptorInterface, Services\Decorators\BindsAsSingleton};

	use Suphle\Hydration\Structures\BaseSingletonBind;

	class ModulesBooter implements BindsAsSingleton {

		use BaseSingletonBind;

		private $modules, $eventManager;

		public function __construct (ActiveDescriptors $descriptorsHolder, ModuleLevelEvents $eventManager) {

			$this->modules = $descriptorsHolder->getDescriptors();

			$this->eventManager = $eventManager;

			var_dump("starting mb");

			// if (\Suphle\Tests\Integration\Flows\FlowRoutesTest::$shouldThrow) throw new \Exception("Error Processing Request", 1);
			
		}
		
		public function getModules ():array { // replace this with  direct calls to new class

			return $this->modules;
		}

		public function bootAllModules ():self {

			foreach ($this->modules as $descriptor) {

				$descriptor->warmModuleContainer(); // We're setting these to be able to attach events soon after

				$descriptor->getContainer()->whenTypeAny()->needsAny([

					DescriptorInterface::class => $descriptor
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

		/**
		 * Organic behavior is for only module matching incoming request to be prepared. But occasionally, we may want to examine modular functionality without routing.
		 * 
		 * @param {skipFirst} Since the caller is likely to have prepared this in order to have access to getContainer
		*/
		public function prepareAllModules (bool $skipFirst = true):self {

			foreach ($this->modules as $index => $descriptor) {

				if ($index == 0 && $skipFirst) continue;

				$descriptor->prepareToRun();
			}

			return $this;
		}
	}
?>
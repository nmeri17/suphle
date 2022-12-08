<?php
	namespace Suphle\Modules;

	use Suphle\Modules\Structures\ActiveDescriptors;

	use Suphle\Events\ModuleLevelEvents;

	use Suphle\Contracts\{Modules\DescriptorInterface, Config\ModuleFiles};

	use Suphle\Services\Decorators\BindsAsSingleton;

	#[BindsAsSingleton]
	class ModulesBooter {

		private array $modules;

		public function __construct (

			ActiveDescriptors $descriptorsHolder,

			private readonly ModuleLevelEvents $eventManager
		) {

			$this->modules = $descriptorsHolder->getDescriptors();
		}
		
		public function getModules ():array {

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
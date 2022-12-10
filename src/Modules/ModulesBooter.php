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

			private readonly ModuleLevelEvents $eventManager
		) {

			//
		}
		
		public function getModules ():array {

			return $this->modules;
		}

		public function bootAllModules (ActiveDescriptors $descriptorsHolder):self {

			$this->modules = $descriptorsHolder->getOriginalDescriptors();

			foreach ($this->modules as $descriptor) {

				$descriptor->warmModuleContainer(); // We're setting these to be able to attach events soon after

				$descriptor->getContainer()->whenTypeAny()->needsAny([

					DescriptorInterface::class => $descriptor,

					ActiveDescriptors::class => $descriptorsHolder // before this point, any object that requires the holder has to receive it manually
				]);
			}

			$this->eventManager->bootReactiveLogger($descriptorsHolder);

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
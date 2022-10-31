<?php
	namespace Suphle\Modules;

	use Suphle\Contracts\{Modules\DescriptorInterface, Services\Decorators\BindsAsSingleton};

	use Suphle\Hydration\Structures\BaseSingletonBind;

	use Throwable;

	/**
	 * Manager/wrapper around [ModuleInitializer]
	*/
	class ModuleToRoute implements BindsAsSingleton {

		use BaseSingletonBind;

		private $activeDescriptor;
		
		public function findContext (array $descriptors):?ModuleInitializer {
			
			foreach ($descriptors as $descriptor) {

				$context = $this->safeSearchRoute( $descriptor);
				
				if ($context->didFindRoute()) {

					$this->activeDescriptor = $descriptor;

					return $context;
				}
			}

			return null;
		}

		/**
		 * @throws Throwable
		*/
		private function safeSearchRoute (DescriptorInterface $descriptor):ModuleInitializer {

			try {

				if (!$descriptor->expatriateHasPreparedExpatriates()) // avoid overwriting boted bindings

					$descriptor->prepareToRun();

				$initializer = $descriptor->getContainer()

				->getClass(ModuleInitializer::class);

				return $initializer->assignRoute();
			}
			catch (Throwable $exception) {

				echo implode("\n", [
					"Error encountered during attempt to find route on descriptor ". $descriptor::class,

					$exception
				]);

				throw $exception;
			}
		}
		
		public function getActiveModule ():?ModuleDescriptor {

			return $this->activeDescriptor;
		}
	}
?>
<?php
	namespace Suphle\Modules;

	use Suphle\Contracts\Modules\DescriptorInterface;

	use Suphle\Services\Decorators\BindsAsSingleton;

	use Throwable;

	/**
	 * Manager/wrapper around [ModuleInitializer]
	*/
	#[BindsAsSingleton]
	class ModuleToRoute {

		private ?DescriptorInterface $activeDescriptor = null;
		
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
		
		public function getActiveModule ():?DescriptorInterface {

			return $this->activeDescriptor;
		}
	}
?>
<?php
	namespace Tilwa\Modules;

	use Tilwa\Contracts\Modules\DescriptorInterface;

	use Throwable;

	/**
	 * Manager/wrapper around [ModuleInitializer]
	*/
	class ModuleToRoute {

		private $activeDescriptor;
		
		public function findContext (array $descriptors):?ModuleInitializer {
			
			foreach ($descriptors as $descriptor) {

				$context = $descriptor->getContainer()

				->getClass(ModuleInitializer::class);

				$this->safeSearchRoute($context, $descriptor);
				
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
		private function safeSearchRoute (

			ModuleInitializer $initializer, DescriptorInterface $descriptor
		):void {

			try {

				$initializer->prepareToFindRoute()->assignRoute();
			}
			catch (Throwable $exception) {

				$message = "Error encountered while attempting for find route on descriptor ". get_class($descriptor);

				echo $message;

				throw $exception;
			}
		}
		
		public function getActiveModule ():?ModuleDescriptor {

			return $this->activeDescriptor;
		}
	}
?>
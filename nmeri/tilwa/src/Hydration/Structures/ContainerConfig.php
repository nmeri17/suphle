<?php
	namespace Tilwa\Hydration\Structures;

	use Tilwa\Contracts\Config\ContainerConfig as IContainerConfig;

	use Tilwa\Bridge\Laravel\Package\LaravelProviderManager;

	class ContainerConfig implements IContainerConfig {

		/**
		 * {@inheritdoc}
		*/
		public function getExternalHydrators ():array {

			return [LaravelProviderManager::class];
		}
	}
?>
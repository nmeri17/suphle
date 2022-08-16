<?php
	namespace Suphle\Hydration\Structures;

	use Suphle\Contracts\Config\ContainerConfig as IContainerConfig;

	use Suphle\Bridge\Laravel\Package\LaravelProviderManager;

	class ContainerConfig implements IContainerConfig {

		/**
		 * {@inheritdoc}
		*/
		public function getExternalHydrators ():array {

			return [

				// LaravelProviderManager::class // commenting this out since it requires the bridge in freshly created modules
			];
		}
	}
?>
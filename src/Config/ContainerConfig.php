<?php
	namespace Suphle\Config;

	use Suphle\Contracts\Config\ContainerConfig as IContainerConfig;

	class ContainerConfig implements IContainerConfig {

		/**
		 * {@inheritdoc}
		*/
		public function getExternalHydrators ():array {

			return [];
		}
	}
?>
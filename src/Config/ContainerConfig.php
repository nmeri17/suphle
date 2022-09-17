<?php
	namespace Suphle\Config;

	use Suphle\Contracts\Config\ContainerConfig as IContainerConfig;

	class ContainerConfig implements IContainerConfig {

		public function containerLogFile ():string {

			return "container-log.txt";
		}

		/**
		 * {@inheritdoc}
		*/
		public function getExternalHydrators ():array {

			return [];
		}
	}
?>
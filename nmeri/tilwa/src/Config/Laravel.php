<?php

	namespace Tilwa\Config;

	use Tilwa\Contracts\Config\Laravel as LaravelConfig;

	class Laravel implements LaravelConfig {

		public function configBridge ():array {

			return [];
		}

		public function getProviders ():array {

			return [];
		}

		public function hasRoutes():bool {

			return true;
		}
	}
?>
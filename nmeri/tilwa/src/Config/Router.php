<?php

	namespace Tilwa\Config;

	use Tilwa\Contracts\Config\Router as RouterConfig;

	use Tilwa\Middleware\FinalHandlerWrapper;

	class Router implements RouterConfig {

		public function apiPrefix():string {

			return "api";
		}

		// should be listed in descending order of the versions
		public function apiStack ():array {}

		public function browserEntryRoute ():string {}

		public function getModelRequestParameter():string {}

		public function defaultMiddleware():array {

			return [
				FinalHandlerWrapper::class
			];
		}
	}
?>
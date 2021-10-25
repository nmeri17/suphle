<?php

	namespace Tilwa\Config;

	use Tilwa\Contracts\Config\Router as RouterConfig;

	use Tilwa\Middleware\FinalHandlerWrapper;

	abstract class Router implements RouterConfig {

		public function apiPrefix():string {

			return "api";
		}

		// should be listed in descending order of the versions
		public function apiStack ():array {

			return [];
		}

		abstract public function browserEntryRoute ():string;

		// list in ascending order of execution
		public function defaultMiddleware():array {

			return [
				FinalHandlerWrapper::class
			];
		}
	}
?>
<?php

	namespace Tilwa\Config;

	use Tilwa\Contracts\Config\Router as RouterConfig;

	class Router implements RouterConfig {

		public function apiPrefix():string {

			return "api";
		}

		public function routePermissions():string {
			
			return RouteGuards::class;
		}

		// should be listed in descending order of the versions
		public function apiStack ():array {}

		public function browserEntryRoute ():string {}

		public function getModelRequestParameter():string {}
	}
?>
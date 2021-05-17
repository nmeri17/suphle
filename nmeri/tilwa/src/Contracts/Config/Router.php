<?php

	namespace Tilwa\Contracts\Config;

	interface Router extends ConfigMarker {

		public function apiPrefix():string { // move these to the concrete

			return "api";
		}

		# class containing route guard rules
		public function routePermissions():string {
			
			return RouteGuards::class;
		}

		// should be listed in descending order of the versions
		public function apiStack ():array;

		public function browserEntryRoute ():string;

		public function getModelRequestParameter():string;
	}
?>
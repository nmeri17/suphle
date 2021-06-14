<?php

	namespace Tilwa\Contracts\Config;

	interface Router extends ConfigMarker {

		public function apiPrefix():string;

		# class containing route guard rules
		public function routePermissions():string;

		// should be listed in descending order of the versions
		public function apiStack ():array;

		// point to the entry collection
		public function browserEntryRoute ():string;

		public function getModelRequestParameter():string;
	}
?>
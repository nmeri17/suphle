<?php

	namespace Tilwa\Contracts\Config;

	interface Router extends ConfigMarker {

		public function apiPrefix():string;

		/**
		 * Should be listed in descending order of the versions
		*/
		public function apiStack ():array;

		// point to the entry collection
		public function browserEntryRoute ():string;

		public function defaultMiddleware():array;

		public function mirrorsCollections ():bool;

		// names the storage mechanism to be used on the browser collection when we've switched to those collections
		public function mirrorAuthenticator ():string;
	}
?>
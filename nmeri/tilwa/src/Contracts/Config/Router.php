<?php
	namespace Tilwa\Contracts\Config;

	interface Router extends ConfigMarker {

		public function apiPrefix ():string;

		/**
		 * Should be listed in descending order of the versions
		*/
		public function apiStack ():array;

		/**
		 * @return App entry collection
		*/
		public function browserEntryRoute ():string;

		/**
		 * List in ascending order of execution
		*/
		public function defaultMiddleware ():array;

		public function mirrorsCollections ():bool;

		// names the storage mechanism to be used on the browser collection when we've switched to those collections
		public function mirrorAuthenticator ():string;

		/**
		 * @return ExternalRouter[]
		*/
		public function externalRouters ():array;
	}
?>
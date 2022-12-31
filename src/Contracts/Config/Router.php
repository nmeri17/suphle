<?php
	namespace Suphle\Contracts\Config;

	interface Router extends ConfigMarker {

		public function apiPrefix ():string;

		/**
		 * Should be listed in descending order of the versions
		*/
		public function apiStack ():array;

		/**
		 * @return null if only API collections should be available
		*/
		public function browserEntryRoute ():?string;

		/**
		 * List in ascending order of execution
		*/
		public function defaultMiddleware ():array;

		public function mirrorsCollections ():bool;

		// names the storage mechanism to be used on the browser collection when we've switched to those collections
		public function mirrorAuthenticator ():string;

		/**
		 * @return class-string<ExternalRouter>[]
		*/
		public function externalRouters ():array;
	}
?>
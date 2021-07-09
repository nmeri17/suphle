<?php

	namespace Tilwa\Contracts;

	interface RouteCollection {

		public function _handlingClass ():string;
		
		public function _prefixCurrent():string;
		
		public function _setLocalPrefix(string $prefix):void;

		public function getPatterns():array;

		public function _authenticatedPaths():array;

		public function _authorizePaths():void;

		public function _assignMiddleware():void;

		public function _getAuthenticator ():AuthStorage;

		public function getPrefixCollection ():string;

		public function isMirroring ():bool;

		public function expectsCrud ():bool;

		public function doesntExpectCrud ():void;

		public function getLocalPrefix ():string;
	}
?>
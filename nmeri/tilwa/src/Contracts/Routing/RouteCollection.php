<?php

	namespace Tilwa\Contracts\Routing;

	use Tilwa\Contracts\Auth\AuthStorage;

	interface RouteCollection {

		public function _handlingClass ():string;
		
		public function _prefixCurrent():string;
		
		public function _setCrudPrefix(string $prefix):void;

		public function _getPatterns():array;

		public function _authenticatedPaths():array;

		public function _authorizePaths():void;

		public function _assignMiddleware():void;

		public function _getAuthenticator ():AuthStorage;

		public function _getPrefixCollection ():?string;

		public function _isMirroring ():bool;

		public function _expectsCrud ():bool;

		public function _getCrudPrefix ():string;

		public function _getLastRegistered ():array;

		public function _setLastRegistered (array $renderers):void
	}
?>
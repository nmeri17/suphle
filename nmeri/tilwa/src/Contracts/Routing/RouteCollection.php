<?php
	namespace Tilwa\Contracts\Routing;

	use Tilwa\Contracts\Auth\AuthStorage;

	use Tilwa\Routing\Crud\BaseBuilder;

	interface RouteCollection {

		public function _handlingClass ():string;
		
		public function _prefixCurrent():string;

		public function _getPrefixCollection ():?string;

		public function _getPatterns():array;

		public function _authenticatedPaths():array;

		public function _authorizePaths():void;

		public function _assignMiddleware():void;

		public function _getAuthenticator ():AuthStorage;
		
		public function _setCrudPrefix(string $prefix):void;

		public function _getCrudPrefix ():string;

		public function _expectsCrud ():bool;

		protected function _crud (string $viewPath, string $viewModelPath = null):BaseBuilder;

		public function _getLastRegistered ():array;

		public function _setLastRegistered (array $renderers):void
	}
?>
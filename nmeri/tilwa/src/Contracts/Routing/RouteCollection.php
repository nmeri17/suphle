<?php
	namespace Tilwa\Contracts\Routing;

	use Tilwa\Routing\MethodSorter;

	use Tilwa\Contracts\Auth\AuthStorage;

	use Tilwa\Request\PathAuthorizer;

	use Tilwa\Middleware\MiddlewareRegistry;

	interface RouteCollection {

		public function _handlingClass ():string;
		
		public function _prefixCurrent():string;

		public function _getPrefixCollection ():?string;

		public function _getPatterns():array;

		public function _authenticatedPaths():array;

		public function _authorizePaths(PathAuthorizer $pathAuthorizer):void;

		public function _assignMiddleware(MiddlewareRegistry $registry):void;

		public function _getAuthenticator ():AuthStorage;

		public function _expectsCrud ():bool;

		public function _crud (string $viewPath, string $viewModelPath = null):CrudBuilder;

		public function _getLastRegistered ():array;

		public function _setLastRegistered (array $renderers):void;

		public function _getMethodSorter ():MethodSorter;
	}
?>
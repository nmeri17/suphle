<?php
	namespace Tilwa\Contracts\Routing;

	use Tilwa\Contracts\Auth\AuthStorage;

	use Tilwa\Routing\Crud\BaseBuilder;

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
		
		public function _setCrudPrefix(string $prefix):void;

		public function _getCrudPrefix ():string;

		public function _expectsCrud ():bool;

		public function _crud (string $viewPath, string $viewModelPath = null):BaseBuilder;

		public function _getLastRegistered ():array;

		public function _setLastRegistered (array $renderers):void;
	}
?>
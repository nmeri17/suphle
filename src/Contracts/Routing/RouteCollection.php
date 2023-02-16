<?php
	namespace Suphle\Contracts\Routing;

	use Suphle\Routing\MethodSorter;

	use Suphle\Contracts\Auth\AuthStorage;

	use Suphle\Request\PathAuthorizer;

	use Suphle\Middleware\MiddlewareRegistry;

	interface RouteCollection {

		final public const INDEX_METHOD = "_index";
		
		public function _prefixCurrent():string;

		public function _setParentPrefix (string $prefix):void;

		public function _getPrefixCollection ():?string;

		public function _getPatterns():array;

		public function _invokePattern (string $methodPattern):void;

		public function _authenticatedPaths():array;

		public function _authorizePaths(PathAuthorizer $pathAuthorizer):void;

		public function _assignMiddleware(MiddlewareRegistry $registry):void;

		public function _getAuthenticator ():AuthStorage;

		public function _expectsCrud ():bool;

		public function _crud (string $markupPath, string $templatePath = null):CrudBuilder;

		public function _getLastRegistered ():array;

		public function _setLastRegistered (array $renderers):void;

		public function _getMethodSorter ():MethodSorter;
	}
?>
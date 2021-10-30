<?php

	namespace Tilwa\Routing;

	use Tilwa\Response\Format\AbstractRenderer;

	use Tilwa\Auth\TokenStorage;

	use Tilwa\Contracts\{Routing\RouteCollection, Auth\AuthStorage};

	use Tilwa\Routing\Crud\{BaseBuilder, ApiBuilder, BrowserBuilder};

	abstract class BaseCollection implements RouteCollection {

		protected $canaryValidator, $routerConfig, $authStorage, $middlewareRegistry, $lastRegistered;

		private $utilities = ["_mirrorBrowserRoutes", "_authenticatedPaths", "_handlingClass", "_crud", "_crudJson", "_register", "_getPrefixCollection", "_canaryEntry", "_setCrudPrefix", "_prefixCurrent", "_getPatterns", "__call", "_prefixFor", "_getAuthenticator", "_getCrudPrefix", "_expectsCrud", "_isMirroring", "_only", "_except", "_assignMiddleware", "_authorizePaths", "_getLastRegistered", "_setLastRegistered"
		],

		$mirroring = false, $crudMode = false, $crudPrefix, $prefixClass;

		public function __construct(CanaryValidator $validator, RouterConfig $routerConfig, SessionStorage $authStorage, MiddlewareRegistry $middlewareRegistry) {

			$this->routerConfig = $routerConfig;

			$this->canaryValidator = $validator;

			$this->authStorage = $authStorage;

			$this->middlewareRegistry = $middlewareRegistry;
		}

		/**
		* overwrite in your routes file
		*	
		* will be treated specially in the matcher, when path is empty i.e. /, cart/
		*/
		/*public function _index ():array {

			// register a route here
		}*/

		/**
		* @description: should be called only in the API first version's _index method
		* Assumes that _index method is defined last so subsequent methods found within the same scope can overwrite methods from the nested browser route search
		*/
		public function _mirrorBrowserRoutes (TokenStorage $tokenStorage):void {

			$this->authStorage = $tokenStorage;

			$this->mirroring = true;

			$this->_prefixFor($this->routerConfig->browserEntryRoute());
		}
		
		public function _prefixCurrent():string {
			
			return "";
		}
		
		public function _setCrudPrefix(string $prefix):void {
			
			$this->crudPrefix = $prefix;
		}

		/**
		 * `save` must be called in the invoking method
		*/
		protected function _crud (string $viewPath, string $viewModelPath = null):BaseBuilder {

			$this->crudMode = true;

			return new BrowserBuilder($this, $viewPath, $viewModelPath );
		}

		protected function _crudJson ():BaseBuilder {

			$this->crudMode = true;

			return new ApiBuilder($this );
		}

		public function __call ($method, $args) {

			$renderer = current($args);

			if (in_array($method, ["_get", "_post", "_delete", "_put"]))

				return $this->_register($renderer, $method);
		}

		protected function _register(AbstractRenderer $renderer, string $method):self {

			$renderer->setRouteMethod(ltrim($method, "_"));

			$this->lastRegistered = [$renderer];

			return $this;
		}

		public function _getLastRegistered ():array {

			return $this->lastRegistered;
		}

		public function _setLastRegistered (array $renderers):void {

			$this->lastRegistered = $renderers;
		}

		public function _prefixFor (string $routeClass):void {

			$this->prefixClass = $routeClass;
		}

		# filter off methods that belong to this base
		public function _getPatterns():array {

			return array_diff(get_class_methods($this), $this->utilities);
		}

		public function _authenticatedPaths():array {

			return [];
		}

		public function _authorizePaths():void {}

		public function _assignMiddleware():void {}

		public function _getAuthenticator ():AuthStorage {

			return $this->authStorage;
		}

		protected function _only(array $include):array {
			
			return array_intersect($this->_getPatterns(), $include);
		}

		protected function _except(array $exclude):array {
			
			return array_diff($this->_getPatterns(), $exclude);
		}

		protected function _canaryEntry(array $canaries):void {

			$validEntries = $this->canaryValidator->validate($canaries);
			
			foreach ($validEntries as $canary)
				
				if ($canary->willLoad() ) {

					$this->_prefixFor($canary->entryClass());

					break;
				}
		}

		public function _getPrefixCollection ():?string {

			return $this->prefixClass;
		}

		public function _isMirroring ():bool {

			return $this->mirroring;
		}

		public function _expectsCrud ():bool {

			return $this->crudMode;
		}

		public function _getCrudPrefix ():string {

			return $this->crudPrefix;
		}
	}
?>
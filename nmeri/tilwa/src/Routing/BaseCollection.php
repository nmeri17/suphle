<?php
	namespace Tilwa\Routing;

	use Tilwa\Response\Format\AbstractRenderer;

	use Tilwa\Request\PathAuthorizer;

	use Tilwa\Routing\Crud\{BaseBuilder, BrowserBuilder};

	use Tilwa\Middleware\MiddlewareRegistry;

	use Tilwa\Contracts\{Routing\RouteCollection, Auth\AuthStorage};

	abstract class BaseCollection implements RouteCollection {

		protected $collectionParent = BaseCollection::class,

		$crudMode = false, $pathAuthorizer,

		$canaryValidator, $authStorage, $middlewareRegistry;

		private $crudPrefix, $prefixClass, $lastRegistered;

		public function __construct(CanaryValidator $validator, AuthStorage $authStorage, MiddlewareRegistry $middlewareRegistry, PathAuthorizer $pathAuthorizer) {

			$this->canaryValidator = $validator;

			$this->authStorage = $authStorage;

			$this->middlewareRegistry = $middlewareRegistry;

			$this->pathAuthorizer = $pathAuthorizer;
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

			return array_diff(get_class_methods($this), get_class_methods($this->collectionParent));
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

		public function _expectsCrud ():bool {

			return $this->crudMode;
		}

		public function _getCrudPrefix ():string {

			return $this->crudPrefix;
		}
	}
?>
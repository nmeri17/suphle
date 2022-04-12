<?php
	namespace Tilwa\Routing;

	use Tilwa\Request\PathAuthorizer;

	use Tilwa\Routing\Crud\BrowserBuilder;

	use Tilwa\Middleware\MiddlewareRegistry;

	use Tilwa\Contracts\{ Auth\AuthStorage, Presentation\BaseRenderer};

	use Tilwa\Contracts\Routing\{RouteCollection, CrudBuilder};

	abstract class BaseCollection implements RouteCollection {

		protected $collectionParent = BaseCollection::class,

		$crudMode = false,

		$canaryValidator, $authStorage;

		private $crudPrefix, $prefixClass, $lastRegistered,

		$methodSorter;

		public function __construct(CanaryValidator $validator, AuthStorage $authStorage, MethodSorter $methodSorter) {

			$this->canaryValidator = $validator;

			$this->authStorage = $authStorage;

			$this->methodSorter = $methodSorter;
		}
		
		public function _prefixCurrent():string {
			
			return "";
		}

		/**
		 * `save` must be called in the invoking method
		*/
		public function _crud (string $viewPath, string $viewModelPath = null):CrudBuilder {

			$this->crudMode = true;

			return new BrowserBuilder($this, $viewPath, $viewModelPath );
		}

		public function __call ($method, $args) {

			$renderer = current($args);

			if (in_array($method, ["_get", "_post", "_delete", "_put"]))

				return $this->_register($renderer, $method);
		}

		protected function _register(BaseRenderer $renderer, string $method):self {

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

			$methods = array_diff(get_class_methods($this), get_class_methods($this->collectionParent));

			$prefixed = array_map(function($name) {

				$prefix = $this->_prefixCurrent();

				if (!empty($prefix))

					return strtoupper($prefix) . "_$name";

				return $name;
			}, $methods);

			return $this->methodSorter->descendingValues($prefixed);
		}

		public function _getMethodSorter ():MethodSorter {

			return $this->methodSorter;
		}

		public function _authenticatedPaths():array {

			return [];
		}

		public function _authorizePaths(PathAuthorizer $pathAuthorizer):void {}

		public function _assignMiddleware(MiddlewareRegistry $registry):void {}

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
	}
?>
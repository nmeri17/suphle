<?php
	namespace Tilwa\Routing;

	use Tilwa\Response\Format\AbstractRenderer;

	use Tilwa\Request\PathAuthorizer;

	use Tilwa\Routing\Crud\{BaseBuilder, BrowserBuilder};

	use Tilwa\Middleware\MiddlewareRegistry;

	use Tilwa\Contracts\{Routing\RouteCollection, Auth\AuthStorage, Presentation\BaseRenderer};

	abstract class BaseCollection implements RouteCollection {

		protected $collectionParent = BaseCollection::class,

		$crudMode = false,

		$canaryValidator, $authStorage;

		private $crudPrefix, $prefixClass, $lastRegistered;

		public function __construct(CanaryValidator $validator, AuthStorage $authStorage) {

			$this->canaryValidator = $validator;

			$this->authStorage = $authStorage;
		}
		
		public function _prefixCurrent():string {
			
			return "";
		}

		/**
		 * `save` must be called in the invoking method
		*/
		public function _crud (string $viewPath, string $viewModelPath = null):BaseBuilder {

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

			usort($prefixed, function ($a, $b) { // move longer patterns up so shorter ones don't misleadingly swallow partly matching segments

				$aLength = strlen($a);

				$bLength = strlen($b);

				if ($aLength == $bLength) return 0;

				return ($bLength > $aLength) ? 1: -1; // push greater right upwards ie descending
			});

			return $prefixed;
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
<?php
	namespace Suphle\Routing;

	use Suphle\Request\PathAuthorizationScrutinizer;

	use Suphle\Routing\Crud\BrowserBuilder;

	use Suphle\Middleware\MiddlewareRegistry;

	use Suphle\Contracts\{Presentation\BaseRenderer, Auth\AuthStorage};

	use Suphle\Contracts\Routing\{RouteCollection, CrudBuilder};

	use Exception;

	abstract class BaseCollection implements RouteCollection {

		protected string $collectionParent = BaseCollection::class; // this is set if this collection is used as prefix in another. Should be used while determining the prefix of that collection

		protected ?string $prefixClass = null, $parentPrefix = null;

		protected bool $crudMode = false;

		protected array $lastRegistered = [];

		public function __construct(

			protected readonly CanaryValidator $canaryValidator,

			protected readonly MethodSorter $methodSorter,

			protected AuthStorage $authStorage
		) {

			//
		}
		
		/**
		 * The same rules that apply to method patterns apply here: uppercase for literals, underscores with "h" for compound names etc
		*/
		public function _prefixCurrent():string {
			
			return "";
		}

		public function _setParentPrefix (string $prefix):void {

			$this->parentPrefix = $prefix;
		}

		/**
		 * `save` must be called in the invoking method
		*/
		public function _crud (string $markupPath, string $templatePath = null):CrudBuilder {

			$this->crudMode = true;

			return new BrowserBuilder($this, $markupPath, $templatePath );
		}

		public function _httpGet (BaseRenderer $renderer):self {

			return $this->_register($renderer, "get");
		}

		public function _httpPost (BaseRenderer $renderer):self {

			return $this->_register($renderer, "post");
		}

		public function _httpPut (BaseRenderer $renderer):self {

			return $this->_register($renderer, "put");
		}

		public function _httpDelete (BaseRenderer $renderer):self {

			return $this->_register($renderer, "delete");
		}

		private function _register (BaseRenderer $renderer, string $method):self {

			$renderer->setRouteMethod($method);

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

		/**
		 * Filter off methods that belong to this base, but first prepend prefixes to them where applicable so the manager dooesn't do that each time manually
		*/
		public function _getPatterns():array {

			$methods = array_diff(

				get_class_methods($this),

				get_class_methods($this->collectionParent) // using an explicit parent instead of automatically differentiating from parent methods to enable extension of route collections
			);

			return $this->methodSorter->descendingValues($this->prependPrefix($methods));
		}

		private function prependPrefix (array $patterns):array {

			return array_map(function($name) {

				$prefix = $this->_prefixCurrent();

				if (!empty($prefix))

					return $prefix . "_$name";

				return $name;
			}, $patterns);
		}

		/**
		 * Antithesis of the [_getPatterns] to trim off prefix
		*/
		public function _invokePattern (string $methodPattern):void {

			$prefix = $this->_prefixCurrent();

			if (!empty($prefix)) {

				$matches = preg_split("/". $prefix. "_/i", $methodPattern);

				$methodPattern = $matches[1];
			}

			$this->$methodPattern();
		}

		public function _getMethodSorter ():MethodSorter {

			return $this->methodSorter;
		}

		public function _assignMiddleware(MiddlewareRegistry $registry):void {}

		public function _preMiddleware (PreMiddlewareRegistry $patternIndicator):void {}

		protected function _only(array $include):array {
			
			return array_intersect(

				$this->_getPatterns(), $this->prependPrefix($include)
			);
		}

		protected function _except(array $exclude):array {
			
			return array_diff(

				$this->_getPatterns(), $this->prependPrefix($exclude)
			);
		}

		protected function _canaryEntry(array $canaries):void {

			$validator = $this->canaryValidator;

			$instances = $validator->setCanaries($canaries)

			->collectionAuthStorage($this->authStorage)

			->setValidCanaries()->getCanaryInstances();
			
			foreach ($instances as $canary) {

				if ($canary->willLoad() ) {

					$this->_prefixFor($canary->entryClass());

					break;
				}
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
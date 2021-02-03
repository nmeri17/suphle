<?php

	namespace Tilwa\Routing;
	
	use Controllers\Home;

	use Tilwa\Http\Response\Format\AbstractRenderer;

	class RouteCollection {

		private $utilities;

		public $prefixClass;

		private $allow;

		private $canaryValidator;

		private $browserEntry;

		public $isMirroring;

		public $expectsCrud;

		public $activeCrudPaths;

		/**
		* @param {permissions} @see `Bootstrap->routePermissions`
		*/
		function __construct(CanaryValidator $validator, string $browserEntry, object $permissions) {

			$this->allow = $permissions;

			$this->canaryValidator = $validator;

			$this->browserEntry = $browserEntry;

			$this->utilities = ["_mirrorBrowserRoutes", "_passover", "_handlingClass", "_crud", "_register", "_setAllow", "_canaryEntry", "_setLocalPrefix"];
		}

		/** overwrite in your routes file
			
		* will be treated specially in the matcher, when path is empty i.e. /, cart/
		*/
		public function _index ():array; // register a route here

		/**
		* @description: should be called only in the API first version's _index method
		* Assumes that _index method is defined last so subsequent methods found within the same scope can overwrite methods from the nested browser route search
		*/
		public function _mirrorBrowserRoutes ():void {

			$this->isMirroring = true;

			return $this->_prefixFor($this->browserEntry);
		}

		public function _handlingClass ():string {

			return Home::class; // default controller
		}
		
		public function _prefixCurrent():string {
			
			return null;
		}
		
		// crud routes must be anchored by either a preceding collection group name, or the current one. So, we make that assertion from this property set externally by the manager
		public function _setLocalPrefix(string $prefix):void {
			
			$this->localPrefix = $prefix;
		}

		protected function _crud ():CrudBuilder {

			if (!empty($this->localPrefix)) { // confirm setting neither creates no crud routes

				$this->expectsCrud = true;

				return new CrudBuilder($this); // you must call `save` in the invoking method
			}
		}

		public function __call ($method, $renderer) {

			if (in_array($method, ["_get", "_post", "_delete", "_put"]))

				return $this->_register($renderer, $method);
		}

		protected function _register(AbstractRenderer $renderer, string $method):array {

			$renderer->routeMethod = ltrim($method, "_");

			return [$renderer];
		}

		public function _prefixFor (string $routeClass):void {

			$this->prefixClass = $routeClass;
		}

		# filter off methods that aren't one of us
		public function getPatterns():array {

			$myMethods = get_class_methods($this);

			if ($parent_class = get_parent_class($this)) {

				$parentMethods = get_class_methods($parent_class);
				$myMethods = array_diff($myMethods, $parentMethods);
			}
			return array_diff($myMethods, $this->utilities);
		}

		# @return $this->allow->auth();
		public function _passover():bool {
			
			return true;
		}

		protected function _canaryEntry(array $canaries):void {

			$validEntries = $this->canaryValidator->validate($canaries);
			
			foreach ($validEntries as $canary)
				
				if ($canary->willLoad() )

					return $this->_prefixFor($canary->entryClass());
		}
	}
?>
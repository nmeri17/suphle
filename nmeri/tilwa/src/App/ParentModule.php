<?php

	namespace Tilwa\App;

	use Models\User;

	use Tilwa\Http\Request\RouteGuards;

	abstract class ParentModule {

		protected $container;

		public function activate ():self {

			$this->entityBindings()->bindEvents(); // evaluate if these actions are worth taking before route finding; `bindEvents` especially
			return $this;
		}

		// should be listed in descending order of the versions
		public function apiStack ():array;

		public function browserEntryRoute ():string;

		public function getRootPath ():string;

		public function setDependsOn(array $bindings):self {
			
			# check if key interface matches the `exports` of incoming type before pairing
		}

		// @return interfaces[] from `Interactions` namespace
		public function getDependsOn():array {

			return [];
		}

		public function exports():string {

			return; // an interface from Interactions namespace for `setDependsOn` on sister modules to consume
		}

		public function getUserModel():string {

			return User::class;
		}

		public function apiPrefix():string {

			return "api";
		}

		public function getViewPath ():string {

			return $this->getRootPath() . 'views'. DIRECTORY_SEPARATOR;
		}

		# class containing route guard rules
		public function routePermissions():string {
			
			return RouteGuards::class;
		}

		// provision your classes here
		abstract public function entityBindings ():self;

		// attach event listeners here
		public function bindEvents ():self {

			// may need to work with an event manager
		}

		protected function on () {

			//
		}
	}

?>
<?php

	namespace Tilwa\App;

	// these user defined classes should be read from the Main module when we have that
	use Models\User;

	use Events\AssignListeners;

	use Tilwa\Http\Request\RouteGuards;

	use Tilwa\Routing\RouteManager;

	abstract class ParentModule {

		protected $container;

		private $dependsOn;

		function __construct() {
			
			$this->container = new Container;
		}

		// should be listed in descending order of the versions
		public function apiStack ():array;

		public function browserEntryRoute ():string;

		public function getRootPath ():string;

		public function setDependsOn(array $dependencies):self {
			
			foreach ($dependencies as $contract => $concrete) {

				$service = $concrete->exports();
				
				if ($service instanceof $contract) {

					$pair = [$contract => $service]; // So far, `exports` classes merely expose functionality in controllers and services. They don't wield any influence over what goes on in the module. But if the need for that arises, we would need to configure it externally. And, that module will grant access via its `exports` class's constructor. Then we will either need a special service provider that allows a more customized control over that guy's initialization, or through the consumed module itself

					$this->dependsOn += $pair;

					$this->container->whenTypeAny()->needsAny($pair);
				}
			}
		}

		// @return interfaces[] from `Interactions` namespace
		public function getDependsOn():array {

			return $this->dependsOn;
		}

		// @return a class implementing `exportsImplements`
		public function exports():object {

			return null;
		}

		// interface from Interactions namespace which will be consumers API with this module
		public function exportsImplements():string {
			
			return "";
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

		// extending module is expected to do a parent::entityBindings($router) before provisioning its own classes
		public function entityBindings (RouteManager $router):self {

			$this->container->whenTypeAny()->needsAny([

				ParentModule::class => $this, // all requests for the parent should respond with the active module

				RouteManager::class => $router
			]);
			return $this;
		}

		public function listenersLoader ():string {

			return AssignListeners::class;
		}
	}
?>
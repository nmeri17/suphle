<?php

	namespace Tilwa\App;

	// these user defined classes should be read from the Main module when we have that
	use Models\User;

	use Events\AssignListeners;

	use Tilwa\Http\Request\RouteGuards;

	use Tilwa\Routing\RouteManager;

	use Tilwa\Controllers\{ServiceWrapper, RepositoryWrapper};

	use Tilwa\Contracts\{Orm, HtmlParser, Authenticator, RequestValidator, QueueManager};

	use Tilwa\ServiceProviders\{OrmProvider, AuthenticatorProvider, HtmlTemplateProvider, RequestValidatorProvider, QueueProvider};

	abstract class ParentModule {

		protected $container;

		private $dependsOn;

		private $serviceLifecycle;

		function __construct(Container $container, bool $serviceLifecycle = false ) {
			
			$this->container = $container;

			$this->serviceLifecycle = $serviceLifecycle;
		}

		// should be listed in descending order of the versions
		public function apiStack ():array;

		public function browserEntryRoute ():string;

		public function getRootPath ():string;

		public function setDependsOn(array $dependencies):self {
			
			foreach ($dependencies as $contract => $concrete) {

				$service = $concrete->exports();
				
				if ($service instanceof $contract) {

					$pair = [$contract => $service];

					$this->dependsOn += $pair;

					$this->container->whenTypeAny()->needsAny($pair);
				}
			}
		}

		// @return interfaces[] from `Interactions` namespace
		public function getDependsOn():array {

			return $this->dependsOn;
		}

		// @return concrete implementing `exportsImplements`
		abstract public function exports():object;

		// interface from Interactions namespace which will be consumers API with this module
		abstract public function exportsImplements():string;

		public function getUserModel():string {

			return User::class; // is providing it worth it?
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
			])
			->whenType(ServiceWrapper::class)->needsArguments([

				"lifeCycle" => $this->serviceLifecycle
			])
			->whenType(RepositoryWrapper::class)->needsArguments([

				"lifeCycle" => $this->serviceLifecycle
			]);
			return $this;
		}

		// @return the class where we bound listeners to events we wanna listen to
		public function listenersLoader ():string {

			return AssignListeners::class;
		}

		public function getContainer():Container {
			
			return $this->container;
		}

		// this information belongs on the container, but we're setting it here since containers are injected externally and we don't wanna clutter the assembly namespace
		public function getServiceProviders():array {

			return [
				Orm::class => OrmProvider::class,

				HtmlParser::class => HtmlTemplateProvider::class,

				Authenticator::class => AuthenticatorProvider::class,

				RequestValidator::class => RequestValidatorProvider::class,

				QueueManager::class => QueueProvider::class
			];
		}
	}
?>
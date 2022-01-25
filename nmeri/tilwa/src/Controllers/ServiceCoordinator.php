<?php
	namespace Tilwa\Controllers;

	use Tilwa\Hydration\Container;

	use Tilwa\Events\EventManager;

	use Tilwa\Contracts\Services\{QueryInterceptor, CommandService,SelectiveDependencies};

	use Tilwa\IO\Http\BaseHttpRequest;

	use Tilwa\Request\PayloadStorage;

	use Tilwa\Contracts\Modules\ControllerModule;

	class ServiceCoordinator implements SelectiveDependencies {

		private $services, $container;

		final public function getPermitted ():array {

			return [
				ConditionalFactory::class, // We're treating it as a type of service in itself
				ControllerModule::class, // These are a service already. There's no need accessing them through another local proxy

				BaseHttpRequest::class, QueryInterceptor::class, PayloadStorage::class, CommandService::class
			];
		}

		final public function getRejected ():array {

			return [EventManager::class,];
		}

		public function hasIsolatedConstructor():bool {
			
			return empty(get_object_vars($this));
		}

		public function __get($property) {

			$concrete = $this->services[$property];

			if ($concrete instanceof EventManager)

				return $concrete; // [ExecutionUnit] will eventually wrap the handler in [RepositoryWrapper] if it matches

			return $this->getWrappedService($concrete);
		}

		public function setContainer(Container $container):self {
			
			$this->container = $container;

			return $this;
		}

		private function getWrappedService(object $originalService) {
			
			$container = $this->container;

			if ($originalService instanceof QueryInterceptor)

				$wrapper = $container->getClass(RepositoryWrapper::class);

			else $wrapper = $container->getClass(ServiceWrapper::class);

			return $wrapper->setActiveService($originalService);
		}

		public function validatorCollection ():?string {

			return null;
		}
	}
?>
<?php
	namespace Tilwa\Services;

	use Tilwa\Events\EventManager;

	use Tilwa\Contracts\Modules\ControllerModule;

	use Tilwa\Contracts\Services\Decorators\{SecuresPostRequest, SelectiveDependencies};

	use Tilwa\IO\Http\BaseHttpRequest;

	use Tilwa\Request\PayloadStorage;

	use Tilwa\Hydration\Container;

	class ServiceCoordinator implements SelectiveDependencies, SecuresPostRequest {

		final public function getPermitted ():array {

			return [
				ConditionalFactory::class, // We're treating it as a type of service in itself
				ControllerModule::class, // These are a service already. There's no need accessing them through another local proxy

				BaseHttpRequest::class, UpdatefulService::class,

				PayloadStorage::class, UpdatelessService::class
			];
		}

		final public function getRejected ():array {

			return [EventManager::class, Container::class, ServiceCoordinator::class];
		}

		public function validatorCollection ():?string {

			return null;
		}
	}
?>
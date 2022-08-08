<?php
	namespace Suphle\Services;

	use Suphle\Events\EventManager;

	use Suphle\Contracts\Modules\ControllerModule;

	use Suphle\Contracts\Services\Decorators\{SecuresPostRequest, SelectiveDependencies, ValidatesActionArguments};

	use Suphle\IO\Http\BaseHttpRequest;

	use Suphle\Request\PayloadStorage;

	use Suphle\Hydration\Container;

	use Suphle\Services\Structures\{ModelfulPayload, ModellessPayload};

	class ServiceCoordinator implements SelectiveDependencies, SecuresPostRequest, ValidatesActionArguments {

		final public function getPermitted ():array {

			return [
				ConditionalFactory::class, // We're treating it as a type of service in itself
				ControllerModule::class, // These are a service already. There's no need accessing them through another local proxy

				PayloadStorage::class, // there may be items we don't want to pass to the builder but to a service?

				BaseHttpRequest::class, UpdatefulService::class,

				UpdatelessService::class
			];
		}

		final public function getRejected ():array {

			return [

				EventManager::class, Container::class,

				ServiceCoordinator::class
			];
		}

		final public function permittedArguments ():array {

			return [

				ModelfulPayload::class, ModellessPayload::class
			];
		}

		public function validatorCollection ():?string {

			return null;
		}
	}
?>
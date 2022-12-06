<?php
	namespace Suphle\Hydration\Structures;

	use Suphle\Contracts\Hydration\DecoratorChain;

	use Suphle\Services\Decorators\{ SecuresPostRequest, VariableDependencies, BindsAsSingleton, InterceptsCalls};

	use Suphle\Services\DecoratorHandlers\{ SecuresPostRequestHandler, VariableDependenciesHandler, BindSingletonHandler, CallInterceptorProxy};

	class BaseDecorators implements DecoratorChain {

		public function allScopes ():array {

			return [

				BindsAsSingleton::class => BindSingletonHandler::class,

				InterceptsCalls::class => CallInterceptorProxy::class,

				SecuresPostRequest::class => SecuresPostRequestHandler::class,

				VariableDependencies::class => VariableDependenciesHandler::class
			];
		}
	}
?>
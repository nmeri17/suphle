<?php
	namespace Suphle\Hydration\Structures;

	use Suphle\Contracts\Hydration\DecoratorChain;

	use Suphle\Contracts\Services\Decorators\{SelectiveDependencies, OnlyLoadedBy, SystemModelEdit, ServiceErrorCatcher, SecuresPostRequest, MultiUserModelEdit, ValidatesActionArguments, VariableDependencies, BindsAsSingleton};

	use Suphle\Services\DecoratorHandlers\{SystemModelEditHandler, ErrorCatcherHandler, SecuresPostRequestHandler, ServicePreferenceHandler, OnlyLoadedByHandler, MultiUserEditHandler, ActionDependenciesValidator, VariableDependenciesHandler, BindSingletonHandler};

	class BaseDecorators implements DecoratorChain {

		public function allScopes ():array {

			return [

				BindsAsSingleton::class => BindSingletonHandler::class,

				MultiUserModelEdit::class => MultiUserEditHandler::class,

				OnlyLoadedBy::class => OnlyLoadedByHandler::class,

				SelectiveDependencies::class => ServicePreferenceHandler::class,

				SystemModelEdit::class => SystemModelEditHandler::class,

				ServiceErrorCatcher::class => ErrorCatcherHandler::class,

				SecuresPostRequest::class => SecuresPostRequestHandler::class,

				ValidatesActionArguments::class => ActionDependenciesValidator::class,

				VariableDependencies::class => VariableDependenciesHandler::class
			];
		}
	}
?>
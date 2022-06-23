<?php
	namespace Tilwa\Hydration\Structures;

	use Tilwa\Contracts\Hydration\DecoratorChain;

	use Tilwa\Contracts\Services\Decorators\{SelectiveDependencies, OnlyLoadedBy, SystemModelEdit, ServiceErrorCatcher, SecuresPostRequest, MultiUserModelEdit, ValidatesActionArguments};

	use Tilwa\Services\DecoratorHandlers\{SystemModelEditHandler, ErrorCatcherHandler, SecuresPostRequestHandler, ServicePreferenceHandler, OnlyLoadedByHandler, MultiUserEditHandler, ActionDependenciesValidator};

	class BaseDecorators implements DecoratorChain {

		public function allScopes ():array {

			return [
				SelectiveDependencies::class => ServicePreferenceHandler::class,

				OnlyLoadedBy::class => OnlyLoadedByHandler::class,

				SystemModelEdit::class => SystemModelEditHandler::class,

				ServiceErrorCatcher::class => ErrorCatcherHandler::class,

				SecuresPostRequest::class => SecuresPostRequestHandler::class,

				MultiUserModelEdit::class => MultiUserEditHandler::class,

				ValidatesActionArguments::class => ActionDependenciesValidator::class
			];
		}
	}
?>
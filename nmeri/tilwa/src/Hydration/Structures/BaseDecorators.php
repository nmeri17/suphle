<?php
	namespace Tilwa\Hydration\Structures;

	use Tilwa\Contracts\Hydration\DecoratorChain;

	use Tilwa\Contracts\Services\Decorators\{SelectiveDependencies, OnlyLoadedBy, SystemModelEdit, ServiceErrorCatcher, SecuresPostRequest};

	use Tilwa\Hydration\DecoratorScopes\{ServicePreferenceHandler, OnlyLoadedByHandler};

	use Tilwa\Services\DecoratorHandlers\{SystemModelEditHandler, ErrorCatcherHandler, SecuresPostRequestHandler};

	class BaseDecorators implements DecoratorChain {

		public function allScopes ():array {

			return [
				SelectiveDependencies::class => ServicePreferenceHandler::class,

				OnlyLoadedBy::class => OnlyLoadedByHandler::class,

				SystemModelEdit::class => SystemModelEditHandler::class,

				ServiceErrorCatcher::class => ErrorCatcherHandler::class,

				SecuresPostRequest::class => SecuresPostRequestHandler::class
			];
		}
	}
?>
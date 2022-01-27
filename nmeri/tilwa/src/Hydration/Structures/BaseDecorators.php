<?php
	namespace Tilwa\Hydration\Structures;

	use Tilwa\Contracts\Hydration\DecoratorChain;

	use Tilwa\Contracts\Services\Decorators\{SelectiveDependencies, OnlyLoadedBy, SystemModelEdit, ServiceErrorCatcher};

	use Tilwa\Hydration\DecoratorScopes\{ServicePreferenceHandler, OnlyLoadedByHandler};

	use Tilwa\Controllers\DecoratorHandlers\{SystemModelEditHandler, ErrorCatcherHandler};

	class BaseDecorators implements DecoratorChain {

		public function allScopes ():array {

			return [
				SelectiveDependencies::class => ServicePreferenceHandler::class,

				OnlyLoadedBy::class => OnlyLoadedByHandler::class,

				SystemModelEdit::class => SystemModelEditHandler::class,

				ServiceErrorCatcher::class => ErrorCatcherHandler::class
			];
		}
	}
?>
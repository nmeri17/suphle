<?php
	namespace Tilwa\Hydration\Structures;

	use Tilwa\Contracts\Hydration\DecoratorChain;

	use Tilwa\Contracts\Services\{SelectiveDependencies, OnlyLoadedBy};

	use Tilwa\Hydration\DecoratorScopes\{ServicePreferenceHandler, OnlyLoadedByHandler};

	class BaseDecorators implements DecoratorChain {

		public function allScopes ():array {

			return [
				SelectiveDependencies::class => ServicePreferenceHandler::class,

				OnlyLoadedBy::class => OnlyLoadedByHandler::class
			];
		}
	}
?>
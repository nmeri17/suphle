<?php
	namespace Tilwa\Hydration\Structures;

	use Tilwa\Contracts\{Hydration\DecoratorChain, Services\SelectiveDependencies};

	use Tilwa\Hydration\DecoratorScopes\ServicePreferenceHandler;

	class BaseDecorators implements DecoratorChain {

		public function allScopes ():array {

			return [
				SelectiveDependencies::class => ServicePreferenceHandler::class
			];
		}
	}
?>
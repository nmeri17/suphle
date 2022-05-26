<?php
	namespace Tilwa\Services\DecoratorHandlers;

	use Tilwa\Contracts\{Hydration\ScopeHandlers\ModifyInjected, Services\OnlyLoadedBy};

	use Tilwa\Exception\Explosives\Generic\UnacceptableDependency;

	class OnlyLoadedByHandler implements ModifyInjected {

		/**
		 * @param {concrete} OnlyLoadedBy
		*/
		public function proxifyInstance (object $concrete, string $caller):object {

			foreach ($concrete->allowedConsumers() as $consumer)

				if ($caller instanceof $consumer)

					return $concrete;

			throw new UnacceptableDependency($caller, get_class($concrete));
		}
	}
?>
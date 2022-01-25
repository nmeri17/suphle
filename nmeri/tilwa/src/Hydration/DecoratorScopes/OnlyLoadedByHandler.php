<?php
	namespace Tilwa\Hydration\DecoratorScopes;

	use Tilwa\Contracts\{Hydration\ScopeHandlers\ModifyInjected, Services\OnlyLoadedBy};

	use Tilwa\Exception\Explosives\Generic\UnacceptableDependency;

	class OnlyLoadedByHandler implements ModifyInjected {

		public function proxifyInstance (OnlyLoadedBy $concrete, string $caller) {

			foreach ($concrete->allowedConsumers() as $consumer)

				if ($concrete instanceof $consumer)

					return $concrete;

			throw new UnacceptableDependency($caller, get_class($concrete));
		}
	}
?>
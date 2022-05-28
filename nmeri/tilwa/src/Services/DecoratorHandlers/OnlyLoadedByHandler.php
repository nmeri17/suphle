<?php
	namespace Tilwa\Services\DecoratorHandlers;

	use Tilwa\Contracts\Hydration\ScopeHandlers\ModifyInjected;

	use Tilwa\Hydration\Structures\ObjectDetails;

	use Tilwa\Exception\Explosives\Generic\UnacceptableDependency;

	class OnlyLoadedByHandler implements ModifyInjected {

		private $objectMeta;

		public function __construct (ObjectDetails $objectMeta) {

			$this->objectMeta = $objectMeta;
		}

		public function examineInstance (object $concrete, string $caller):object {

			foreach ($concrete->allowedConsumers() as $consumer)

				if ($this->objectMeta->stringInClassTree($caller, $consumer))

					return $concrete;

			throw new UnacceptableDependency($caller, get_class($concrete));
		}

		public function getMethodHooks ():array {

			return [];
		}
	}
?>
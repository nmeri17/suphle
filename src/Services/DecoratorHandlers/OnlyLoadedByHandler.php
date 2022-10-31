<?php
	namespace Suphle\Services\DecoratorHandlers;

	use Suphle\Contracts\{Hydration\ScopeHandlers\ModifyInjected, Services\Decorators\OnlyLoadedBy};

	use Suphle\Hydration\Structures\ObjectDetails;

	use Suphle\Exception\Explosives\Generic\UnacceptableDependency;

	class OnlyLoadedByHandler implements ModifyInjected {

		public function __construct(private readonly ObjectDetails $objectMeta)
  {
  }

		/**
		 * @param {concrete}: OnlyLoadedBy
		*/
		public function examineInstance (object $concrete, string $caller):object {

			foreach ($concrete->allowedConsumers() as $consumer)

				if ($this->objectMeta->stringInClassTree($caller, $consumer))

					return $concrete;

			throw new UnacceptableDependency($caller, $concrete::class);
		}
	}
?>
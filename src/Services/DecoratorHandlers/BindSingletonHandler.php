<?php
	namespace Suphle\Services\DecoratorHandlers;

	use Suphle\Contracts\Hydration\ScopeHandlers\ModifyInjected;

	use Suphle\Contracts\Services\Decorators\BindsAsSingleton;

	use Suphle\Hydration\{Container, Structures\ObjectDetails};

	use Suphle\Exception\Explosives\Generic\InvalidImplementor;

	class BindSingletonHandler implements ModifyInjected {

		public function __construct(private readonly ObjectDetails $objectMeta, private readonly Container $container)
  {
  }

		/**
		 * @param {concrete}: BindsAsSingleton
		*/
		public function examineInstance (object $concrete, string $caller):object {

			$allegedParent = $concrete->entityIdentity();

			$concreteName = $concrete::class;

			if (!$this->objectMeta->stringInClassTree(

				$concreteName, $allegedParent
			))

				throw new InvalidImplementor($allegedParent, $concreteName);

			$this->container->whenTypeAny()->needsAny([

				$allegedParent => $concrete
			]);

			return $concrete;
		}
	}
?>
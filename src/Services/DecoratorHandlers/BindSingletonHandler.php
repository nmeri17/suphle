<?php
	namespace Suphle\Services\DecoratorHandlers;

	use Suphle\Contracts\Hydration\ScopeHandlers\ModifyInjected;

	use Suphle\Contracts\Services\Decorators\BindsAsSingleton;

	use Suphle\Hydration\{Container, Structures\ObjectDetails};

	use Suphle\Exception\Explosives\Generic\InvalidImplementor;

	class BindSingletonHandler implements ModifyInjected {

		private $objectMeta, $container;

		public function __construct (ObjectDetails $objectMeta, Container $container) {

			$this->objectMeta = $objectMeta;

			$this->container = $container;
		}

		/**
		 * @param {concrete}: BindsAsSingleton
		*/
		public function examineInstance (object $concrete, string $caller):object {

			$allegedParent = $concrete->entityIdentity();

			$concreteName = get_class($concrete);

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
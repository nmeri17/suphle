<?php
	namespace Suphle\Services\DecoratorHandlers;

	use Suphle\Contracts\Hydration\ScopeHandlers\ModifyInjected;

	use Suphle\Hydration\{Container, Structures\ObjectDetails};

	use Suphle\Exception\Explosives\Generic\UnacceptableDependency;

	class BindSingletonHandler implements ModifyInjected {

		private $objectMeta, $container;

		public function __construct (ObjectDetails $objectMeta, Container $container) {

			$this->objectMeta = $objectMeta;

			$this->container = $container;
		}

		public function examineInstance (object $concrete, string $caller):object {

			$this->container->whenTypeAny()->needsAny([

				get_class($concrete) => $concrete
			]);

			return $concrete;
		}
	}
?>
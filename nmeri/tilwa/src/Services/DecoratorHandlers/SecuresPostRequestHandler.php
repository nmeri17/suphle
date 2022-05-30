<?php
	namespace Tilwa\Services\DecoratorHandlers;

	use Tilwa\Contracts\Hydration\ScopeHandlers\ModifiesArguments;

	use Tilwa\Contracts\Services\Decorators\{SystemModelEdit, MultiUserModelEdit};

	use Tilwa\Hydration\Structures\ObjectDetails;

	use Tilwa\Request\RequestDetails;

	use Tilwa\Exception\Explosives\Generic\MissingPostDecorator;

	class SecuresPostRequestHandler implements ModifiesArguments {

		private $postDecorators = [

			SystemModelEdit::class, MultiUserModelEdit::class
		],

		$requestDetails, $objectMeta;

		public function __construct (RequestDetails $requestDetails, ObjectDetails $objectMeta) {

			$this->requestDetails = $requestDetails;

			$this->objectMeta = $objectMeta;
		}

		public function transformConstructor (object $dummyInstance, array $arguments):array {

			if (!$this->requestDetails->matchesMethod("put"))

				return $arguments;

			foreach ($arguments as $dependency)

				foreach ($this->postDecorators as $decorator) {

					if (is_object($dependency) && $this->objectMeta->implementsInterface(

						get_class($dependency),

						$decorator
					))

						return $arguments;
				}

			throw new MissingPostDecorator(get_class($dummyInstance));
		}

		public function transformMethods (object $concreteInstance, array $arguments):array {

			return $arguments;
		}
	}
?>
<?php
	namespace Tilwa\Services\DecoratorHandlers;

	use Tilwa\Contracts\Services\Decorators\{SystemModelEdit, MultiUserModelEdit};

	use Tilwa\Hydration\Structures\ObjectDetails;

	use Tilwa\Request\RequestDetails;

	use Tilwa\Exception\Explosives\Generic\MissingPostDecorator;

	class SecuresPostRequestHandler extends BaseArgumentModifier {

		private $postDecorators = [

			SystemModelEdit::class, MultiUserModelEdit::class
		],

		$requestDetails;

		public function __construct (RequestDetails $requestDetails, ObjectDetails $objectMeta) {

			$this->requestDetails = $requestDetails;

			parent::__construct($objectMeta);
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
	}
?>
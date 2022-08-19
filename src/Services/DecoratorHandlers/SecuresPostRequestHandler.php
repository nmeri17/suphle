<?php
	namespace Suphle\Services\DecoratorHandlers;

	use Suphle\Contracts\Services\Decorators\{SystemModelEdit, MultiUserModelEdit};

	use Suphle\Hydration\Structures\ObjectDetails;

	use Suphle\Request\RequestDetails;

	use Suphle\Exception\Explosives\Generic\MissingPostDecorator;

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
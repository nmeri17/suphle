<?php
	namespace Suphle\Services\DecoratorHandlers;

	use Suphle\Contracts\Services\CallInterceptors\{SystemModelEdit, MultiUserModelEdit};

	use Suphle\Hydration\Structures\ObjectDetails;

	use Suphle\Request\RequestDetails;

	use Suphle\Exception\Explosives\Generic\MissingPostDecorator;

	class SecuresPostRequestHandler extends BaseArgumentModifier {

		private array $postDecorators = [

			SystemModelEdit::class, MultiUserModelEdit::class
		];

		public function __construct (

			private readonly RequestDetails $requestDetails,

			ObjectDetails $objectMeta
		) {

			parent::__construct($objectMeta);
		}

		public function transformConstructor (object $dummyInstance, array $arguments):array {

			if (!$this->requestDetails->matchesMethod("put"))

				return $arguments;

			foreach ($arguments as $dependency)

				foreach ($this->postDecorators as $decorator) {

					if (is_object($dependency) && $this->objectMeta->implementsInterface(

						$dependency::class,

						$decorator
					))

						return $arguments;
				}

			throw new MissingPostDecorator($dummyInstance::class);
		}
	}
?>
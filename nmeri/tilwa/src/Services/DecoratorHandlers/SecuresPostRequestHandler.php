<?php
	namespace Tilwa\Services\DecoratorHandlers;

	use Tilwa\Contracts\Hydration\ScopeHandlers\ModifiesArguments;

	use Tilwa\Contracts\Services\Decorators\{SystemModelEdit, MultiUserModelEdit};

	use Tilwa\Request\RequestDetails;

	use Tilwa\Exception\Explosives\Generic\MissingPostDecorator;

	class SecuresPostRequestHandler implements ModifiesArguments {

		private $postDecorators = [

			SystemModelEdit::class, MultiUserModelEdit::class
		],

		$requestDetails;

		public function __construct (RequestDetails $requestDetails) {

			$this->requestDetails = $requestDetails;
		}

		public function transformConstructor ($dummyInstance, array $arguments):array {

			if ($this->requestDetails->httpMethod() != "put")

				return $arguments;

			foreach ($arguments as $dependency)

				foreach ($this->postDecorators as $decorator) {

					if ($dependency instanceof $decorator)

						return $arguments;
				}

			throw new MissingPostDecorator($dummyInstance);
		}

		public function transformMethods ($concreteInstance, array $arguments):array {

			return $arguments;
		}
	}
?>
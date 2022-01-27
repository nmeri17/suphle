<?php
	namespace Tilwa\Controllers\DecoratorHandlers;

	use Tilwa\Contracts\Hydration\ScopeHandlers\ModifiesArguments;

	use Tilwa\Contracts\Services\Decorators\{SystemModelEdit, MultiUserModelEdit};

	use Tilwa\Routing\RequestDetails;

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

			foreach ($arguments as $dependency) {

				$satisfied = false;

				foreach ($this->postDecorators as $decorator) {

					if ($dependency instanceof $decorator)

						$satisfied = true;
				}
				if (!$satisfied)

					throw new MissingPostDecorator($dummyInstance);
			}

			return $arguments;
		}

		public function transformMethods ($concreteInstance, array $arguments):array {

			return $arguments;
		}
	}
?>
<?php
	namespace Suphle\Testing\Proxies;

	use Suphle\Hydration\Container;

	use Suphle\Testing\Condiments\MockFacilitator;

	use PHPUnit\Framework\TestCase;

	class WriteOnlyContainer extends TestCase { // so we can have access to the doubling methods

		use MockFacilitator;

		private $container;

		public function __construct (Container $container) {

			$this->container = $container;
		}

		public function replaceWithMock (
			string $interface, string $concrete, array $methodStubs,

			array $mockMethods = [], bool $retainOtherMethods = true
		):self {

			$doubleMode = $retainOtherMethods ? "positiveDouble":"negativeDouble";

			$this->container->whenTypeAny()->needsAny([

				$interface => $this->$doubleMode($concrete, $methodStubs, $mockMethods)
			]);

			return $this;
		}

		public function replaceWithConcrete (string $interface, object $concrete):self {

			$this->container->whenTypeAny()->needsAny([

				$interface => $concrete
			]);

			return $this;
		}
	}
?>
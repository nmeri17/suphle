<?php
	namespace Tilwa\Testing\Proxies;

	use Tilwa\Hydration\Container;

	use Tilwa\Testing\Condiments\MockFacilitator;

	use PHPUnit\Framework\TestCase;

	class WriteOnlyContainer extends TestCase { // so we can have access to the doubling methods

		use MockFacilitator;

		private $container;

		public function __construct (Container $container) {

			$this->container = $container;
		}

		public function replaceWithMock (string $interface, string $concrete, array $overrides, bool $retainOtherMethods = true):self {

			$this->container->whenTypeAny()->needsAny([

				$interface => $retainOtherMethods ?

					$this->positiveDouble($concrete, $overrides):

					$this->negativeDouble($concrete, $overrides)
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
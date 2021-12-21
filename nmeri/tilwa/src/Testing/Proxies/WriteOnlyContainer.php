<?php
	namespace Tilwa\Testing\Proxies;

	use Tilwa\Hydration\Container;

	use Tilwa\Testing\Condiments\MockFacilitator;

	/**
	 * Using a wrapper rather than an extension cuz we don't want Container's methods polluting the callback where this will be used
	*/
	class WriteOnlyContainer {

		use MockFacilitator;

		private $container;

		public function __construct () {

			$this->container = new Container;
		}

		public function replaceWithMock (string $interface, string $concrete, array $overrides, bool $retainOtherMethods = true):self {

			$this->container->whenTypeAny()->needsAny([

				$interface => $retainOtherMethods ?

					$this->positiveStub($concrete, $overrides):

					$this->negativeStub($concrete, $overrides)
			]);

			return $this;
		}

		public function replaceWithConcrete (string $interface, object $concrete):self {

			$this->container->whenTypeAny()->needsAny([

				$interface => $concrete
			]);

			return $this;
		}

		public function getContainer ():Container {

			return $this->container;
		}
	}
?>
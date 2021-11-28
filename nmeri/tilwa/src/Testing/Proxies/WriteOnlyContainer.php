<?php
	namespace Tilwa\Testing\Proxies;

	use Tilwa\App\Container;

	use Tilwa\Testing\Condiments\MockFacilitator;

	/**
	 * Using a wrapper than extension cuz we don't the container's methods leaking into the callback
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
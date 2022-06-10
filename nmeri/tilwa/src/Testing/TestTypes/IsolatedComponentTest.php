<?php
	namespace Tilwa\Testing\TestTypes;

	use Tilwa\Hydration\Container;

	use Tilwa\IO\{Session\InMemorySession, Cache\InMemoryCache};

	use Tilwa\Request\RequestDetails;

	use Tilwa\Contracts\IO\{Session, CacheManager};

	use Tilwa\Testing\Proxies\{GagsException, Extensions\CheckProvisionedClasses};

	/**
	 * Used for tests that mostly require a Container. Boots and provides this container to them
	*/
	abstract class IsolatedComponentTest extends TestVirginContainer {

		use GagsException {

			GagsException::setUp as mufflerSetup;
		}

		protected $container, $usesRealDecorator = false;

		protected function setUp ():void {

			$this->container = $container = $this->positiveDouble(

				CheckProvisionedClasses::class,

				$this->getContainerStubs()
			);

			$this->bootContainer($container);

			$this->withDefaultInterfaceCollection($container);

			$this->entityBindings();

			$this->maySetRealDecorator();

			$this->mayMonitorContainer($this->container);

			$this->mufflerSetup();
		}

		protected function entityBindings ():void {

			foreach ($this->concreteBinds() as $name => $concrete) // this goes first so if any of the simpleBinds below requires a concrete, it'll be available to it

				$this->container->whenTypeAny()->needsAny([

					$name => $concrete
				]);

			foreach ($this->simpleBinds() as $contract => $className) {

				$concrete = $this->container->getClass($className); // for some funny reason, this provision doesn't work except it's first stored in a variable

				$this->container->whenTypeAny()->needsAny([

					$contract => $concrete
				]);
			}
		}

		protected function simpleBinds ():array {

			return [

				Session::class => InMemorySession::class,

				CacheManager::class => InMemoryCache::class
			];
		}

		protected function concreteBinds ():array {

			return [];
		}

		// used for normalizing traits that are applicable to both this and module level test
		protected function getContainer ():Container {

			return $this->container;
		}

		protected function massProvide (array $provisions):void {

			$container = $this->container;

			foreach ($provisions as $parentType => $concrete)

				$container->refreshClass($parentType);

			$container->whenTypeAny()->needsAny($provisions);
		}

		private function getContainerStubs ():array {

			$stubs = [];

			if (!$this->usesRealDecorator)

				$stubs["getDecorator"] = $this->stubDecorator();

			return $stubs;
		}

		private function maySetRealDecorator ():void {

			if ($this->usesRealDecorator)

				$this->container->interiorDecorate();
		}

		protected function setRequestPath (string $requestPath):void {

			RequestDetails::fromContainer($this->container, $requestPath);
		}
	}
?>
<?php
	namespace Tilwa\Testing\TestTypes;

	use Tilwa\Hydration\Container;

	use Tilwa\Contracts\IO\Session;

	use Tilwa\IO\Session\InMemorySession;

	use Tilwa\Testing\{Condiments\GagsException, Proxies\Extensions\CheckProvisionedClasses};

	/**
	 * Used for tests that require a container. Boots and provides this container to them
	*/
	class IsolatedComponentTest extends TestVirginContainer {

		use GagsException {

			GagsException::setUp as mufflerSetup;
		}

		protected $container,

		$muffleExceptionBroadcast = true;

		protected function setUp ():void {

			$this->container = $container = $this->positiveDouble(CheckProvisionedClasses::class, [

				"getDecorator" => $this->stubDecorator()
			]);

			$this->bootContainer($container);

			$this->withDefaultInterfaceCollection($container);

			$this->entityBindings();

			if ($this->muffleExceptionBroadcast)

				$this->mufflerSetup();
		}

		protected function entityBindings ():void {

			foreach ($this->concreteBinds() as $name => $concrete) // this goes first so if any of the simpleBinds below requires a concrete, it'll be available to it

				$this->container->whenTypeAny()->needsAny([

					$name => $concrete
				]);

			foreach ($this->simpleBinds() as $contract => $className)

				$this->container->whenTypeAny()->needsAny([

					$contract => $this->container->getClass($className)
				]);
		}

		protected function simpleBinds ():array {

			return [

				Session::class => InMemorySession::class
			];
		}

		protected function concreteBinds ():array {

			$cacheManager = \Tilwa\Contracts\CacheManager::class;

			return [

				$cacheManager => $this->negativeDouble($cacheManager, [])
			];
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
	}
?>
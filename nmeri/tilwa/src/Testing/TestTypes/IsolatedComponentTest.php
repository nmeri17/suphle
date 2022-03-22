<?php
	namespace Tilwa\Testing\TestTypes;

	use Tilwa\Hydration\Container;

	use Tilwa\Testing\Condiments\GagsException;

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

			$this->container = $container = $this->positiveDouble(Container::class, [

				"getDecorator" => $this->stubDecorator()
			]);

			$this->bootContainer($container);

			$this->withDefaultInterfaceCollection($container);

			$this->entityBindings();

			if ($this->muffleExceptionBroadcast)

				$this->mufflerSetup();
		}

		protected function entityBindings ():void {

			foreach ($this->simpleBinds() as $contract => $className)

				$this->container->whenTypeAny()->needsAny([

					$contract => $this->container->getClass($className)
				]);

			foreach ($this->concreteBinds() as $name => $concrete)

				$this->container->whenTypeAny()->needsAny([

					$name => $concrete
				]);
		}

		protected function simpleBinds ():array {

			return [];
		}

		protected function concreteBinds ():array {

			return [];
		}

		// used for normalizing traits that are applicable to both this and module level test
		protected function getContainer ():Container {

			return $this->container;
		}

		protected function massProvide (array $provisions):void {

			$this->container->whenTypeAny()->needsAny($provisions);
		}
	}
?>
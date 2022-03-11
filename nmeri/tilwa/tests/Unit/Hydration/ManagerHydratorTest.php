<?php
	namespace Tilwa\Tests\Unit\Hydration;

	use Tilwa\Hydration\Container;

	use Tilwa\Contracts\Auth\UserContract;

	use Tilwa\Bridge\Laravel\Package\{ManagerHydrator, LaravelProviderManager};

	class ManagerHydratorTest extends TestVirginContainer {

		public function test_can_hydrate_bridge_package_manager () {

			$managerName = LaravelProviderManager::class;

			$container = $this->positiveDouble(Container::class, [

				"lastHydratedFor" => $managerName,

				"getDecorator" => $this->stubDecorator(),

				"getInterfaceHydrator" => $this->stubbedInterfaceCollection()
			]);

			$this->bootContainer($container);

			$this->withDefaultInterfaceCollection($container);

			$sut = new ManagerHydrator($container);

			$this->assertInstanceOf(

				$managerName, $sut->getManager() // when
			); // then
		}
	}
?>
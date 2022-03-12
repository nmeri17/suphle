<?php
	namespace Tilwa\Tests\Unit\Hydration;

	use Tilwa\Hydration\{Container, ExternalPackageManagerHydrator};

	use Tilwa\Bridge\Laravel\Package\LaravelProviderManager;

	class ManagerHydratorTest extends TestVirginContainer {

		public function test_can_set_bridge_package_manager () {

			$container = $this->positiveDouble(Container::class, [

				"getDecorator" => $this->stubDecorator()
			]);

			$sut = $this->positiveDouble(ExternalPackageManagerHydrator::class, [], [], compact("container") );

			$this->stubSingle([

				"getExternalContainerManager" => $sut
			], $container);

			$this->bootContainer($container);

			$this->withDefaultInterfaceCollection($container);

			$container->setExternalHydrators([

				LaravelProviderManager::class
			]); // when

			$this->assertClassHasAttribute(

				"managers", get_class($sut)
			); // then
		}

		protected function registerCoreBindings ($container, array $bindings = []) {

			$container->whenTypeAny()->needsAny(array_merge([

				ModuleFiles::class => ModuleFilesMock::class
			], $bindings));
		}
	}
?>
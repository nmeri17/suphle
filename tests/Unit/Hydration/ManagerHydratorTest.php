<?php
	namespace Suphle\Tests\Unit\Hydration;

	use Suphle\Hydration\{Container, ExternalPackageManagerHydrator, Structures\BaseInterfaceCollection};

	use Suphle\Contracts\Config\{ ModuleFiles, ContainerConfig};

	use Suphle\Contracts\Hydration\ExternalPackageManager;

	use Suphle\Config\AscendingHierarchy;

	use Suphle\File\FileSystemReader;

	use Suphle\Testing\TestTypes\TestVirginContainer;

	class ManagerHydratorTest extends TestVirginContainer {

		protected function getContainer ():Container {

			$container = $this->positiveDouble(Container::class, [

				"getDecorator" => $this->stubDecorator()
			]);

			$this->bootContainer($container);

			return $container;
		}

		protected function injectBindings (Container $container):void {

			$systemReader = $container->getClass(FileSystemReader::class);

			$anchorPath = $systemReader->pathFromLevels(__DIR__, "Mocks/Modules/ModuleOne/Config", 2);

			$manager = $this->positiveDouble(ExternalPackageManager::class, []);

			$container->whenTypeAny()->needsAny([

				ContainerConfig::class => $this->positiveDouble(ContainerConfig::class, [

					"getExternalHydrators" => [

						get_class($manager)
					]
				]),

				ModuleFiles::class => new AscendingHierarchy($anchorPath, "\Suphle\Tests\Mocks\Modules\ModuleOne\\", $systemReader)
			]);
		}

		public function test_can_set_bridge_package_manager () {

			$container = $this->getContainer();

			// given
			$this->injectBindings($container);

			$container->setInterfaceHydrator(

				get_class($this->positiveDouble(BaseInterfaceCollection::class, []))
			);

			$sut = new ExternalPackageManagerHydrator($container);

			$container->setExternalContainerManager($sut); // when

			$this->assertTrue($sut->hasManagers()); // then
		}
	}
?>
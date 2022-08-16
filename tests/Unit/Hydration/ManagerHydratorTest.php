<?php
	namespace Suphle\Tests\Unit\Hydration;

	use Suphle\Hydration\{Container, ExternalPackageManagerHydrator, Structures\BaseInterfaceCollection};

	use Suphle\Contracts\Config\{Router, ModuleFiles, ContainerConfig};

	use Suphle\Config\AscendingHierarchy;

	use Suphle\File\FileSystemReader;

	use Suphle\Bridge\Laravel\Package\LaravelProviderManager;

	use Suphle\Testing\TestTypes\TestVirginContainer;

	use Suphle\Tests\Mocks\Modules\ModuleOne\Config\RouterMock;

	class ManagerHydratorTest extends TestVirginContainer {

		protected function getContainer ():Container {

			$container = $this->positiveDouble(Container::class, [

				"getDecorator" => $this->stubDecorator()
			]);

			$this->bootContainer($container);

			return $container;
		}

		protected function getConfigBindings ():BaseInterfaceCollection {

			return new class extends BaseInterfaceCollection {

				public function getConfigs ():array {

					return array_merge(parent::getConfigs(), [

						Router::class => RouterMock::class
					]);
				}
			};
		}

		protected function injectBindings (Container $container):void {

			$systemReader = $container->getClass(FileSystemReader::class);

			$anchorPath = $systemReader->pathFromLevels(__DIR__, "Mocks/Modules/ModuleOne/Config", 2);

			$container->whenTypeAny()->needsAny([

				ContainerConfig::class => $this->positiveDouble(ContainerConfig::class, [

					"getExternalHydrators" => [

						LaravelProviderManager::class
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

				get_class($this->getConfigBindings())
			);

			$sut = new ExternalPackageManagerHydrator($container);

			$container->setExternalContainerManager($sut); // when

			$this->assertTrue($sut->hasManagers()); // then
		}
	}
?>
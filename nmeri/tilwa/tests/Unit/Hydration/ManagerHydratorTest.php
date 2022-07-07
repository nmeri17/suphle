<?php
	namespace Tilwa\Tests\Unit\Hydration;

	use Tilwa\Hydration\{Container, ExternalPackageManagerHydrator, Structures\BaseInterfaceCollection};

	use Tilwa\Contracts\Config\{Router, ModuleFiles};

	use Tilwa\Config\AscendingHierarchy;

	use Tilwa\File\FileSystemReader;

	use Tilwa\Testing\TestTypes\TestVirginContainer;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\Config\RouterMock;

	class ManagerHydratorTest extends TestVirginContainer {

		public function test_can_set_bridge_package_manager () {

			$container = $this->positiveDouble(Container::class, [

				"getDecorator" => $this->stubDecorator()
			]);

			$this->bootContainer($container);

			$newBindings = new class extends BaseInterfaceCollection {

				public function getConfigs ():array {

					return array_merge(parent::getConfigs(), [

						Router::class => RouterMock::class
					]);
				}
			};

			$container->setInterfaceHydrator(get_class($newBindings));

			$systemReader = $container->getClass(FileSystemReader::class);

			$anchorPath = $systemReader->pathFromLevels(__DIR__, "Mocks/Modules/ModuleOne/Config", 2);

			$container->whenTypeAny()->needsAny([

				ModuleFiles::class => new AscendingHierarchy($anchorPath, $systemReader)
			]);

			$sut = new ExternalPackageManagerHydrator($container);

			$container->setExternalContainerManager($sut); // when

			$this->assertTrue($sut->hasManagers()); // then
		}
	}
?>
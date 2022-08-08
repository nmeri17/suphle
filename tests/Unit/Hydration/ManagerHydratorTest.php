<?php
	namespace Suphle\Tests\Unit\Hydration;

	use Suphle\Hydration\{Container, ExternalPackageManagerHydrator, Structures\BaseInterfaceCollection};

	use Suphle\Contracts\Config\{Router, ModuleFiles};

	use Suphle\Config\AscendingHierarchy;

	use Suphle\File\FileSystemReader;

	use Suphle\Testing\TestTypes\TestVirginContainer;

	use Suphle\Tests\Mocks\Modules\ModuleOne\Config\RouterMock;

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

				ModuleFiles::class => new AscendingHierarchy($anchorPath, "\Suphle\Tests\Mocks\Modules\ModuleOne\\", $systemReader)
			]);

			$sut = new ExternalPackageManagerHydrator($container);

			$container->setExternalContainerManager($sut); // when

			$this->assertTrue($sut->hasManagers()); // then
		}
	}
?>
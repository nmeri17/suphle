<?php
	namespace Suphle\Tests\Integration\Services\DependencySanitization;

	use Suphle\Hydration\{Container, Structures\ObjectDetails};

	use Suphle\Server\DependencySanitizer;

	use Suphle\Testing\TestTypes\IsolatedComponentTest;

	use Suphle\Tests\Integration\Generic\CommonBinds;

	use Suphle\Tests\Mocks\Modules\ModuleOne\Meta\ModuleOneDescriptor;

	abstract class TestSanitization extends IsolatedComponentTest {

		use CommonBinds;

		protected DependencySanitizer $sanitizer;

		protected ObjectDetails $objectMeta;

		protected function setUp ():void {

			parent::setUp();

			$container = $this->getContainer();

			$this->sanitizer = $container->getClass(DependencySanitizer::class);

			$this->objectMeta = $container->getClass(ObjectDetails::class);

			$this->setSanitizationPath();
		}

		/*protected function getModules ():array {

			return [new ModuleOneDescriptor(new Container)];
		}*/

		abstract protected function setSanitizationPath ():void;

		protected function getClassDir (string $className):string {

			return dirname($this->objectMeta->getReflectedClass($className)

			->getFileName());
		}

		protected function escapeClassName (string $className):string {

			return "/". str_replace("\\", "\\\\", $className) . "/";
		}
	}
?>
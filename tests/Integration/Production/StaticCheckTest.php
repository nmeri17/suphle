<?php
	namespace Suphle\Tests\Integration\Production;

	use Suphle\Hydration\Container;

	use Suphle\File\FileSystemReader;

	use Suphle\Server\PsalmWrapper;

	use Suphle\Testing\TestTypes\ModuleLevelTest;

	use Suphle\Tests\Mocks\Modules\ModuleOne\Meta\ModuleOneDescriptor;

	use Suphle\Tests\Mocks\Modules\ModuleOne\Concretes\StaticChecks\{UsesNonMatchingTypes, ContainsError};

	use ReflectionClass;

	class StaticCheckTest extends ModuleLevelTest {

		protected function getModules ():array {

			return [new ModuleOneDescriptor(new Container)];
		}

		public function test_offers_to_rectify_violations () {

			$psalmWrapper = $this->getPsalmWrapper();

			$scanStatus = $psalmWrapper->scanForErrors(

				[$this->getClassPath(UsesNonMatchingTypes::class)] // given
			); // when

			$this->assertTrue($scanStatus); // then

			$haystack = $psalmWrapper->getLastProcess()->getOutput();

			$this->assertStringContainsString(PsalmWrapper::ALTER_OPTION, $haystack);
		}

		protected function getClassPath (string $className):string {

			return (new ReflectionClass($className))->getFileName();
		}

		protected function getPsalmWrapper ():PsalmWrapper {

			$container = $this->getContainer();

			$vendorDir = $container->getClass(FileSystemReader::class)

			->pathFromLevels($_SERVER["COMPOSER_RUNTIME_BIN_DIR"], "", 2);

			$wrapper = $container->getClass(PsalmWrapper::class);

			$wrapper->setExecutionPath($vendorDir);

			$wrapper->initPsalm();

			return $wrapper;
		}

		public function test_file_with_error_returns_false () {

			$scanStatus = $this->getPsalmWrapper()->scanForErrors(

				[$this->getClassPath(ContainsError::class)] // given
			); // when

			$this->assertFalse($scanStatus); // then
		}
	}
?>
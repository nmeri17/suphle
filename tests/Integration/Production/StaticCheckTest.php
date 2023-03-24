<?php
	namespace Suphle\Tests\Integration\Production;

	use Suphle\Hydration\Container;

	use Suphle\Server\PsalmWrapper;

	use Suphle\Testing\{TestTypes\ModuleLevelTest, Proxies\RealVendorPath};

	use Suphle\Tests\Mocks\Modules\ModuleOne\Meta\ModuleOneDescriptor;

	use Suphle\Tests\Mocks\Modules\ModuleOne\Concretes\StaticChecks\{UsesNonMatchingTypes, ContainsError};

	use ReflectionClass;

	class StaticCheckTest extends ModuleLevelTest {

		use RealVendorPath;

		protected function getModules ():array {

			return [new ModuleOneDescriptor(new Container)];
		}

		public function test_offers_to_rectify_violations () {

			$psalmWrapper = $this->getPsalmWrapper();

			$scanStatus = $psalmWrapper->analyzeErrorStatus(

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

			$wrapper = $this->getContainer()->getClass(PsalmWrapper::class);

			$wrapper->setExecutionPath($this->getVendorPath());

			$wrapper->scanConfigLevel();

			return $wrapper;
		}

		public function test_file_with_error_returns_false () {

			$scanStatus = $this->getPsalmWrapper()->analyzeErrorStatus(

				[$this->getClassPath(ContainsError::class)] // given
			); // when

			$this->assertFalse($scanStatus); // then
		}
	}
?>
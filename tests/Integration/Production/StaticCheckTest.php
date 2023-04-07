<?php
	namespace Suphle\Tests\Integration\Production;

	use Suphle\Hydration\Container;

	use Suphle\Server\PsalmWrapper;

	use Suphle\Testing\{TestTypes\ModuleLevelTest, Proxies\RealVendorPath};

	use Suphle\Tests\Mocks\Modules\ModuleOne\Meta\ModuleOneDescriptor;

	use Suphle\Tests\Mocks\Modules\ModuleOne\Concretes\StaticChecks\ContainsError;

	use ReflectionClass;

	class StaticCheckTest extends ModuleLevelTest {

		use RealVendorPath;

		protected function getModules ():array {

			return [new ModuleOneDescriptor(new Container)];
		}

		protected function getClassPath (string $className):string {

			return (new ReflectionClass($className))->getFileName();
		}

		protected function getPsalmWrapper ():PsalmWrapper {

			$wrapper = $this->getContainer()->getClass(PsalmWrapper::class);

			$wrapper->setExecutionPath($this->getVendorParent(), "Modules");

			return $wrapper;
		}

		public function test_file_with_error_returns_false () {

			$this->setOutputCallback(fn () => null); // mute output/report by psalm process

			$scanStatus = $this->getPsalmWrapper()->analyzeErrorStatus( // when

				[$this->getClassPath(ContainsError::class)], // given

				false // important to run in this mode since we can't set a path to a specific file; thus, it'll attempt to repair everything
			);

			$this->assertFalse($scanStatus); // then
		}
	}
?>
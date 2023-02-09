<?php
	namespace Suphle\Tests\Integration\Services\Proxies;

	use Suphle\Testing\TestTypes\ModuleLevelTest;

	use Suphle\Exception\Explosives\Generic\MissingPostDecorator;

	use Suphle\Tests\Mocks\Modules\ModuleOne\Coordinators\ValidatorCoordinator;

	use Suphle\Tests\Integration\Services\ReplacesRequestPayload;

	class SecuresPutRequestTest extends ModuleLevelTest {

		use ReplacesRequestPayload;

		public function test_missing_types_throws_errors () {

			$this->expectException(MissingPostDecorator::class); // then

			$this->stubRequestObjects(

				7, [], $this->stubRequestMethod("put")
			); // given

			$this->getContainer()->getClass(ValidatorCoordinator::class); // when
		}
	}
?>
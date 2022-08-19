<?php
	namespace Suphle\Tests\Integration\Services\Proxies;

	use Suphle\Testing\{TestTypes\IsolatedComponentTest, Condiments\DirectHttpTest};

	use Suphle\Exception\Explosives\Generic\MissingPostDecorator;

	use Suphle\Tests\Integration\Generic\CommonBinds;

	use Suphle\Tests\Mocks\Modules\ModuleOne\Controllers\ValidatorController;

	class SecuresPutRequestTest extends IsolatedComponentTest {

		use DirectHttpTest, CommonBinds;

		public function test_missing_types_throws_errors () {

			$this->expectException(MissingPostDecorator::class); // then

			$this->setHttpParams("/dummy", "put"); // given

			$this->container->getClass(ValidatorController::class); // when
		}
	}
?>
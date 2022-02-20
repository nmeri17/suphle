<?php
	namespace Tilwa\Tests\Integration\Services\Proxies;

	use Tilwa\Testing\{TestTypes\IsolatedComponentTest, Condiments\DirectHttpTest};

	use Tilwa\Exception\Explosives\Generic\MissingPostDecorator;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\Controllers\ValidatorController;

	class SecuresPutRequestTest extends IsolatedComponentTest {

		use DirectHttpTest;

		public function test_missing_types_throws_errors () {

			$this->setExpectedException(MissingPostDecorator::class); // then

			$this->setHttpParams("/dummy", "put"); // given

			$this->container->getClass(ValidatorController::class); // when
		}
	}
?>
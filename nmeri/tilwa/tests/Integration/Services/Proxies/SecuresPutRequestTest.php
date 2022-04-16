<?php
	namespace Tilwa\Tests\Integration\Services\Proxies;

	use Tilwa\Testing\{TestTypes\IsolatedComponentTest, Condiments\DirectHttpTest};

	use Tilwa\Exception\Explosives\Generic\MissingPostDecorator;

	use Tilwa\Tests\Integration\Generic\CommonBinds;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\Controllers\ValidatorController;

	class SecuresPutRequestTest extends IsolatedComponentTest {

		use DirectHttpTest, CommonBinds;

		public function test_missing_types_throws_errors () {

			$this->expectException(MissingPostDecorator::class); // then

			$this->setHttpParams("/dummy", "put"); // given

			$this->container->getClass(ValidatorController::class); // when
		}
	}
?>
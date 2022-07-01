<?php
	namespace Tilwa\Tests\Integration\Services\CoodinatorManager;

	use Tilwa\Services\CoodinatorManager;

	use Tilwa\Testing\TestTypes\IsolatedComponentTest;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\{Meta\ModuleOneDescriptor, Controllers\BaseController};

	use Tilwa\Tests\Integration\Generic\CommonBinds;

	use InvalidArgumentException;

	class ActionArgumentsTest extends IsolatedComponentTest {

		use CommonBinds;

		protected $usesRealDecorator = true;

		public function test_action_method_rejects_unwanted_dependencies () {

			$this->expectException(InvalidArgumentException::class); // then

			$controller = $this->positiveDouble(BaseController::class);

			$this->container->getClass(CoodinatorManager::class)

			->setDependencies ( $controller, "incorrectActionInjection") // given

			->setHandlerParameters(); // when
		}
	}
?>
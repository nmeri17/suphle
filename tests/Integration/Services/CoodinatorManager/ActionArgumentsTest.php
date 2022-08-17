<?php
	namespace Suphle\Tests\Integration\Services\CoodinatorManager;

	use Suphle\Services\CoodinatorManager;

	use Suphle\Testing\TestTypes\IsolatedComponentTest;

	use Suphle\Tests\Mocks\Modules\ModuleOne\{Meta\ModuleOneDescriptor, Controllers\BaseController};

	use Suphle\Tests\Integration\Generic\CommonBinds;

	use InvalidArgumentException;

	class ActionArgumentsTest extends IsolatedComponentTest {

		use CommonBinds;

		public function test_action_method_rejects_unwanted_dependencies () {

			$this->expectException(InvalidArgumentException::class); // then

			$controller = $this->positiveDouble(BaseController::class);

			$this->container->getClass(CoodinatorManager::class)

			->setDependencies ( $controller, "incorrectActionInjection") // given

			->setHandlerParameters(); // when
		}
	}
?>
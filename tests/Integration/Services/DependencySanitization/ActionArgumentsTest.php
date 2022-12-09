<?php
	namespace Suphle\Tests\Integration\Services\DependencySanitization;

	use Suphle\Tests\Mocks\Modules\ModuleOne\Coordinators\BaseCoordinator;

	use InvalidArgumentException;

	class ActionArgumentsTest extends TestSanitization {

		protected const COORDINATOR_NAME = BaseCoordinator::class;

		protected function setSanitizationPath ():void {

			$this->sanitizer->setExecutionPath($this->getClassDir(self::COORDINATOR_NAME));
		}

		public function test_action_method_rejects_unwanted_dependencies () {

			$this->expectException(InvalidArgumentException::class); // then

			$this->expectExceptionMessageMatches(

				$this->escapeClassName(self::COORDINATOR_NAME)
			);

			// given 1 @see setSanitizationPath
			
			$this->sanitizer->coordinatorActionMethods(); // given 2

			$this->sanitizer->cleanseConsumers(); // when
		}
	}
?>
<?php
	namespace Tilwa\Tests\Integration\Services\Proxies;

	use Tilwa\Request\PayloadStorage;

	use Tilwa\Testing\TestTypes\IsolatedComponentTest;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\Concretes\DatalessErrorThrower;

	use Tilwa\Exception\Explosives\NotFoundException;

	class ServiceErrorCatcherTest extends IsolatedComponentTest {

		private $serviceName = DatalessErrorThrower::class;

		public function test_successful_call_returns_value () {

			$value = 55;

			$dto = $this->container->getClass($this->serviceName)

			->setCorrectValue($value); // when

			$this->assertSame($dto->operationValue(), $value); // then
		}

		public function test_failed_call_returns_default_type () {

			$dto = $this->container->getClass($this->serviceName)

			->notCaughtInternally(); // when

			$this->assertSame($dto->operationValue(), 0); // then
		}

		/**
		 * @dataProvider failureStateMethods
		*/
		public function test_failureState_replaces_error (string $methodName) {

			$container = $this->container;

			$this->assertCaughtPayload($container->getClass(PayloadStorage::class)); // then 1

			$sut = $container->getClass($this->serviceName);

			$result = call_user_func([$sut, $methodName]); // when

			$this->assertTrue($result->hasErrors()); // then 2
		}

		public function failureStateMethods ():array {

			return [
				["deliberateError"],

				["deliberateException"]
			];
		}

		public function test_can_rethrow_exceptions () {

			$this->setExpectedException(NotFoundException::class); // then

			$this->container->getClass($this->serviceName)->terminateRequest(); // when
		}
	}
?>
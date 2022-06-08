<?php
	namespace Tilwa\Tests\Integration\Services\Proxies;

	use Tilwa\Hydration\Container;

	use Tilwa\Request\PayloadStorage;

	use Tilwa\Exception\Explosives\NotFoundException;

	use Tilwa\Contracts\Modules\DescriptorInterface;

	use Tilwa\Testing\TestTypes\InvestigateSystemCrash;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\{Concretes\Services\DatalessErrorThrower, Meta\ModuleOneDescriptor};

	class ServiceErrorCatcherTest extends InvestigateSystemCrash {

		private $serviceName = DatalessErrorThrower::class,

		$container;

		protected function setUp ():void {

			parent::setUp();

			$this->container = $this->getContainer();
		}

		protected function getModule ():DescriptorInterface {

			return new ModuleOneDescriptor (new Container);
		}

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

			$this->assertWillCatchPayload( // then 1

				$this->container->getClass(PayloadStorage::class),

				function () use ($methodName) {

					$sut = $this->container->getClass($this->serviceName);

					$result = call_user_func([$sut, $methodName]); // when

					$this->assertTrue($result->hasErrors()); // then 2
				}
			);
		}

		public function failureStateMethods ():array {

			return [
				["deliberateError"],

				["deliberateException"]
			];
		}

		public function test_can_rethrow_exceptions () {

			$this->expectException(NotFoundException::class); // then

			$this->container->getClass($this->serviceName)->terminateRequest(); // when
		}
	}
?>
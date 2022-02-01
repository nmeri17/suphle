<?php
	namespace Tilwa\Tests\Integration\Services\Proxies;

	use Tilwa\Testing\{TestTypes\IsolatedComponentTest, Condiments\MockFacilitator};

	use Tilwa\Tests\Mocks\Modules\ModuleOne\Concretes\DatalessErrorThrower;

	use Tilwa\Contracts\Exception\AlertAdapter;

	use Tilwa\Exception\Explosives\NotFoundException;

	class ServiceErrorCatcherTest extends IsolatedComponentTest {

		use MockFacilitator;

		private $serviceName = DatalessErrorThrower::class,

		$alerter;

		public function setUp ():void {

			parent::setUp();

			$this->alerter = $this->negativeStub(AlertAdapter::class);

			$this->container->whenTypeAny()->needsArguments([

				AlertAdapter::class => $this->alerter
			]);
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

			$sut = $this->container->getClass($this->serviceName);

			$result = call_user_func([$sut, $methodName]); // when

			// then
			$this->alerter->expects($this->once())->method($methodName)
			
			->with( $this->anything(), $this->anything() );

			$this->assertTrue($result->hasErrors());
		}

		public function failureStateMethods ():array {

			return [
				["deliberateError"],

				["deliberateException"]
			];
		}

		public function test_can_rethrow_exceptions () {

			$this->setExpectedException(NotFoundException::class); // then

			$dto = $this->container->getClass($this->serviceName)

			->terminateRequest(); // when
		}
	}
?>
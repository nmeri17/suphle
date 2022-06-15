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

		$container, $payloadStorage;

		protected function setUp ():void {

			parent::setUp();

			$this->container = $this->getContainer();

			$this->payloadStorage = $this->container->getClass(PayloadStorage::class);
		}

		protected function getModule ():DescriptorInterface {

			return new ModuleOneDescriptor (new Container);
		}

		public function test_failed_call_returns_default_type () {

			$default = 0;

			$operationResult = $this->container->getClass($this->serviceName)

			->notCaughtInternally(); // when

			$this->assertSame($default, $operationResult); // then
		}

		/**
		 * @dataProvider failureStateMethods
		*/
		public function test_failureState_replaces_return_value_on_error (string $methodName) {

			$sut = $this->container->getClass($this->serviceName);

			$result = $this->assertWontCatchPayload(

				$this->payloadStorage, [$sut, $methodName]
			); // when

			// then
			$this->assertSame($methodName, $result);

			$this->assertTrue($sut->matchesErrorMethod($methodName));
		}

		protected function broadcasterArguments ():array {

			return [

				"payloadStorage" => $this->payloadStorage
			];
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
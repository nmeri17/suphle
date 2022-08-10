<?php
	namespace Suphle\Tests\Integration\Exception;

	use Suphle\Contracts\Modules\DescriptorInterface;

	use Suphle\Testing\{TestTypes\InvestigateSystemCrash, Proxies\WriteOnlyContainer};

	use Suphle\Tests\Integration\Modules\DoublesHandlerIdentifier;

	use Suphle\Tests\Mocks\Modules\ModuleOne\Meta\ModuleOneDescriptor;

	use Exception;

	class HandlerIdentifierShutdownTest extends InvestigateSystemCrash {

		use DoublesHandlerIdentifier;

		protected function getModule ():DescriptorInterface {

			return new ModuleOneDescriptor(new \Suphle\Hydration\Container);
		}

		public function test_fatal_exception_shutsdown_gracefully () {

			$this->bindBroadcastAlerter(1, []);// then @see module injection

			$willThrow = $this->returnCallback(function () {

				throw new Exception;
			});

			$this->getHandlerIdentifier([ // given

				"respondFromHandler" => $willThrow,

				"findExceptionRenderer" => $willThrow
			])

			->diffuseSetResponse(false); // when
		}
	}
?>
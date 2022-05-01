<?php
	namespace Tilwa\Tests\Integration\Exception;

	use Tilwa\Hydration\Container;

	use Tilwa\Exception\Explosives\NotFoundException;

	use Tilwa\Request\PayloadStorage;

	use Tilwa\Contracts\Config\ExceptionInterceptor;

	use Tilwa\Testing\TestTypes\InvestigateSystemCrash;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\Meta\ModuleOneDescriptor;

	use Exception;

	class TerminatedRequestTest extends InvestigateSystemCrash {

		protected function getModules ():array {

			return [new ModuleOneDescriptor(new Container)];
		}

		public function test_exceptions_uses_assigned_handler () {

			$exceptionName = NotFoundException::class;

			$this->exceptionModuleHandler(

				$this->throwException(new $exceptionName) // given
			)
			->diffusedRequestResponse(); // when

			$container = $this->getContainer();

			$diffuser = $container->getClass(ExceptionInterceptor::class)->getHandlers()[$exceptionName];

			$this->assertExceptionUsesRenderer(
			
				$container->getClass($diffuser)->getRenderer()
			); // then
		}

		public function test_exceptions_without_assigned_handler_uses_default () {

			$this->exceptionModuleHandler(

				$this->throwException(new Exception) // given
			)
			->diffusedRequestResponse(); // when

			$container = $this->getContainer();

			$defaultHandler = $container->getClass(ExceptionInterceptor::class)->defaultHandler();

			$this->assertExceptionUsesRenderer(
			
				$container->getClass($defaultHandler)->getRenderer()
			); // then
		}

		public function test_fatal_exception_shutsdown_gracefully () {

			$this->assertWillCatchPayload($this->getContainer()->getClass(PayloadStorage::class)); // then

			$this->exceptionModuleHandler(

				$this->returnCallback(function () { // given

					trigger_error("waterloo", E_USER_ERROR);
				})
			)->diffusedRequestResponse(); // when
		}
	}
?>
<?php
	namespace Tilwa\Tests\Integration\Exception;

	use Tilwa\Hydration\Container;

	use Tilwa\Exception\Explosives\NotFoundException;

	use Tilwa\Request\PayloadStorage;

	use Tilwa\Response\Format\Markup;

	use Tilwa\Contracts\{Config\ExceptionInterceptor, Modules\DescriptorInterface};

	use Tilwa\Testing\TestTypes\InvestigateSystemCrash;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\Meta\ModuleOneDescriptor;

	use Exception;

	class TerminatedRequestTest extends InvestigateSystemCrash {

		private $exceptionConfig, $payloadStorage;

		protected function setUp ():void {

			parent::setUp();

			$container = $this->getContainer();

			$this->exceptionConfig = $container->getClass(ExceptionInterceptor::class);

			$this->payloadStorage = $container->getClass(PayloadStorage::class);
		}

		protected function getModule ():DescriptorInterface {

			return new ModuleOneDescriptor(new Container);
		}

		public function test_exceptions_uses_assigned_handler () {

			$this->assertExceptionUsesRenderer( // then
			
				new Markup("missingHandler", "errors/not-found"),

				function () {

					throw new NotFoundException; // when
				}
			);
		}

		public function test_exceptions_without_assigned_handler_uses_default () {

			$this->assertExceptionUsesRenderer( // then
			
				new Markup("genericHandler", "/errors/default"),

				function () {

					throw new Exception; // when
				}
			);
		}

		public function test_fatal_exception_shutsdown_gracefully () {

			$this->assertWillCatchPayload(

				$this->payloadStorage,

				function () {

					throw new Exception; // when
				}
			); // then
		}

		protected function broadcasterArguments ():array {

			return ["payloadStorage" => $this->payloadStorage];
		}
	}
?>
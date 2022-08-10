<?php
	namespace Suphle\Tests\Integration\Exception;

	use Suphle\Hydration\Container;

	use Suphle\Exception\Explosives\NotFoundException;

	use Suphle\Request\PayloadStorage;

	use Suphle\Response\Format\Markup;

	use Suphle\Contracts\{Config\ExceptionInterceptor, Modules\DescriptorInterface};

	use Suphle\Testing\TestTypes\InvestigateSystemCrash;

	use Suphle\Tests\Mocks\Modules\ModuleOne\Meta\ModuleOneDescriptor;

	use Exception;

	class TerminatedRequestTest extends InvestigateSystemCrash {

		protected function getModule ():DescriptorInterface {

			return new ModuleOneDescriptor(new Container);
		}

		public function test_exceptions_uses_assigned_handler () {

			$this->assertExceptionUsesRenderer( // then
			
				new Markup("missingHandler", ""),

				function () {

					throw new NotFoundException; // when
				}
			);
		}

		public function test_exceptions_without_assigned_handler_uses_default () {

			$this->assertExceptionUsesRenderer( // then
			
				new Markup("genericHandler", "errors/default"),

				function () {

					throw new Exception; // when
				}
			);
		}
	}
?>
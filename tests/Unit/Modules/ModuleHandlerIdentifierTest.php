<?php
	namespace Suphle\Tests\Unit\Modules;

	use Suphle\Modules\ModuleHandlerIdentifier;

	use Suphle\Hydration\Container;

	use Suphle\Exception\Explosives\ValidationFailure;

	use Suphle\Contracts\Auth\{ModuleLoginHandler, LoginRenderers};

	use Suphle\Contracts\Config\AuthContract;

	use Suphle\Testing\TestTypes\ModuleLevelTest;

	use Suphle\Tests\Mocks\Modules\ModuleOne\Meta\ModuleOneDescriptor;

	class ModuleHandlerIdentifierTest extends ModuleLevelTest {

		protected function getModules ():array {

			return [new ModuleOneDescriptor (new Container)];
		}
		
		public function test_validation_failure_on_login_will_terminate () {

			$this->expectException(ValidationFailure::class); // then

			$sutName = ModuleLoginHandler::class;

			$this->massProvide([

				$sutName => $this->negativeDouble($sutName, [

					"isValidRequest" => false // given
				]),

				AuthContract::class => $this->positiveDouble(AuthContract::class, [

					"getLoginCollection" => $this->negativeDouble(LoginRenderers::class)
				])
			]);

			$this->entrance->extractFromContainer(); // refresh for above [given]

			$this->entrance->handleLoginRequest(); // when
		}
	}
?>
<?php
	namespace Tilwa\Tests\Unit\Modules;

	use Tilwa\Modules\ModuleHandlerIdentifier;

	use Tilwa\Hydration\Container;

	use Tilwa\Exception\Explosives\ValidationFailure;

	use Tilwa\Contracts\Auth\{ModuleLoginHandler, LoginRenderers};

	use Tilwa\Contracts\Config\AuthContract;

	use Tilwa\Testing\{TestTypes\ModuleLevelTest, Condiments\MockFacilitator};

	use Tilwa\Tests\Mocks\Modules\ModuleOne\Meta\ModuleOneDescriptor;

	class ModuleHandlerIdentifierTest extends ModuleLevelTest {

		use MockFacilitator;

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

					"getLoginCollection" => $this->negativeDouble(LoginRenderers::class, [])
				])
			]);

			$this->entrance->extractFromContainer(); // refresh for above [given]

			$this->entrance->handleLoginRequest(); // when
		}
	}
?>
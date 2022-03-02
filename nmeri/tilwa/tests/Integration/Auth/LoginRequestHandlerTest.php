<?php
	namespace Tilwa\Tests\Integration\Auth;

	use Tilwa\Hydration\Container;

	use Tilwa\Contracts\Auth\ModuleLoginHandler;

	use Tilwa\Exception\Explosives\ValidationFailure;

	use Tilwa\Testing\TestTypes\ModuleLevelTest;

	use Tilwa\Testing\Condiments\{DirectHttpTest, MockFacilitator};

	use Tilwa\Tests\Mocks\Modules\ModuleOne\Meta\ModuleOneDescriptor;

	class LoginRequestHandlerTest extends ModuleLevelTest {

		use DirectHttpTest, MockFacilitator;

		private $email = "foo@nmeri.com";

		public function getModules ():array {

			return new ModuleOneDescriptor(new Container);
		}

		public function test_invalid_payload_terminates_request () {

			$this->setExpectedException(ValidationFailure::class); // then

			$this->setJsonParams("/login", [

				"email" => $this->email
			]); // given

			$this->entrance->handleLoginRequest(); // when
		}

		public function test_valid_payload_tries_getting_response () {

			$this->setJsonParams("/login", [

				"email" => $this->email,

				"password" => "alphon123"
			]); // given

			$sutName = ModuleLoginHandler::class;

			$this->massProvide([

				$sutName => $this->positiveStub($sutName)->expects($this->once())

					->method("getResponse")->with($this->anything()) // then
			]);

			$this->entrance->extractFromContainer();

			$this->entrance->handleLoginRequest(); // when
		}
	}
?>
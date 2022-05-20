<?php
	namespace Tilwa\Tests\Integration\Auth;

	use Tilwa\Hydration\Container;

	use Tilwa\Contracts\Auth\ModuleLoginHandler;

	use Tilwa\Exception\Explosives\ValidationFailure;

	use Tilwa\Testing\{Condiments\DirectHttpTest, TestTypes\ModuleLevelTest};

	use Tilwa\Tests\Mocks\Modules\ModuleOne\Meta\ModuleOneDescriptor;

	class LoginRequestHandlerTest extends ModuleLevelTest {

		use DirectHttpTest;

		private $email = "foo@nmeri.com";

		public function getModules ():array {

			return [
				
				new ModuleOneDescriptor(new Container)
			];
		}

		public function test_invalid_payload_terminates_request () {

			$this->expectException(ValidationFailure::class); // then

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

				$sutName => $this->positiveDouble($sutName, [], [

					"processLoginRequest" => [1, []]
				]) // then
			]);

			$this->entrance->extractFromContainer();

			$this->entrance->handleLoginRequest(); // when
		}
	}
?>
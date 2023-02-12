<?php
	namespace Suphle\Tests\Integration\Bridge\Laravel;

	use Suphle\Contracts\{Bridge\LaravelContainer, Config\Router};

	use Suphle\Testing\{TestTypes\ModuleLevelTest, Proxies\WriteOnlyContainer};

	use Suphle\Security\CSRF\CsrfGenerator;

	use Suphle\Tests\Mocks\Modules\ModuleOne\{Meta\ModuleOneDescriptor, Config\RouterMock, Routes\ValidatorCollection};

	use Illuminate\Http\Request as LaravelRequest;

	class RequestEventTest extends ModuleLevelTest {

		protected bool $debugCaughtExceptions = true;

		protected function getModules ():array {

			return [

				$this->replicateModule(ModuleOneDescriptor::class, function (WriteOnlyContainer $container) {

					$container->replaceWithMock(Router::class, RouterMock::class, [
			
						"browserEntryRoute" => ValidatorCollection::class
					]);
				})
			];
		}

		public function test_their_container_sees_change_in_our_requests () {

			// given
			$url1 = "get-without";

			$url2 = "post-with-json";

			$payload = ["foo" => "udu bunch"];

			$this->get("/$url1"); // when

			$requestObject = $this->getTheirRequest();

			$this->assertSame($url1, $requestObject->path($url1)); // then 1

			$this->post("/$url2", array_merge([

				CsrfGenerator::TOKEN_FIELD => $this->getContainer()

				->getClass(CsrfGenerator::class)->newToken()
			], $payload)) // when

			->assertOk(); // sanity check

			$requestObject = $this->getTheirRequest();

			// then
			$this->assertSame($url2, $requestObject->path($url2));

			$this->assertSame($payload["foo"], $requestObject->get("foo"));
		}

		protected function getTheirRequest ():LaravelRequest {

			return $this->getContainer()->getClass(LaravelContainer::class)

			->make(LaravelContainer::INCOMING_REQUEST_KEY);
		}
	}
?>
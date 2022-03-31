<?php
	namespace Tilwa\Tests\Integration\Auth;

	use Tilwa\Auth\{Repositories\BrowserAuthRepo, Renderers\BrowserLoginRenderer, Storage\SessionStorage};

	use Tilwa\Routing\RouteManager;

	use Tilwa\Contracts\Presentation\BaseRenderer;

	class BrowserAuthRepoTest extends TestLoginRepo {

		const LOGIN_PATH = "/login";

		private $sutName = BrowserLoginRenderer::class;

		public function test_successLogin () {

			$loginService = $this->negativeDouble(BrowserAuthRepo::class);

			$this->container->whenTypeAny()->needsAny([

				$this->sutName => $this->negativeDouble($this->sutName, [

					"getLoginService" => $loginService,

					"successRenderer" => $this->negativeDouble(\Tilwa\Contracts\Presentation\BaseRenderer::class, [

						"getController" => $loginService
					])
				], [

					"successRenderer" => [1, []] // then
				])
			]);

			$this->sendCorrectRequest(self::LOGIN_PATH); // given

			$this->getLoginResponse(); // when
		}

		protected function concreteBinds ():array {

			return array_merge(parent::concreteBinds(), [

				RouteManager::class => $this->replaceConstructorArguments(RouteManager::class, [], [

					"getPreviousRenderer" => $this->positiveDouble(BaseRenderer::class )
				])
			]);
		}

		public function test_failedLogin () {

			$this->container->whenTypeAny()->needsAny([

				$this->sutName => $this->positiveDouble($this->sutName, [], [

					"successRenderer" => [1, []]
				])
			]); // then

			$this->sendIncorrectRequest(self::LOGIN_PATH); // given

			$this->getLoginResponse(); // when
		}

		public function test_cant_access_api_auth_route_with_session () {

			$user = $this->replicator->getRandomEntity();

			$this->actingAs($user, SessionStorage::class); // given

			$this->get("/api/v1/secure-segment") // when

			->assertUnauthorized(); // then
		}
	}
?>
<?php
	namespace Tilwa\Tests\Integration\Auth;

	use Tilwa\Auth\{ Renderers\BrowserLoginRenderer, Storage\SessionStorage};

	use Tilwa\Routing\RouteManager;

	use Tilwa\Contracts\Presentation\BaseRenderer;

	class BrowserLoginRendererTest extends TestLoginRenderer {

		const LOGIN_PATH = "/login";

		protected $loginRendererName = BrowserLoginRenderer::class;

		public function test_successLogin () {

			$this->injectLoginRenderer(1, 0); // then

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

			$this->injectLoginRenderer(0, 1); // then

			$this->sendIncorrectRequest(self::LOGIN_PATH); // given

			$this->getLoginResponse(); // when
		}
	}
?>
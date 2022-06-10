<?php
	namespace Tilwa\Tests\Integration\Auth\Bases;

	use Tilwa\Auth\{Renderers\BrowserLoginRenderer, Repositories\BrowserAuthRepo};

	use Tilwa\Testing\TestTypes\IsolatedComponentTest;

	class BaseTestBrowserLoginRenderer extends IsolatedComponentTest {

		use TestLoginRenderer;

		const LOGIN_PATH = "/login";

		protected function loginRendererName ():string {

			return BrowserLoginRenderer::class;
		}

		protected function loginRepoService ():string {

			return BrowserAuthRepo::class;
		}
	}
?>
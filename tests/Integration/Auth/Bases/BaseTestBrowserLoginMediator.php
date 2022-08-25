<?php
	namespace Suphle\Tests\Integration\Auth\Bases;

	use Suphle\Auth\{Renderers\BrowserLoginRenderer, Repositories\BrowserAuthRepo};

	use Suphle\Testing\TestTypes\IsolatedComponentTest;

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
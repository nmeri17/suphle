<?php
	namespace Suphle\Tests\Integration\Auth\Bases;

	use Suphle\Auth\{Renderers\BrowserLoginMediator, Repositories\BrowserAuthRepo};

	use Suphle\Testing\TestTypes\IsolatedComponentTest;

	class BaseTestBrowserLoginMediator extends IsolatedComponentTest {

		use TestLoginMediator;

		final const LOGIN_PATH = "/login";

		protected function loginRendererName ():string {

			return BrowserLoginMediator::class;
		}

		protected function loginRepoService ():string {

			return BrowserAuthRepo::class;
		}
	}
?>
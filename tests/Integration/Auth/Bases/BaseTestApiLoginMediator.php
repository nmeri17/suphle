<?php
	namespace Suphle\Tests\Integration\Auth\Bases;

	use Suphle\Auth\{Renderers\ApiLoginMediator, Repositories\ApiAuthRepo};

	use Suphle\Testing\TestTypes\IsolatedComponentTest;

	class BaseTestApiLoginMediator extends IsolatedComponentTest {

		use TestLoginMediator;

		const LOGIN_PATH = "/api/v1/login";

		protected function loginRendererName ():string {

			return ApiLoginMediator::class;
		}

		protected function loginRepoService ():string {

			return ApiAuthRepo::class;
		}
	}
?>
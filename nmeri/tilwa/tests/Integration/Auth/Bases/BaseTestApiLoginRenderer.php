<?php
	namespace Tilwa\Tests\Integration\Auth\Bases;

	use Tilwa\Auth\{Renderers\ApiLoginRenderer, Repositories\ApiAuthRepo};

	use Tilwa\Testing\TestTypes\IsolatedComponentTest;

	class BaseTestApiLoginRenderer extends IsolatedComponentTest {

		use TestLoginRenderer;

		const LOGIN_PATH = "/api/v1/login";

		protected function loginRendererName ():string {

			return ApiLoginRenderer::class;
		}

		protected function loginRepoService ():string {

			return ApiAuthRepo::class;
		}
	}
?>
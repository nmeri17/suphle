<?php
	namespace Tilwa\Config;

	use Tilwa\Contracts\Config\Auth as AuthContract;

	use Tilwa\Auth\Renderers\{BrowserLoginRenderer, ApiLoginRenderer};

	class Auth implements AuthContract {

		public function getLoginPaths ():array {

			return [
				$this->markupRedirect() => BrowserLoginRenderer::class,

				"api/v1/login" => ApiLoginRenderer::class
			];
		}

		public function getTokenSecretKey ():string {

			return getenv("APP_SECRET_KEY");
		}

		public function getTokenIssuer ():string {

			return getenv("SITE_HOST");
		}

		public function getTokenTtl ():int {

			return getenv("JWT_TTL");
		}

		public function getModelObservers():array {

			return [];
		}

		public function markupRedirect ():string {

			return "login";
		}
	}
?>
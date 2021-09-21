<?php

	namespace Tilwa\Config;

	use Tilwa\Contracts\Config\Auth as AuthContract;

	use Tilwa\Auth\{BrowserLoginRenderer, SessionStorage, ApiLoginRenderer};

	class Auth implements AuthContract {

		public function getLoginPaths ():array {

			return [
				"login" => BrowserLoginRenderer::class,

				"api/v1/login" => ApiLoginRenderer::class
			];
		}

		public function getPathRenderer (string $path):?string {

			$rendererList = $this->getLoginPaths();

			if (array_key_exists($path, $rendererList))

				return $rendererList[$path];
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

		public function isAdmin ($user):bool {

			return false;
		}

		public function getModelObservers():array {

			return [];
		}
	}
?>
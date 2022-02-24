<?php
	namespace Tilwa\Config;

	use Tilwa\Contracts\Config\Auth as AuthContract;

	use Tilwa\Auth\Renderers\{BrowserLoginRenderer, ApiLoginRenderer};

	use Tilwa\Routing\RequestDetails;

	class Auth implements AuthContract {

		private $requestDetails;

		public function __construct (RequestDetails $requestDetails) {

			$this->requestDetails = $requestDetails;
		}

		protected function getLoginPaths ():array {

			return [
				$this->markupRedirect() => BrowserLoginRenderer::class,

				"api/v1/login" => ApiLoginRenderer::class
			];
		}

		public function getLoginCollection ():?string {

			$rendererList = $this->getLoginPaths();

			$path = $this->requestDetails->getPath();

			if (array_key_exists($path, $rendererList))

				return $rendererList[$path];
		}

		public function isLoginRequest ():bool {

			return $this->requestDetails->isPostRequest() && !is_null($this->getLoginCollection());
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

		public function getModelObservers ():array {

			return [];
		}

		public function markupRedirect ():string {

			return "login";
		}
	}
?>
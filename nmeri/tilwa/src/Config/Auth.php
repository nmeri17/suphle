<?php
	namespace Tilwa\Config;

	use Tilwa\Contracts\{Config\AuthContract, IO\EnvAccessor};

	use Tilwa\Auth\Renderers\{BrowserLoginRenderer, ApiLoginRenderer};

	use Tilwa\Request\RequestDetails;

	class Auth implements AuthContract {

		private $requestDetails, $envAccessor;

		public function __construct (RequestDetails $requestDetails, EnvAccessor $envAccessor) {

			$this->requestDetails = $requestDetails;

			$this->envAccessor = $envAccessor;
		}

		protected function getLoginPaths ():array {

			return [
				$this->markupRedirect() => BrowserLoginRenderer::class,

				"api/v1/login" => ApiLoginRenderer::class
			];
		}

		public function getLoginCollection ():?string {

			$rendererList = $this->getLoginPaths();

			$requestDetails = $this->requestDetails;

			foreach ($rendererList as $key => $renderer)

				if ($requestDetails->matchesPath($key))

					return $rendererList[$key];

			return null;
		}

		public function isLoginRequest ():bool {

			return $this->requestDetails->isPostRequest() && !is_null($this->getLoginCollection());
		}

		public function getTokenSecretKey ():string {

			return $this->envAccessor->getField("APP_SECRET_KEY");
		}

		public function getTokenIssuer ():string {

			return $this->envAccessor->getField("SITE_HOST");
		}

		public function getTokenTtl ():int {

			return $this->envAccessor->getField("JWT_TTL");
		}

		public function getModelObservers ():array {

			return [];
		}

		public function markupRedirect ():string {

			return "login";
		}
	}
?>
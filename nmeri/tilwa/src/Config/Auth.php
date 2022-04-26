<?php
	namespace Tilwa\Config;

	use Tilwa\Contracts\Config\AuthContract;

	use Tilwa\Auth\Renderers\{BrowserLoginRenderer, ApiLoginRenderer};

	use Tilwa\Request\RequestDetails;

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

			$requestDetails = $this->requestDetails;

			foreach ($rendererList as $key => $renderer)

				if ($requestDetails->matchesPath($key))

					return $rendererList[$key];

			return null;
		}

		public function isLoginRequest ():bool {

			return $this->requestDetails->isPostRequest() && !is_null($this->getLoginCollection());
		}

		public function getModelObservers ():array {

			return [];
		}

		public function markupRedirect ():string {

			return "login";
		}
	}
?>
<?php
	namespace Suphle\Config;

	use Suphle\Contracts\Config\AuthContract;

	use Suphle\Auth\Renderers\{BrowserLoginMediator, ApiLoginMediator};

	use Suphle\Request\RequestDetails;

	class Auth implements AuthContract {

		private $requestDetails;

		public function __construct (RequestDetails $requestDetails) {

			$this->requestDetails = $requestDetails;
		}

		protected function getLoginPaths ():array {

			return [
				$this->markupRedirect() => BrowserLoginMediator::class,

				"api/v1/login" => ApiLoginMediator::class
			];
		}

		public function getLoginCollection ():?string {

			foreach ($this->getLoginPaths() as $key => $renderer) {

				if ($this->requestDetails->matchesPath($key))

					return $renderer;
			}

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
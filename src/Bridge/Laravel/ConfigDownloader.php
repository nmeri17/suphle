<?php
	namespace Suphle\Bridge\Laravel;

	use Suphle\Contracts\IO\EnvAccessor;

	use Suphle\IO\Http\BaseHttpRequest;

	use Suphle\Exception\DetectedExceptionManager;

	use Psr\Http\{Client\ClientInterface, Message\ResponseInterface};

	use GuzzleHttp\RequestOptions;

	class ConfigDownloader extends BaseHttpRequest {

		private $saveInLocation, $envAccessor;

		public function __construct (

			ClientInterface $requestClient, DetectedExceptionManager $exceptionDetector,

			EnvAccessor $envAccessor
		) {

			parent::__construct($requestClient, $exceptionDetector);

			$this->envAccessor = $envAccessor;
		}

		public function getRequestUrl ():string {

			$envUrl = $this->envAccessor->getField("LARAVEL_CONFIG_URL");

			return !empty($envUrl) ? $envUrl:

			"https://raw.githubusercontent.com/laravel/laravel/8.x/config/app.php";
		}

		public function setFilePath (string $path):self {

			$this->saveInLocation = $path;

			return $this;
		}

		protected function getHttpResponse ():ResponseInterface {

			return $this->requestClient->request(
			
				"get", $this->getRequestUrl(),

				[RequestOptions::SINK => $this->saveInLocation]
			);
		}

		protected function convertToDomainObject (ResponseInterface $response) {

			return $response;
		}
	}
?>
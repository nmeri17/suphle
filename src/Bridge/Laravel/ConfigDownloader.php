<?php
	namespace Suphle\Bridge\Laravel;

	use Suphle\Contracts\IO\EnvAccessor;

	use Suphle\IO\Http\BaseHttpRequest;

	use Suphle\Exception\DetectedExceptionManager;

	use Psr\Http\{Client\ClientInterface, Message\ResponseInterface};

	use GuzzleHttp\RequestOptions;

	class ConfigDownloader extends BaseHttpRequest {

		final public const ENV_CONFIG_URL = "LARAVEL_CONFIG_URL";

		private $saveInLocation;

		public function __construct (

			ClientInterface $requestClient, DetectedExceptionManager $exceptionDetector,

			private readonly EnvAccessor $envAccessor
		) {

			parent::__construct($requestClient, $exceptionDetector);
		}

		public function getRequestUrl ():string {

			return $this->envAccessor->getField(

				self::ENV_CONFIG_URL,

				"https://raw.githubusercontent.com/laravel/laravel/8.x/config/app.php"
			);
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
<?php
	namespace Tilwa\Bridge\Laravel;

	use Tilwa\Hydration\BaseInterfaceLoader;

	use Tilwa\Contracts\Config\{ModuleFiles, Laravel as LaravelConfig};

	use Tilwa\Contracts\Bridge\LaravelContainer;

	use Tilwa\Routing\RequestDetails;

	use Tilwa\Request\PayloadStorage;

	use Illuminate\Http\Request;

	class LaravelAppLoader extends BaseInterfaceLoader {

		private $requestDetails, $fileConfig, $payloadStorage,

		$laravelConfig;

		public function __construct (RequestDetails $requestDetails, ModuleFiles $fileConfig, PayloadStorage $payloadStorage, LaravelConfig $laravelConfig) {

			$this->requestDetails = $requestDetails;

			$this->fileConfig = $fileConfig;

			$this->payloadStorage = $payloadStorage;

			$this->laravelConfig = $laravelConfig;
		}

		public function bindArguments():array {

			return [

				"basePath" => $this->fileConfig->activeModulePath() . DIRECTORY_SEPARATOR . $this->laravelConfig->frameworkDirectory();
			];
		}

		public function afterBind(LaravelContainer $initialized):void {

			$replaceConfig = new ConfigLoader;

			$initialized->instance("config", $replaceConfig);

			(new ConfigFileFinder)

			->loadConfigurationFiles($initialized, $replaceConfig);

			$this->provideRequest($initialized);
		}

		public function concrete():string {

			return LaravelAppConcrete::class;
		}

		private function provideRequest (LaravelContainer $initialized):void {

			$initialized->bind("request", function ($app) {
				
				return Request::create(
					$this->requestDetails->getPath(),

					$this->requestDetails->httpMethod(),

					$this->payloadStorage->fullPayload(),

					$_COOKIE, $_FILES, $_SERVER
				);
			});
		}
	}
?>
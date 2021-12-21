<?php
	namespace Tilwa\InterfaceLoaders;

	use Tilwa\Hydration\BaseInterfaceLoader;

	use Tilwa\Contracts\{Config\ModuleFiles, LaravelApp};

	use Tilwa\Bridge\Laravel\{LaravelAppConcrete, ConfigLoader};

	use Tilwa\Routing\RequestDetails;

	use Tilwa\Request\PayloadStorage;

	use Illuminate\Foundation\Bootstrap\LoadConfiguration;

	use Illuminate\Http\Request;

	class LaravelAppLoader extends BaseInterfaceLoader {

		private $requestDetails, $fileConfig, $payloadStorage;

		public function __construct (RequestDetails $requestDetails, ModuleFiles $fileConfig, PayloadStorage $payloadStorage) {

			$this->requestDetails = $requestDetails;

			$this->fileConfig = $fileConfig;

			$this->payloadStorage = $payloadStorage;
		}

		public function bindArguments():array {

			return [

				"basePath" => $this->fileConfig->activeModulePath()
			];
		}

		public function afterBind(LaravelApp $initialized):void {

			// ordering between this 2 matters a lot
			$initialized->bind("config", function ($app) {
				
				return new ConfigLoader;
			});

			(new LoadConfiguration)->bootstrap($initialized);

			$this->provideRequest($initialized);
		}

		public function concrete():string {

			return LaravelAppConcrete::class;
		}

		private function provideRequest (LaravelApp $initialized):void {

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
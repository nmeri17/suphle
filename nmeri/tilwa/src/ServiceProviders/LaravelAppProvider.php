<?php

	namespace Tilwa\ServiceProviders;

	use Tilwa\App\ServiceProvider;

	use Tilwa\Contracts\{Config\ModuleFiles, LaravelApp};

	use Tilwa\Bridge\Laravel\{LaravelAppConcrete, ConfigLoader};

	use Tilwa\Routing\RequestDetails;

	use Illuminate\Foundation\Bootstrap\LoadConfiguration;

	use Illuminate\Http\Request;

	class LaravelAppProvider extends ServiceProvider {

		private $requestDetails, $fileConfig;

		public function __construct (RequestDetails $requestDetails, ModuleFiles $fileConfig) {

			$this->requestDetails = $requestDetails;

			$this->fileConfig = $fileConfig;
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

					$this->requestDetails->getPayload(),

					$_COOKIE, $_FILES, $_SERVER
				);
			});
		}
	}
?>
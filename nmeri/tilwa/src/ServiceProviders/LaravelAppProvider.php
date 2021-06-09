<?php

	namespace Tilwa\ServiceProviders;

	use Tilwa\App\ServiceProvider;

	use Tilwa\Contracts\{Config\ModuleFiles, LaravelApp};

	use Tilwa\Bridge\Laravel\{LaravelAppConcrete, ConfigLoader};

	use Tilwa\Routing\RequestDetails;

	use Illuminate\Foundation\Bootstrap\LoadConfiguration;

	use Illuminate\Http\Request;

	class LaravelAppProvider extends ServiceProvider {

		private $requestDetails;

		public function bindArguments(ModuleFiles $fileConfig, RequestDetails $requestDetails):array {

			$this->requestDetails = $requestDetails;

			return [

				"basePath" => $fileConfig->activeModulePath()
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

					$this->requestDetails->getMethod(),

					$this->requestDetails->getPayload(),

					$_COOKIE, $_FILES, $_SERVER
				);
			});
		}
	}
?>
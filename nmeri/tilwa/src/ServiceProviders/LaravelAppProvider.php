<?php

	namespace Tilwa\ServiceProviders;

	use Tilwa\App\ServiceProvider;

	use Tilwa\Contracts\{Config\ModuleFiles, LaravelApp};

	use Tilwa\Bridge\Laravel\{LaravelAppConcrete, ConfigLoader};

	use Illuminate\Contracts\Config\Repository;

	use Illuminate\Foundation\Bootstrap\LoadConfiguration;

	class LaravelAppProvider extends ServiceProvider {

		public function bindArguments(ModuleFiles $fileConfig):array {

			return [

				"basePath" => $fileConfig->activeModulePath()
			];
		}

		public function afterBind(LaravelApp $initialized):void {

			// ordering here matters a lot
			$initialized->bind(Repository::class, function ($app) {
				
				return new ConfigLoader;
			});

			(new LoadConfiguration)->bootstrap($initialized);
		}

		public function concrete():string {

			return LaravelAppConcrete::class;
		}
	}
?>
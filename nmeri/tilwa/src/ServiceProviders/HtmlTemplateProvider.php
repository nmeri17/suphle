<?php

	namespace Tilwa\ServiceProviders;

	use Tilwa\App\ServiceProvider;

	use Tilwa\Routing\RouteManager;

	use Tilwa\Contracts\{ModuleFiles, HtmlTemplate};

	class HtmlTemplateProvider extends ServiceProvider {

		public function bindArguments(ModuleFiles $fileConfig, HtmlTemplate $htmlConfig) {

			$htmlConfig->addViewPath($fileConfig->activeModulePath(). DIRECTORY_SEPARATOR) . 'views';

			return [

				"config" => $htmlConfig
			];
		}

		public function concrete():string {

			return ""; // replace with transphporm adapter
		}
	}
?>
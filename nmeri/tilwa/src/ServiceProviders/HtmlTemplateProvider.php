<?php

	namespace Tilwa\ServiceProviders;

	use Tilwa\App\{Bootstrap, ServiceProvider};

	use Tilwa\Http\Response\Templating\TemplateEngine;

	use Tilwa\Routing\RouteManager;

	class HtmlTemplateProvider extends ServiceProvider {

		public function bindArguments(ModuleFiles $config, RouteManager $router) {

			return [

				"folder" => $config->activeModulePath() . 'views'. DIRECTORY_SEPARATOR // this should push into our HTML view folders array, not set it this way
			];
		}

		public function concrete():string {

			return TemplateEngine::class; // replace with transphporm adapter
		}
	}
?>
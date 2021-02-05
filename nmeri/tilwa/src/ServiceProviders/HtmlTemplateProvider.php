<?php

	namespace Tilwa\ServiceProviders;

	use Tilwa\App\{Bootstrap, ServiceProvider};

	use Tilwa\Http\Response\Templating\TemplateEngine;

	use Tilwa\Routing\RouteManager;

	class HtmlTemplateProvider extends ServiceProvider {

		public function bindArguments(Bootstrap $module, RouteManager $router) {

			return [

				"folder" => $module->getViewPath()
			];
		}

		public function concrete():string {

			return TemplateEngine::class;
		}
	}
?>
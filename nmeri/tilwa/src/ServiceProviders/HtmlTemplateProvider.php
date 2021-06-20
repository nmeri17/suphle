<?php

	namespace Tilwa\ServiceProviders;

	use Tilwa\App\ServiceProvider;

	use Tilwa\Contracts\{ModuleFiles, HtmlTemplate};

	class HtmlTemplateProvider extends ServiceProvider {

		public function concrete():string {

			return ""; // replace with transphporm adapter. work with [HtmlTemplate]
		}
	}
?>
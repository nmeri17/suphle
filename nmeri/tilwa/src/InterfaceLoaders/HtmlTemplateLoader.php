<?php

	namespace Tilwa\ServiceProviders;

	use Tilwa\App\ServiceProvider;

	use Tilwa\Adapters\Markups\Transphporm;

	class HtmlTemplateProvider extends ServiceProvider {

		public function concrete():string {

			return Transphporm::class;
		}
	}
?>
<?php
	namespace Tilwa\InterfaceLoaders;

	use Tilwa\App\BaseInterfaceLoader;

	use Tilwa\Adapters\Markups\Transphporm;

	class HtmlTemplateLoader extends BaseInterfaceLoader {

		public function concrete():string {

			return Transphporm::class;
		}
	}
?>
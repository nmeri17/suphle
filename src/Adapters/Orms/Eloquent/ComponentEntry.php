<?php
	namespace Suphle\Adapters\Orms\Eloquent;

	use Suphle\ComponentTemplates\BaseComponentEntry;

	class ComponentEntry extends BaseComponentEntry {

		public function uniqueName ():string {

			return "SuphleEloquentTemplates";
		}

		protected function templatesLocation ():string {

			return __DIR__ . DIRECTORY_SEPARATOR . "ComponentTemplates";
		}
	}
?>
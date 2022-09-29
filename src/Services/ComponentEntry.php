<?php
	namespace Suphle\Services;

	use Suphle\ComponentTemplates\BaseComponentEntry;

	class ComponentEntry extends BaseComponentEntry {

		public function uniqueName ():string {

			return "SuphleServicesTemplates";
		}

		protected function templatesLocation ():string {

			return __DIR__ . DIRECTORY_SEPARATOR . "ComponentTemplates";
		}
	}
?>
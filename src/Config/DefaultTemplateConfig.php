<?php
	namespace Suphle\Config;

	use Suphle\Contracts\Config\ComponentTemplates;

	use Suphle\Exception\ComponentEntry as ExceptionComponentEntry;

	use Suphle\Bridge\Laravel\ComponentEntry as LaravelComponentEntry;

	class DefaultTemplateConfig implements ComponentTemplates {

		public function getTemplateEntries ():array {

			return [
				ExceptionComponentEntry::class,

				LaravelComponentEntry::class
			];
		}
	}
?>
<?php
	namespace Suphle\Config;

	use Suphle\Contracts\Config\ComponentTemplates;

	use Suphle\Exception\Component\ComponentEntry as ExceptionComponentEntry;

	class DefaultTemplateConfig implements ComponentTemplates {

		public function getTemplateEntries ():array {

			return [ExceptionComponentEntry::class];
		}
	}
?>
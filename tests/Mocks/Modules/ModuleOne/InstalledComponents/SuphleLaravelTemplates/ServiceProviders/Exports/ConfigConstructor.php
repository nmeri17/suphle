<?php
	namespace Suphle\Tests\Mocks\Modules\ModuleOne\InstalledComponents\SuphleLaravelTemplates\ServiceProviders\Exports;

	class ConfigConstructor {

		private $firstConfig;

		public function __construct (array $firstConfig) {

			$this->firstConfig = $firstConfig;
		}

		public function getSecondLevel ():array {

			return $this->firstConfig["second_level"];
		}
	}
?>
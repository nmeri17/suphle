<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleOne\Bridge\Laravel\ServiceProviders\Exports;

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
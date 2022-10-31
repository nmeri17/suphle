<?php
	namespace Suphle\Tests\Mocks\Modules\ModuleOne\InstalledComponents\SuphleLaravelTemplates\ServiceProviders\Exports;

	class ConfigConstructor {

		public function __construct(private readonly array $firstConfig)
  {
  }

		public function getSecondLevel ():array {

			return $this->firstConfig["second_level"];
		}
	}
?>
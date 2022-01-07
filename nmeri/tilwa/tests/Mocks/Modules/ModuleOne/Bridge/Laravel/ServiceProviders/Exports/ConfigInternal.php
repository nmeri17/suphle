<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleOne\Bridge\Laravel\ServiceProviders\Exports;

	class ConfigInternal {

		public function getSecondLevel ():array {

			return config("nested.first_level.second_level");
		}

		public function magicValue ():string {

			return "boo!";
		}

	    public function __call (string $method, array $arguments) {

	    	if ($method == "internalMagic")

				return $this->magicValue();
	    }
	}
?>
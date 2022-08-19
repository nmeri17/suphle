<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleOne\InterfaceLoader;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\Concretes\CConcrete;

	use Tilwa\Hydration\BaseInterfaceLoader;

	class CProvider extends BaseInterfaceLoader {

		public function bindArguments ():array {

			return ["value" => 10];
		}

		public function concreteName ():string {

			return CConcrete::class;
		}
	}
?>
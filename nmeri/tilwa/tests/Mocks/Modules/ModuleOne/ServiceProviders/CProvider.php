<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleOne\InterfaceLoader;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\Concretes\CConcrete;

	use Tilwa\App\BaseInterfaceLoader;

	class CProvider extends BaseInterfaceLoader {

		public function bindArguments ():array {

			return ["value" => 10];
		}

		public function concrete():string {

			return CConcrete::class;
		}
	}
?>
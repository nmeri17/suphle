<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleOne\ServiceProviders;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\Concretes\CConcrete;

	use Tilwa\App\ServiceProvider;

	class CProvider extends ServiceProvider {

		public function bindArguments ():array {

			return ["value" => 10];
		}

		public function concrete():string {

			return CConcrete::class;
		}
	}
?>
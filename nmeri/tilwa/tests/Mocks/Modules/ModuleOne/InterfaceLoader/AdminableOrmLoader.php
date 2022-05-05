<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleOne\InterfaceLoader;

	use Tilwa\Adapters\Orms\Eloquent\OrmLoader;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\Adapters\AdminableOrmBridge;

	class AdminableOrmLoader extends OrmLoader { // using a real class for this to avoid all the headache associated with stubbing out laravelConcrete, connection etc

		public function concrete():string {

			return AdminableOrmBridge::class;
		}
	}
?>
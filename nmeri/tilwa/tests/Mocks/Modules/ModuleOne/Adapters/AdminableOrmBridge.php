<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleOne\Adapters;

	use Tilwa\Adapters\Orms\Eloquent\OrmBridge;

	use Tilwa\Contracts\Auth\UserContract;

	use Tilwa\Tests\Mocks\Models\Eloquent\AdminableUser;

	class AdminableOrmBridge extends OrmBridge {

		public function userModel ():UserContract {
var_dump("expression");
			return $this->container->getClass(AdminableUser::class);
		}
	}
?>
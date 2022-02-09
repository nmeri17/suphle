<?php
	namespace Tilwa\Tests\Mocks\Models\Eloquent;

	use Tilwa\Adapters\Orms\Eloquent\Models\User;

	class AdminableUser extends User {

		public function isAdmin ():bool {

			return false;
		}
	}
?>
<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleOne\Concretes;

	use Exception;

	class ThrowsException {

		public function awesomeMethod () {

			throw new Exception;
		}
	}
?>
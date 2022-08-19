<?php
	namespace Suphle\Tests\Mocks\Modules\ModuleOne\Concretes;

	use Exception;

	class ThrowsException {

		public function awesomeMethod () {

			throw new Exception;
		}
	}
?>
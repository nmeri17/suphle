<?php
	namespace Suphle\Tests\Mocks\Modules\ModuleOne\Authentication;

	use Suphle\Auth\Repositories\BrowserAuthRepo;

	use Suphle\Services\Decorators\ValidationRules;

	class CustomBrowserRepo extends BrowserAuthRepo {

		#[ValidationRules([
			"email" => "required|email",

			"password" => "required|numeric|min:9"
		])]
		public function successLogin ():iterable {

			return [$this->startSessionForCompared()];
		}
	}
?>
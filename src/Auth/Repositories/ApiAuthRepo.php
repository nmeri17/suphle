<?php
	namespace Suphle\Auth\Repositories;

	use Suphle\Auth\Storage\TokenStorage;

	use Suphle\Contracts\Auth\ColumnPayloadComparer;

	use Suphle\Services\Decorators\ValidationRules;

	class ApiAuthRepo extends BaseAuthRepo {

		public function __construct (

			protected readonly ColumnPayloadComparer $comparer,

			protected readonly TokenStorage $authStorage
		) { 
			
			//
		}

		#[ValidationRules([
			"email" => "required|email",

			"password" => "required|alpha_num|min:5"
		])]
		public function successLogin ():iterable {

			return [

				"token" => $this->startSessionForCompared()
			];
		}
	}
?>
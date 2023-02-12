<?php
	namespace Suphle\Tests\Mocks\Modules\ModuleOne\Coordinators;

	use Suphle\Services\{ServiceCoordinator, Decorators\ValidationRules};

	class ValidatorCoordinator extends ServiceCoordinator {

		public function handleGet () {

			return ["message" => "mercy"];
		}

		public function postNoValidator () {

			//
		}

		#[ValidationRules(["foo" => "required"])]
		public function postWithValidator () {

			return [];
		}
	}
?>
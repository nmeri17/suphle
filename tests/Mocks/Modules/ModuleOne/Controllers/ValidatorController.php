<?php
	namespace Suphle\Tests\Mocks\Modules\ModuleOne\Controllers;

	use Suphle\Services\ServiceCoordinator;

	use Suphle\Tests\Mocks\Modules\ModuleOne\Validators\ValidatorOne;

	class ValidatorController extends ServiceCoordinator {

		public function validatorCollection ():?string {

			return ValidatorOne::class;
		}

		public function handleGet () {

			//
		}

		public function postNoValidator () {

			//
		}

		public function postWithValidator () {

			//
		}
	}
?>
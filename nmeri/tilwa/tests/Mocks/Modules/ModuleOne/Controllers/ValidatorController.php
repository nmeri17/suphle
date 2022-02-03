<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleOne\Controllers;

	use Tilwa\Services\ServiceCoordinator;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\Validators\ValidatorOne;

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
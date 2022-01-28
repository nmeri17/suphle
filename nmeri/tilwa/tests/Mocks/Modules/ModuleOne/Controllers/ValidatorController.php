<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleOne\Controllers;

	use Tilwa\Services\ServiceCoordinator;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\Validators\ValidatorOne;

	class ValidatorController extends ServiceCoordinator {

		public function validatorCollection ():?string {

			return ValidatorOne::class;
		}

		// assume we wanna get Products matching a variadic list of criteria (means a simple [setIdentifier] won't be enough?). So that guy will have to read them from the validator
		public function transferIncomingGet (ModelOne $model) {

			//
		}

		public function transferIncomingPost () {

			//
		}
	}
?>
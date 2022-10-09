<?php
	namespace Suphle\Tests\Mocks\Modules\ModuleOne\Routes\Auth;

	use Suphle\Routing\BaseCollection;

	use Suphle\Request\PathAuthorizer;

	use Suphle\Response\Format\Json;

	use Suphle\Tests\Mocks\Modules\ModuleOne\Coordinators\EmploymentEditCoordinator;

	use Suphle\Tests\Mocks\Modules\ModuleOne\Authorization\Paths\{EmploymentEditRule, AdminRule};

	class UnlocksAuthorization1 extends BaseCollection {

		public function _handlingClass ():string {

			return EmploymentEditCoordinator::class;
		}

		public function RETAIN () {

			$this->_get(new Json("simpleResult"));
		}

		public function ADDITIONAL__RULEh () {

			$this->_get(new Json("simpleResult"));
		}

		public function SECEDE () {

			$this->_get(new Json("simpleResult"));
		}

		public function GMULTI__EDITh_id () {

			$this->_get(new Json("getEmploymentDetails"));
		}

		public function GMULTI__EDIT__UNAUTHh () {

			$this->_get(new Json("getEmploymentDetails"));
		}

		public function PMULTI__EDITh_id () {

			$this->_put(new Json("updateEmploymentDetails"));
		}

		public function _authorizePaths (PathAuthorizer $pathAuthorizer):void {

			$pathAuthorizer->addRule (

				[ "GMULTI__EDITh_id"], EmploymentEditRule::class
			)
			->forgetRule([

				"SECEDE", "GMULTI__EDIT__UNAUTHh"
			], AdminRule::class);
		}
	}
?>
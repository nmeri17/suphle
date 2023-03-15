<?php
	namespace Suphle\Tests\Mocks\Modules\ModuleOne\Routes\Auth;

	use Suphle\Routing\{BaseCollection, PreMiddlewareRegistry, CollectionMetaFunnel, Decorators\HandlingCoordinator};

	use Suphle\Auth\RequestScrutinizers\AuthorizeMetaFunnel;

	use Suphle\Response\Format\Json;

	use Suphle\Tests\Mocks\Modules\ModuleOne\Coordinators\EmploymentEditCoordinator;

	use Suphle\Tests\Mocks\Modules\ModuleOne\Authorization\Paths\{EmploymentEditRule, AdminRule};

	#[HandlingCoordinator(EmploymentEditCoordinator::class)]
	class UnlocksAuthorization1 extends BaseCollection {

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

		public function _preMiddleware (PreMiddlewareRegistry $registry):void {

			$registry->tagPatterns(

				new AuthorizeMetaFunnel(["GMULTI__EDITh_id"], EmploymentEditRule::class)
			)
			->removeTag([

				"SECEDE", "GMULTI__EDIT__UNAUTHh"
			], AuthorizeMetaFunnel::class, function (AuthorizeMetaFunnel $collector) {

				return $collector->ruleClass == AdminRule::class;
			});
		}
	}
?>
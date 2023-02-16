<?php
	namespace Suphle\Tests\Mocks\Modules\ModuleOne\Routes;

	use Suphle\Routing\BaseCollection;

	use Suphle\Response\Format\{Json, Markup, Redirect};

	use Suphle\Routing\Decorators\HandlingCoordinator;

	use Suphle\Tests\Mocks\Modules\ModuleOne\Coordinators\ValidatorCoordinator;

	#[HandlingCoordinator(ValidatorCoordinator::class)]
	class ValidatorCollection extends BaseCollection {

		public function POST__WITH__JSONh () {

			$this->_post(new Json("postWithValidator"));
		}

		public function POST__WITH__HTMLh () {

			$this->_post(new Redirect("postWithValidator", fn () => "/"));
		}

		public function POST__WITHOUTh () {

			$this->_post(new Json("postNoValidator"));
		}

		public function GET__WITHOUTh () {

			$this->_get(new Markup("handleGet", "secure-some.edit-form"));
		}
	}
?>
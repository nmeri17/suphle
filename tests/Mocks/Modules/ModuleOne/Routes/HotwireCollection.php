<?php
	namespace Suphle\Tests\Mocks\Modules\ModuleOne\Routes;

	use Suphle\Routing\BaseCollection;

	use Suphle\Response\Format\Redirect;

	use Suphle\Adapters\Presentation\Hotwire\Formats\{RedirectHotwireStream, ReloadHotwireStream};

	use Suphle\Tests\Mocks\Modules\ModuleOne\Coordinators\HotwireCoordinator;

	class HotwireCollection extends BaseCollection {

		public function _handlingClass ():string {

			return HotwireCoordinator::class;
		}

		public function INIT__POSTh () {

			$this->_get(new Markup("loadForm", "secure-some/edit-form"));
		}

		public function REGULAR__MARKUPh () {

			$this->_post(new Redirect("regularFormResponse", fn () => "/"));
		}

		public function HOTWIRE__REDIRECTh () {

			$renderer = (new RedirectHotwireStream("hotwireFormResponse", fn () => "/"))

			->addReplace(
				"hotwireReplace", $this->employmentId(...),

				"hotwire/replace-fragment"
			)
			->addBefore(
				"hotwireBefore", $this->employmentId(...),

				"hotwire/before-fragment"
			);

			$this->_post($renderer);
		}

		public function employmentId ():int {

			return $this->rawResponse["data"]->id;
		}

		public function HOTWIRE__RELOADh () {

			$renderer = (new ReloadHotwireStream("hotwireFormResponse"))

			->addAfter(
				"hotwireAfter", $this->employmentId(...),

				"hotwire/after-fragment"
			)
			->addUpdate(
				"hotwireUpdate", $this->employmentId(...),

				"hotwire/update-fragment"
			);

			$this->_post($renderer);
		}
	}
?>
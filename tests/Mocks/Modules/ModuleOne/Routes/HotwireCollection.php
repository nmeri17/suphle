<?php
	namespace Suphle\Tests\Mocks\Modules\ModuleOne\Routes;

	use Suphle\Routing\BaseCollection;

	use Suphle\Response\Format\Redirect;

	use Suphle\Adapters\Presentation\Hotwire\Formats\{RedirectHotwireStream, ReloadHotwireStream};

	use Suphle\Adapters\Orms\Eloquent\Models\ModelDetail;

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

		public function employmentId ():string {

			return (new ModelDetail)

			->normalizeIdentifier($this->rawResponse["data"]);
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

			$this->_put($renderer);
		}

		public function NO__REPLACE__NODEh () {

			$renderer = (new RedirectHotwireStream("hotwireFormResponse", fn () => "/"))

			->addAppend(
				"hotwireReplace", $this->employmentId(...),

				"hotwire/append-fragment"
			)
			->addBefore(
				"hotwireBefore", $this->employmentId(...),

				"hotwire/before-fragment"
			);

			$this->_post($renderer);
		}

		public function DELETE__SINGLEh () {

			$renderer = (new RedirectHotwireStream("hotwireFormResponse", fn () => "/"))

			->addRemove(
				"hotwireReplace", $this->employmentId(...)
			);

			$this->_delete($renderer);
		}

		public function COMBINE__DELETEh () {

			$renderer = (new RedirectHotwireStream("hotwireFormResponse", fn () => "/"))

			->addRemove(
				"hotwireReplace", $this->employmentId(...)
			)
			->addAfter(
				"hotwireAfter", $this->employmentId(...),

				"hotwire/after-fragment"
			);

			$this->_delete($renderer);
		}
	}
?>
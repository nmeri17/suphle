<?php
	namespace Suphle\Tests\Mocks\Modules\ModuleOne\Routes;

	use Suphle\Routing\BaseCollection;

	use Suphle\Response\Format\{Redirect, Markup};

	use Suphle\Exception\Diffusers\ValidationFailureDiffuser;

	use Suphle\Adapters\Presentation\Hotwire\Formats\{RedirectHotwireStream, ReloadHotwireStream};

	use Suphle\Adapters\Orms\Eloquent\Models\ModelDetail;

	use Suphle\Tests\Mocks\Modules\ModuleOne\Coordinators\HotwireCoordinator;

	use Suphle\Tests\Mocks\Models\Eloquent\Employment;

	class HotwireCollection extends BaseCollection {

		public function _handlingClass ():string {

			return HotwireCoordinator::class;
		}

		public function INIT__POSTh () {

			$this->_get(new Markup("loadForm", "secure-some.edit-form"));
		}

		public function REGULAR__MARKUPh () {

			$this->_post(new Redirect("regularFormResponse", fn () => "/"));
		}

		public function HOTWIRE__REDIRECTh () {

			$renderer = (new RedirectHotwireStream("hotwireFormResponse", fn () => "/"))

			->addReplace(
				"hotwireReplace", $this->getStreamActionTarget(),

				"hotwire/replace-fragment"
			)
			->addBefore(
				"hotwireBefore", $this->getStreamActionTarget(),

				"hotwire/before-fragment"
			);

			$this->_post($renderer);
		}

		/**
		 * On success, creates a turbo stream for given element. On failure, should attempt to replace/update the form from which request originated
		*/
		public function getStreamActionTarget (string $formTarget = "update-form"):callable {

			return function () use ($formTarget) {

				$responseBody = $this->rawResponse;

				if (!array_key_exists(ValidationFailureDiffuser::ERRORS_PRESENCE, $responseBody))

					return (new ModelDetail)

					->idFromModel($responseBody["data"]);

				return $formTarget;
			};
		}

		public function HOTWIRE__RELOADh () {

			$renderer = (new ReloadHotwireStream("hotwireFormResponse"))

			->addAfter(
				"hotwireAfter", $this->getStreamActionTarget(),

				"hotwire/after-fragment"
			)
			->addUpdate(
				"hotwireUpdate", $this->getStreamActionTarget(),

				"hotwire/update-fragment"
			);

			$this->_put($renderer);
		}

		public function NO__REPLACE__NODEh () {

			$renderer = (new RedirectHotwireStream("hotwireFormResponse", fn () => "/"))

			->addAppend(
				"hotwireReplace", $this->getStreamActionTarget(),

				"hotwire/append-fragment"
			)
			->addBefore(
				"hotwireBefore", $this->getStreamActionTarget(),

				"hotwire/before-fragment"
			);

			$this->_post($renderer);
		}

		public function DELETE__SINGLEh () {

			$renderer = (new RedirectHotwireStream("hotwireFormResponse", fn () => "/"))

			->addRemove(
				"hotwireReplace", $this->getStreamActionTarget()
			);

			$this->_delete($renderer);
		}

		public function COMBINE__DELETEh () {

			$renderer = (new RedirectHotwireStream("hotwireFormResponse", fn () => "/"))

			->addRemove(
				"hotwireReplace", $this->getStreamActionTarget()
			)
			->addAfter(
				"hotwireAfter", $this->getStreamActionTarget(),

				"hotwire/after-fragment"
			);

			$this->_delete($renderer);
		}
	}
?>
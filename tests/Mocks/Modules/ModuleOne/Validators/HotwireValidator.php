<?php
	namespace Suphle\Tests\Mocks\Modules\ModuleOne\Validators;

	class HotwireValidator {

		public function regularFormResponse ():array {

			return [

				"id" => "required|numeric|exists:employment",

				"id2" => "required|numeric|exists:employment,id"
			];
		}

		public function hotwireFormResponse ():array {

			return $this->regularFormResponse();
		}

		public function hotwireReplace ():array {

			return $this->regularFormResponse();
		}

		public function hotwireBefore ():array {

			return [];
		}

		public function hotwireAfter ():array {

			return [];
		}

		public function hotwireUpdate ():array {

			return $this->regularFormResponse();
		}
	}
?>
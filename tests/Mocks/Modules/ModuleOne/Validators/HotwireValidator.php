<?php
	namespace Suphle\Tests\Mocks\Modules\ModuleOne\Validators;

	class HotwireValidator {

		public function regularFormResponse ():array {

			return [

				"id" => "required|numeric|exists:employment",

				"id2" => "required|numeric|exists:employment"
			];
		}

		public function hotwireFormResponse ():array {

			return $this->regularFormResponse();
		}

		public function hotwireReplace ():array {

			return ["id" => "required|numeric|exists:employment"];
		}

		public function hotwireBefore ():array {

			return ["id2" => "required|numeric|exists:employment"];
		}

		public function hotwireAfter ():array {

			return ["id" => "required|numeric|exists:employment"];
		}

		public function hotwireUpdate ():array {

			return ["id2" => "required|numeric|exists:employment"];
		}
	}
?>
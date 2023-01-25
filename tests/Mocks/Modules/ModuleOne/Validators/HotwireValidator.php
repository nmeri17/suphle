<?php
	namespace Suphle\Tests\Mocks\Modules\ModuleOne\Validators;

	class HotwireValidator {

		public function regularFormResponse ():array {

			return [

				"id" => "required|numeric|exists",

				"id2" => "required|numeric|exists"
			];
		}

		public function hotwireFormResponse ():array {

			return $this->regularFormResponse();
		}

		public function hotwireReplace ():array {

			return ["id" => "required|numeric|exists"];
		}

		public function hotwireBefore ():array {

			return ["id2" => "required|numeric|exists"];
		}

		public function hotwireAfter ():array {

			return ["id" => "required|numeric|exists"];
		}

		public function hotwireUpdate ():array {

			return ["id2" => "required|numeric|exists"];
		}
	}
?>
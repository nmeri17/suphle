<?php
	namespace Suphle\Tests\Mocks\Modules\ModuleOne\Validators;

	class ImageValidator {

		private function genericRules ():array {

			return [
				"belonging_resource" => "required|string",

				"profile_pic" => "required|image"
			];
		}

		public function applyNoOptimization ():array {

			return $this->genericRules();
		}

		public function applyAllOptimizations ():array {

			return $this->genericRules();
		}

		public function applyThumbnail ():array {

			return $this->genericRules();
		}
	}
?>
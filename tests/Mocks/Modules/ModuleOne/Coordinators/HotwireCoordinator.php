<?php
	namespace Suphle\Tests\Mocks\Modules\ModuleOne\Coordinators;

	use Suphle\Tests\Mocks\Modules\ModuleOne\{Validators\HotwireValidator};

	use Suphle\Tests\Mocks\Modules\ModuleOne\PayloadReaders\{BaseEmploymentBuilder, EmploymentId2Builder};

	class HotwireCoordinator extends ServiceCoordinator {

		public function validatorCollection ():string {

			return HotwireValidator::class;
		}

		public function loadForm ():array {

			return [];
		}

		public function regularFormResponse ():array {

			return []; // not really necessary to return anything since they just redirect
		}

		public function hotwireFormResponse (BaseEmploymentBuilder $employmentBuilder):array {

			return [];
		}

		/**
		 * Just return the posted data
		*/
		public function hotwireReplace (BaseEmploymentBuilder $employmentBuilder):array {

			return ["data" => $employmentBuilder->get()];
		}

		public function hotwireBefore (EmploymentId2Builder $employmentBuilder):array {

			return ["data" => $employmentBuilder->get()];
		}

		public function hotwireAfter (BaseEmploymentBuilder $employmentBuilder):array {

			return ["data" => $employmentBuilder->get()];
		}

		public function hotwireUpdate (EmploymentId2Builder $employmentBuilder):array {

			return ["data" => $employmentBuilder->get()];
		}
	}
?>
<?php
	namespace Suphle\Tests\Mocks\Modules\ModuleOne\Coordinators;

	use Suphle\Services\ServiceCoordinator;

	use Suphle\Tests\Mocks\Modules\ModuleOne\{Validators\HotwireValidator, Concretes\Services\SystemModelEditMock1};

	use Suphle\Tests\Mocks\Modules\ModuleOne\PayloadReaders\{BaseEmploymentBuilder, EmploymentId2Builder};

	class HotwireCoordinator extends ServiceCoordinator {

		public function validatorCollection ():string {

			return HotwireValidator::class;
		}

		public function __construct (protected SystemModelEditMock1 $editService) {

			//
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

			return ["data" => $employmentBuilder->getBuilder()->first()];
		}

		public function hotwireBefore (EmploymentId2Builder $employmentBuilder):array {

			return ["data" => $employmentBuilder->getBuilder()->first()];
		}

		public function hotwireAfter (BaseEmploymentBuilder $employmentBuilder):array {

			return ["data" => $employmentBuilder->getBuilder()->first()];
		}

		public function hotwireUpdate (EmploymentId2Builder $employmentBuilder):array {

			return ["data" => $employmentBuilder->getBuilder()->first()];
		}
	}
?>
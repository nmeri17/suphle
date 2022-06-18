<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleOne\Concretes\Services;

	use Tilwa\Services\ConditionalFactory;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\Interfaces\GreaterFields;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\Concretes\Services\ConditionalHandlers\{FieldBGreater, FieldAGreater, BothFieldsEqual};

	class ConditionalFactoryMock extends ConditionalFactory {

		protected function manufacturerMethod ():string {

			return "greatestFields";
		}

		protected function greatestFields (int $fieldA, int $fieldB, int $fieldC):void {

			$this->whenCase([$this, "caseACondition"], FieldAGreater::class, $fieldA, $fieldB)

			->whenCase([$this, "caseBCondition"], FieldBGreater::class, $fieldB, $fieldA)

			->finally( BothFieldsEqual::class, $fieldC);
		}

		protected function getInterface ():string {

			return GreaterFields::class;
		}

		public function caseACondition (int $fieldA, int $fieldB):bool {

			return $fieldA > $fieldB;
		}

		public function caseBCondition (int $fieldB, int $fieldA):bool {

			return $fieldB > $fieldA;
		}
	}
?>
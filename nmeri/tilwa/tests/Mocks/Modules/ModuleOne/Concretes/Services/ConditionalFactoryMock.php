<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleOne\Concretes\Services;

	use Tilwa\Services\ConditionalFactory;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\Interfaces\GreaterFields;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\Concretes\Services\ConditionalHandlers\{FieldBGreater, FieldAGreater, LastLast};

	class ConditionalFactoryMock extends ConditionalFactory {

		protected function manufacture (int $fieldA, int $fieldB, int $fieldC):void {

			$this->whenCase($this->caseACondition, FieldAGreater::class, $fieldA, $fieldC)

			->whenCase($this->caseBCondition, FieldBGreater::class, $fieldB, $fieldA)

			->finally( LastLast::class, $fieldC);
		}

		protected function getInterface ():string {

			return GreaterFields::class;
		}

		private function caseACondition (int $fieldA, int $fieldB):bool {

			return $fieldA > $fieldB;
		}

		private function caseBCondition (int $fieldB, int $fieldA):bool {

			return $fieldB > $fieldA;
		}
	}
?>
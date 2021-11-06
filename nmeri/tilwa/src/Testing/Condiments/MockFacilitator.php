<?php
	namespace Tilwa\Testing\Condiments;

	use PHPUnit\Framework\MockObject\MockObject;

	trait MockFacilitator {

		protected function positiveMock (string $target, array $overrides, array $constructorArguments):MockObject {

			return $this->positiveMockRaw($target, array_map(function ($value) {

				return $this->returnValue($value);
			}, $overrides), $constructorArguments);
		}

		protected function positiveMockRaw (string $target, array $overrides, array $constructorArguments):MockObject {

			return $this->mockRaw($target, $overrides, true, $constructorArguments);
		}

		/**
		 * Use when the other methods contain actions we don't wanna trigger
		*/
		protected function negativeMock (string $target, array $overrides, array $constructorArguments):MockObject {

			return $this->negativeMockRaw($target, array_map(function ($value) {

				return $this->returnValue($value);
			}, $overrides), $constructorArguments);
		}

		protected function negativeMockRaw (string $target, array $overrides, array $constructorArguments):MockObject {

			return $this->mockRaw($target, $overrides, false, $constructorArguments);
		}

		private function mockRaw (string $target, array $overrides, bool $retainOtherMethods, array $constructorArguments = null):MockObject {

			$builder = $this->getMockBuilder($target);

			if ($retainOtherMethods)

				$builder->setMethods(array_keys($overrides));

			if (!is_null($constructorArguments))

				$builder->setConstructorArgs($constructorArguments);

			$builder = $builder->getMock();

            $built = $builder->expects($this->any());

            foreach ($overrides as $method => $newValue)

            	$built->method($method)->will($newValue);

			return $builder;
		}
	}
?>
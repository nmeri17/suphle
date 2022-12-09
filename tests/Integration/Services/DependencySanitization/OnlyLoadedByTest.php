<?php
	namespace Suphle\Tests\Integration\Services\DependencySanitization;;

	use Suphle\Exception\Explosives\Generic\UnacceptableDependency;

	use Suphle\Tests\Mocks\Modules\ModuleOne\Concretes\{BCounter, Services\FailForMailable};

	class OnlyLoadedByTest extends TestSanitization {

		protected const FORBIDDEN = FailForMailable::class;

		protected function setSanitizationPath ():void {

			$this->sanitizer->setExecutionPath($this->getClassDir(BCounter::class));
		}

		public function test_unwanted_dependency_throws_errors () {

			// then
			$this->expectException(UnacceptableDependency::class);

			$this->expectExceptionMessageMatches(

				$this->escapeClassName(self::FORBIDDEN)
			);

			// given @see setUp path setting
			
			$this->sanitizer->protectMailBuilders();

			$this->sanitizer->cleanseConsumers(); // when
		}

		public function test_permitted_consumer_throws_no_error () {
			
			$this->sanitizer->protectMailBuilders([self::FORBIDDEN]); // given

			$this->sanitizer->cleanseConsumers(); // when

			$this->assertTrue(true);
		}
	}
?>
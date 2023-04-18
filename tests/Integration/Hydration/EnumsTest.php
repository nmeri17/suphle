<?php
	namespace Suphle\Tests\Integration\Hydration;

	use Suphle\Testing\TestTypes\IsolatedComponentTest;

	use Suphle\Tests\Mocks\Modules\ModuleOne\Concretes\{InjectsPureEnum, InjectsBackedEnum};

	use Suphle\Tests\Mocks\Modules\ModuleOne\Enums\{PureEnum, BackedEnum};

	class EnumsTest extends IsolatedComponentTest {

		public function test_gets_argument_when_pure_enum_default () {

			$sut = $this->container->getClass(InjectsPureEnum::class); // when

			$this->assertSame(PureEnum::UJU, $sut->pureEnum); // then
		}

		public function test_gets_argument_when_backed_enum_default () {

			$sut = $this->container->getClass(InjectsBackedEnum::class); // when

			$this->assertSame(BackedEnum::AGNES, $sut->backedEnum); // then
		}
	}
?>
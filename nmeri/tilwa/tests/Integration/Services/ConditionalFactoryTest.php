<?php
	namespace Tilwa\Tests\Integration\Services;

	use Tilwa\Testing\TestTypes\IsolatedComponentTest;

	use Tilwa\Tests\Integration\Generic\CommonBinds;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\Concretes\Services\ConditionalFactoryMock;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\Concretes\Services\ConditionalHandlers\{FieldBGreater, FieldAGreater, BothFieldsEqual};

	class ConditionalFactoryTest extends IsolatedComponentTest {

		use CommonBinds;

		private $factory;

		public function setUp ():void {

			parent::setUp();

			$this->factory = $this->container->getClass(ConditionalFactoryMock::class);
		}

		public function test_runs_fieldA () {

			$handler = $this->factory->retrieveConcrete(15, 10, 2); // when

			$this->assertInstanceOf(FieldAGreater::class, $handler); // then
		}

		public function test_runs_fieldB () {

			$handler = $this->factory->retrieveConcrete(10, 15, 2); // when

			$this->assertInstanceOf(FieldBGreater::class, $handler); // then
		}

		public function test_runs_fieldC () {

			$handler = $this->factory->retrieveConcrete(10, 10, 15); // when

			$this->assertInstanceOf(BothFieldsEqual::class, $handler); // then
		}
	}
?>
<?php
	namespace Tilwa\Tests\Unit\Flows;

	use Tilwa\Testing\TestTypes\IsolatedComponentTest;

	use Tilwa\Testing\Condiments\MockFacilitator;

	use Tilwa\Flows\{FlowHydrator, Structures\RouteUserNode, Previous\SingleNode};

	use DateTime;

	class FlowConfigTest extends IsolatedComponentTest {

	 	use MockFacilitator;

	 	private $hydrator, $unitNode;

		public function setUp ():void {

			parent::setUp();

			$this->hydrator = $this->container->getClass(FlowHydrator::class); // we don't wanna replace any of the methods

			$this->unitNode = new SingleNode("data");
		}

		public function test_setMaxHitsHydrator () {

			// given
			$callback = function () {

				return 5;
			};

			$this->unitNode->setMaxHits($callback);

			// then
			$sut = $this->getProphet()->prophesize(RouteUserNode::class);

			$sut->setMaxHitsHydrator($callback)->shouldBeCalled();

			$this->hydrator->runNodeConfigs( $sut->reveal(), $this->unitNode); // when
		}

		public function test_setExpiresAtHydrator () {

			// given
			$callback = function () {

				return new DateTime;
			};

			$this->unitNode->setTTL($callback);

			// then
			$sut = $this->getProphet()->prophesize(RouteUserNode::class);

			$sut->setExpiresAtHydrator($callback)->shouldBeCalled();

			$this->hydrator->runNodeConfigs( $sut->reveal(), $this->unitNode); // when
		}
	}
?>
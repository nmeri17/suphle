<?php
	namespace Suphle\Tests\Unit\Flows;

	use Suphle\Flows\{FlowHydrator, Structures\RouteUserNode, Previous\SingleNode};

	use Suphle\Testing\TestTypes\IsolatedComponentTest;

	use Suphle\Tests\Integration\Generic\CommonBinds;

	use DateTime;

	class FlowConfigTest extends IsolatedComponentTest {

		use CommonBinds;

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

			$sut = $this->positiveDouble(RouteUserNode::class, [], [// then

				"setMaxHitsHydrator" => [1, [$callback]]
			]);

			$this->hydrator->runNodeConfigs( $sut, $this->unitNode); // when
		}

		public function test_setExpiresAtHydrator () {

			// given
			$callback = function () {

				return new DateTime;
			};

			$this->unitNode->setTTL($callback);

			$sut = $this->positiveDouble(RouteUserNode::class, [], [ // then

				"setExpiresAtHydrator" => [1, [$callback]]
			]);

			$this->hydrator->runNodeConfigs( $sut, $this->unitNode); // when
		}
	}
?>
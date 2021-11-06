<?php
	namespace Tilwa\Tests\Integration\Routing\Nested;

	use Tilwa\Testing\{Proxies\FrontDoorTest, TestTypes\ModuleLevelTest};

	class PatternIndicatorTest extends ModuleLevelTest {

		use FrontDoorTest {

			FrontDoorTest::setUp as frontSetup;
		};

		public function setUp () {

			parent::setUp();

			$this->frontSetup();
		}

		protected function getModules():array {

			return [

				$this->replicateModule(ModuleOneDescriptor::class, function (Container $container) {

					$container->whenTypeAny()->needsAny([

						IRouter::class => $this->positiveMock(
							RouterMock::class,

							[
								"browserEntryRoute" => $this->getEntryCollection() // change to desired collection
							]
						)
					]);
				})
			];
		}

		public function test_nested_route_can_unlink_auth () {

			// requires entry
			// visit one occupied above but unlinked below, without impersonation and assert passage
		}
	}
?>
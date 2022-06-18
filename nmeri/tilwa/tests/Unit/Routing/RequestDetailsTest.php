<?php
	namespace Tilwa\Tests\Unit\Routing;

	use Tilwa\Contracts\Config\Router;

	use Tilwa\Request\RequestDetails;

	use Tilwa\Testing\TestTypes\IsolatedComponentTest;

	use Tilwa\Tests\Integration\Generic\CommonBinds;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\Config\RouterMock;

	class RequestDetailsTest extends IsolatedComponentTest {

		use CommonBinds;

		protected function setUp ():void {

			parent::setUp();

			$this->stubConfig ([ "apiStack" => [ // given

				"v3" => "class3",

				"v2" => "class2",

				"v1" => "class1"	
			]]);
		}

		public function test_apiVersion_gets_versions_below_given () {

			$sut = $this->getRequestDetails("api/v2/first"); // when

			$this->assertSame([

				"v2" => "class2",

				"v1" => "class1"
			], $sut->apiVersionClasses()); // then
		}

		public function test_apiVersion_doesnt_get_versions_above_given () {

			$sut = $this->getRequestDetails("api/v1/first"); // when

			$this->assertSame([

				"v1" => "class1"
			], $sut->apiVersionClasses()); // then
		}

		private function stubConfig (array $stubMethods):void {

			$this->massProvide([

				Router::class => $this->positiveDouble(RouterMock::class, $stubMethods
				)
			]);
		}

		private function getRequestDetails (string $url):RequestDetails {

			$instance = RequestDetails::fromContainer($this->container, $url);

			$instance->setIncomingVersion();

			return $instance;
		}
	}
?>
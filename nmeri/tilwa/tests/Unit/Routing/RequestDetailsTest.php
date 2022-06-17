<?php
	namespace Tilwa\Tests\Unit\Routing;

	use Tilwa\Contracts\Config\Router;

	use Tilwa\Request\RequestDetails;

	use Tilwa\Testing\TestTypes\IsolatedComponentTest;

	use Tilwa\Tests\Integration\Generic\CommonBinds;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\Config\RouterMock;

	class RequestDetailsTest extends IsolatedComponentTest {

		use CommonBinds;

		public function test_apiVersion_gets_all_below () {

			$this->stubConfig ([ "apiStack" => [

				"v3" => "class3",

				"v2" => "class2",

				"v1" => "class1"	
			]]);

			$sut = $this->getRequestDetails("api/v2/first");

			$sut->setIncomingVersion();
			
			$this->assertSame([

				"v2" => "class2",

				"v1" => "class1"
			], $sut->apiVersionClasses());
		}

		private function stubConfig (array $stubMethods):void {

			$this->massProvide([

				Router::class => $this->positiveDouble(RouterMock::class, $stubMethods
				)
			]);
		}

		private function getRequestDetails (string $url):RequestDetails {

			RequestDetails::fromContainer($this->container, $url);

			return $this->container->getClass(RequestDetails::class);
		}
	}
?>
<?php
	namespace Suphle\Tests\Unit\Routing;

	use Suphle\Contracts\Config\Router;

	use Suphle\Request\RequestDetails;

	use Suphle\Testing\TestTypes\IsolatedComponentTest;

	use Suphle\Tests\Integration\Generic\CommonBinds;

	use Suphle\Tests\Mocks\Modules\ModuleOne\Config\RouterMock;

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
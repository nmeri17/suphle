<?php
	namespace Suphle\Tests\Unit\Routing;

	use Suphle\Contracts\Config\Router;

	use Suphle\Request\RequestDetails;

	use Suphle\Hydration\Container;

	use Suphle\Testing\TestTypes\IsolatedComponentTest;

	use Suphle\Tests\Integration\Generic\CommonBinds;

	use Suphle\Tests\Mocks\Modules\ModuleOne\Config\RouterMock;

	class RequestDetailsTest extends IsolatedComponentTest {

		use CommonBinds;

		protected bool $usesRealDecorator = false;

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

			$parameters = $this->container->getMethodParameters(Container::CLASS_CONSTRUCTOR, RequestDetails::class);

			$newRequestDetail = new class (...$parameters) extends RequestDetails {

				public static $parameters;

				public static function newRequestInstance (Container $container):RequestDetails {

					return new self(...self::$parameters);
				}
			};

			$newRequestDetail::$parameters = $parameters;

			$instance = $newRequestDetail::fromContainer($this->container, $url);

			$instance->setIncomingVersion();

			return $instance;
		}
	}
?>
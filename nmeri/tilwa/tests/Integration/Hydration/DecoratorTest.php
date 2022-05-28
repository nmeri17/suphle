<?php
	namespace Tilwa\Tests\Integration\Hydration;

	use Tilwa\Hydration\DecoratorHydrator;

	use Tilwa\Testing\TestTypes\IsolatedComponentTest;

	use Tilwa\Tests\Integration\Generic\CommonBinds;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\Concretes\{ThrowsException, Services\MultiUserEditMock};

	use ProxyManager\Factory\AccessInterceptorValueHolderFactory as AccessInterceptor;

	use Throwable;

	class DecoratorTest extends IsolatedComponentTest {

		use CommonBinds;

		private $hydrator;

		protected function setUp ():void {

			parent::setUp();

			$this->hydrator = $this->container->getClass(DecoratorHydrator::class);
		}

		/*public function test_injectScope_wraps_concrete () {

			// decorate object, call that method with it, expect its type to be of proxified instance

			$this->container->getClass(MultiUserEditMock::class);
		}*/

		public function test_catches_error () {

			$awesomeClass = new ThrowsException;

			$sut = (new AccessInterceptor)->createProxy($awesomeClass, [

				"awesomeMethod" => function ($proxy, $concrete, $method, $parameters, &$earlyReturn) { // we control this, only releasing concrete method and paramters

					try {
						$result = $concrete->$method();
					}
					catch (Throwable $exception) {

						$result = 48;
					}

					$earlyReturn = true; // for all methods

					return $result;
				}
			], [

				"awesomeMethod" => function ($proxy, $concrete, $method, $parameters, $result, &$earlyReturn) {
					
					var_dump(47, $concrete, $result);

					$earlyReturn = true;

					return 63;
				}
			]);

			$this->assertSame(48, $sut->awesomeMethod());
		}
	}
?>
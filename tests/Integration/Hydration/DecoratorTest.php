<?php
	namespace Suphle\Tests\Integration\Hydration;

	use Suphle\Hydration\DecoratorHydrator;

	use Suphle\Services\Decorators\{InterceptsCalls, VariableDependencies};

	use Suphle\Testing\TestTypes\IsolatedComponentTest;

	use Suphle\Tests\Integration\Generic\CommonBinds;

	use Suphle\Tests\Mocks\Modules\ModuleOne\Concretes\{ThrowsException, Services\SystemModelEditMock1};

	use ProxyManager\Factory\AccessInterceptorValueHolderFactory as AccessInterceptor;

	use Throwable, ReflectionAttribute;

	class DecoratorTest extends IsolatedComponentTest {

		use CommonBinds;

		private DecoratorHydrator $hydrator;

		protected function setUp ():void {

			parent::setUp();

			$this->hydrator = $this->container->getClass(DecoratorHydrator::class);
		}

		public function test_getRelevantDecors_gets_correct_list () {

			$decoratorToHandler = [

				InterceptsCalls::class => null,

				VariableDependencies::class => null
			];

			$result = $this->hydrator->getRelevantDecors(

				$decoratorToHandler, SystemModelEditMock1::class
			);

			foreach ($decoratorToHandler as $decoratorName => $handler) {

				$this->assertArrayHasKey($decoratorName, $result);

				$this->assertInstanceOf(
				
					ReflectionAttribute::class, $result[$decoratorName][0]
				);
			}
		}

		public function test_catches_error () { // proof of concept

			$awesomeClass = new ThrowsException;

			$sut = (new AccessInterceptor)->createProxy($awesomeClass, [

				"awesomeMethod" => function ($proxy, $concrete, $method, $parameters, &$earlyReturn) { // we control this, only releasing concrete method and paramters

					try {
						$result = $concrete->$method();
					}
					catch (Throwable) {

						$result = 48;
					}

					$earlyReturn = true; // for all methods

					return $result;
				}
			], [

				"awesomeMethod" => function ($proxy, $concrete, $method, $parameters, $result, &$earlyReturn) {
					
					var_dump(47, $concrete, $result); // early return means this won't run

					$earlyReturn = true;

					return 63;
				}
			]);

			$this->assertSame(48, $sut->awesomeMethod());
		}
	}
?>
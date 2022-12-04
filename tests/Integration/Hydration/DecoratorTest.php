<?php
	namespace Suphle\Tests\Integration\Hydration;

	use Suphle\Hydration\DecoratorHydrator;

	use Suphle\Contracts\Services\Decorators\{MultiUserModelEdit, ServiceErrorCatcher};

	use Suphle\Testing\TestTypes\IsolatedComponentTest;

	use Suphle\Tests\Integration\Generic\CommonBinds;

	use Suphle\Tests\Mocks\Modules\ModuleOne\Concretes\{ThrowsException, Services\EmploymentEditMock};

	use ProxyManager\Factory\AccessInterceptorValueHolderFactory as AccessInterceptor;

	use Throwable;

	class DecoratorTest extends IsolatedComponentTest {

		use CommonBinds;

		private string $subDecorator = MultiUserModelEdit::class;
  private string $superDecorator = ServiceErrorCatcher::class;
  private string $decoratedClass = EmploymentEditMock::class;
  private $hydrator;

		protected function setUp ():void {

			parent::setUp();

			$this->hydrator = $this->container->getClass(DecoratorHydrator::class);
		}

		public function test_getRelevantDecors_sub_first_returns_sub () {

			$result = $this->hydrator->getRelevantDecors([

				$this->subDecorator => null, $this->superDecorator => null
			], $this->decoratedClass);

			$this->assertSame([$this->subDecorator], $result);
		}

		public function test_getRelevantDecors_super_first_returns_sub () {

			$result = $this->hydrator->getRelevantDecors([

				$this->superDecorator => null, $this->subDecorator => null
			], $this->decoratedClass);

			$this->assertSame([$this->subDecorator], $result);
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
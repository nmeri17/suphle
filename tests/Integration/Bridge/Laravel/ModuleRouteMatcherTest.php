<?php
	namespace Suphle\Tests\Integration\Bridge\Laravel;

	use Suphle\Bridge\Laravel\Routing\ModuleRouteMatcher;

	use Suphle\Contracts\Config\Laravel as ILaravel;

	use Suphle\Testing\{TestTypes\IsolatedComponentTest, Condiments\DirectHttpTest};

	use Suphle\Tests\Integration\Generic\CommonBinds;

	use Suphle\Tests\Mocks\Modules\ModuleOne\Config\LaravelMock;

	class ModuleRouteMatcherTest extends IsolatedComponentTest {

		use DirectHttpTest, CommonBinds {

			CommonBinds::simpleBinds as commonSimples;
		}
		
		public function test_getResponse_from_provided_route () {

			// given ==> [simpleBinds]

		    // when
		    $this->setHttpParams("/laravel/entry"); // calling this before sut is created since LaravelContainer needs the information

			$sut = $this->container->getClass(ModuleRouteMatcher::class); // RegistersRouteProvider->boot never runs

		   $this->assertTrue($sut->canHandleRequest()); // then
		}

		protected function simpleBinds ():array {

			return array_merge($this::commonSimples(), [

				ILaravel::class => LaravelMock::class
			]);
		}
	}
?>
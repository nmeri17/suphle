<?php
	namespace Tilwa\Tests\Integration\Bridge\Laravel;

	use Tilwa\Bridge\Laravel\Routing\ModuleRouteMatcher;

	use Tilwa\Contracts\Config\Laravel as ILaravel;

	use Tilwa\Testing\{TestTypes\IsolatedComponentTest, Condiments\DirectHttpTest};

	use Tilwa\Tests\Integration\Generic\CommonBinds;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\Config\LaravelMock;

	class ModuleRouteMatcherTest extends IsolatedComponentTest {

		use DirectHttpTest, CommonBinds;
		
		public function test_getResponse_from_provided_route () {

			// given ==> [simpleBinds]

		    // when
		    $this->setHttpParams("/laravel/entry"); // calling this before sut is created since LaravelContainer needs the information

			$sut = $this->container->getClass(ModuleRouteMatcher::class);

		   $this->assertTrue($sut->canHandleRequest()); // then
		}

		protected function simpleBinds ():array {

			return array_merge(parent::simpleBinds(), [

				ILaravel::class => LaravelMock::class
			]);
		}
	}
?>
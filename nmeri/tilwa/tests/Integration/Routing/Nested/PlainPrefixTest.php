<?php
	namespace Tilwa\Tests\Integration\Routing\Nested;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\Routes\Prefix\OuterCollection;

	use Tilwa\Tests\Integration\Routing\TestsRouter;

	class PlainPrefixTest extends TestsRouter {

		protected function getEntryCollection ():string {

			return OuterCollection::class;
		}

		public function test_nested_route_changes_handling_class () {

			$entry = $this->container->getClass($this->getEntryCollection());

			$matchingRenderer = $this->fakeRequest("/outer/use-method/without"); // when

			$this->assertNotNull($matchingRenderer);

			$this->assertNotEquals($matchingRenderer->getController(), $entry->_handlingClass()); // then
		}

		public function test_method_name_overwrites_internal_prefix () {

			$matchingRenderer = $this->fakeRequest("/outer/ignore-internal/with"); // when

			$this->assertNotNull($matchingRenderer);

			$this->assertTrue($matchingRenderer->matchesHandler("hasInner")); // then
		}
	}
?>
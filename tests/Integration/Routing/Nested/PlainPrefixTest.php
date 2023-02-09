<?php
	namespace Suphle\Tests\Integration\Routing\Nested;

	use Suphle\Tests\Mocks\Modules\ModuleOne\Routes\Prefix\OuterCollection;

	use Suphle\Tests\Integration\Routing\TestsRouter;

	class PlainPrefixTest extends TestsRouter {

		protected function getEntryCollection ():string {

			return OuterCollection::class;
		}

		public function test_nested_route_changes_handling_class () {

			$entry = $this->getContainer()->getClass($this->getEntryCollection());

			$matchingRenderer = $this->fakeRequest("/outer/use-method/without"); // when

			$this->assertNotNull($matchingRenderer);

			$controller = $matchingRenderer->getCoordinator();

			$this->assertNotEquals($controller::class, $entry->_handlingClass()); // then
		}

		public function test_method_name_overwrites_internal_prefix () {

			$matchingRenderer = $this->fakeRequest("/outer/ignore-internal/with"); // when

			$this->assertNotNull($matchingRenderer);

			$this->assertTrue($matchingRenderer->matchesHandler("hasInner")); // then
		}
	}
?>
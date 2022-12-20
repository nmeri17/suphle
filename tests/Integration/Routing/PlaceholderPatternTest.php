<?php
	namespace Suphle\Tests\Integration\Routing;

	use Suphle\Tests\Mocks\Modules\ModuleOne\Routes\CanaryCollections\DefaultCollection;

	class PlaceholderPatternTest extends TestsRouter {

		protected function getEntryCollection ():string {

			return DefaultCollection::class; // given
		}

		public function test_placeholder_doesnt_catch_longer_path () {

			$matchingRenderer = $this->fakeRequest("/5");

			$this->assertNotNull($matchingRenderer); // sanity check

			$matchingRenderer = $this->fakeRequest("/5/invalid"); // when

			$this->assertNull($matchingRenderer); // then
		}
	}
?>
<?php
	namespace Tilwa\Tests\Integration\Routing\Crud;

	use Tilwa\Tests\Integration\Routing\BaseRouterTest;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\Routes\Crud\NoPrefixRoutes;

	class NoPrefixTest extends BaseRouterTest {

		protected function getEntryCollection ():string {

			return NoPrefixRoutes::class;
		}

		public function test_collection_requires_prefix () {

			// confirm setting neither creates no crud routes
		}
	}
?>
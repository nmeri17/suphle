<?php
	namespace Tilwa\Tests\Integration\Routing\Crud;

	use Tilwa\Tests\Integration\Routing\TestsRouter;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\Routes\Crud\BasicRoutes;

	use Exception;

	class GenericTest extends TestsRouter {

		protected function getEntryCollection ():string {

			return BasicRoutes::class;
		}

		/**
	     * @dataProvider allPathsAndHandlers
	     */
		public function test_can_find_all_routes (string $requestPath, string $handler, string $httpMethod, $payload = null) {

			$matchingRenderer = $this->fakeRequest("/save-all/$requestPath", $httpMethod);

			$this->assertNotNull($matchingRenderer);

			$this->assertTrue($matchingRenderer->matchesHandler($handler));
		}

		public function allPathsAndHandlers ():array {

			return [
				["create", "showCreateForm", "get"],

				["save", "saveNew", "post"],

				["", "showAll", "get"],

				["5", "showOne", "get"],

				["edit", "updateOne", "put", ["id" => 5]],

				["delete", "deleteOne", "delete", ["id" => 5]],

				["search", "showSearchForm", "get"]
			];
		}

		public function test_can_disable_routes () {

			$matchingRenderer = $this->fakeRequest("/disable-some/save", "post"); // when

			$this->assertNull($matchingRenderer); // then
		}

		public function test_can_override_routes () {

			$matchingRenderer = $this->fakeRequest("/override/5"); // when

			$this->assertNotNull($matchingRenderer);

			$this->assertTrue($matchingRenderer->matchesHandler("myOverride")); // then
		}

		public function test_override_non_existent_throws_error () {

			$this->expectException(Exception::class); // then

			$this->fakeRequest("/non-existent/save"); // when
		}
	}
?>
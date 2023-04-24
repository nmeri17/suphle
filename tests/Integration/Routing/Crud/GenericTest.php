<?php
	namespace Suphle\Tests\Integration\Routing\Crud;

	use Suphle\Exception\Explosives\IncompatibleHttpMethod;

	use Suphle\Security\CSRF\CsrfGenerator;

	use Suphle\Testing\Condiments\BaseDatabasePopulator;

	use Suphle\Tests\Integration\Routing\TestsRouter;

	use Suphle\Tests\Mocks\Modules\ModuleOne\Routes\Crud\BasicRoutes;

	use Suphle\Tests\Mocks\Models\Eloquent\Employment;

	use Exception;

	class GenericTest extends TestsRouter {

		use BaseDatabasePopulator {

			BaseDatabasePopulator::setUp as databaseAllSetup;
		}

		protected array $csrfField;

		protected function setUp ():void {

			$this::databaseAllSetup();

			$this->csrfField = [

				CsrfGenerator::TOKEN_FIELD => $this->getContainer()

				->getClass(CsrfGenerator::class)->newToken()
			];
		}

		protected function getActiveEntity ():string {

			return Employment::class;
		}

		protected function getEntryCollection ():string {

			return BasicRoutes::class;
		}

		public function test_can_find_all_routes () {

			$this->dataProvider([

				$this->allPathsAndHandlers(...)
			], function (
				string $requestPath, string $handler, string $httpMethod,

				$payload = null
			) {

				$matchingRenderer = $this->fakeRequest(

					"/save-all/$requestPath", $httpMethod, $payload
				);

				$this->assertNotNull($matchingRenderer);

				$this->assertTrue($matchingRenderer->matchesHandler($handler));
			});
		}

		public function allPathsAndHandlers ():array {

			$payload = array_merge($this->csrfField, ["id" => 5]);

			return [
				["create", "showCreateForm", "get"],

				[
					"save", "saveNew", "post",

					array_merge($this->csrfField, [

						"title" => "Will employ for a bag of nuts",

						"employer_id" => 1,

						"salary" => 500_000
					])
				],

				["", "showAll", "get"],

				["5", "showOne", "get"],

				["edit", "updateOne", "put", $payload],

				["delete", "deleteOne", "delete", $payload],

				["search", "showSearchForm", "get"]
			];
		}

		public function test_can_disable_routes () {

			$this->expectException(IncompatibleHttpMethod::class); // then

			// In the collection, we disabled explicit HTTP method "post";url "save", which would make this request be interpreted as HTTP method "get";url ".../id"
			$this->fakeRequest("/disable-some/save", "post"); // when
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

		/**
		 * @dataProvider invalidCruds
		*/
		public function test_correctly_fails_invalid_paths (string $requestPath) {

			$matchingRenderer = $this->fakeRequest("/save-all/$requestPath"); // when

			$this->assertNull($matchingRenderer); // then // means 404
		}

		public function invalidCruds ():array {

			return [

				["/create"], // extra slash

				["create/nmeri"]
			];
		}
	}
?>
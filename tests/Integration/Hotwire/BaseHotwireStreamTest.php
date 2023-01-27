<?php
	namespace Suphle\Tests\Integration\Hotwire;

	use Suphle\Contracts\Requests\CoodinatorManager;

	use Suphle\Adapters\Presentation\Hotwire\{HotwireCoodinatorManager, HotwireStreamBuilder, HotwireAsserter, Formats\BaseHotwireStream};

	use Suphle\Response\Format\{Reload, Redirect};

	use Suphle\Security\CSRF\CsrfGenerator;

	use Suphle\Request\PayloadStorage;

	use Suphle\Hydration\Container;

	use Suphle\Exception\Diffusers\ValidationFailureDiffuser;

	use Suphle\Testing\{TestTypes\ModuleLevelTest, Proxies\WriteOnlyContainer, Proxies\Extensions\TestResponseBridge};

	use Suphle\Tests\Integration\Services\CoodinatorManager\HttpValidationTest;

	use Suphle\Tests\Mocks\Modules\ModuleOne\{Routes\HotwireCollection, Meta\ModuleOneDescriptor, Config\RouterMock};

	use Suphle\Tests\Mocks\Models\Eloquent\Employment;

	class BaseHotwireStreamTest extends ModuleLevelTest {

		use BaseDatabasePopulator, HotwireAsserter;

		protected const INITIAL_URL = "/init-post",

		DUAL_REDIRECT = "/hotwire-redirect",

		DUAL_RELOAD = "/hotwire-reload",

		POST_METHOD = "post", PUT_METHOD = "put";

		protected Container $container;

		protected array $csrfField;

		protected Employment $employment1, $employment2;

		protected function setUp ():void {

			parent::setUp();

			$container = $this->container = $this->getContainer();

			$this->massProvide([

				CoodinatorManager::class => $container->getClass(HotwireCoodinatorManager::class)
			]);

			$this->csrfField = [

				CsrfGenerator::TOKEN_FIELD => $container->getClass(CsrfGenerator::class)
				
				->newToken()
			];
		}

		protected function getModules ():array {

			return [

				$this->replicateModule(ModuleOneDescriptor::class, function(WriteOnlyContainer $container) {

					$container->replaceWithMock(Router::class, RouterMock::class, [

						"browserEntryRoute" => HotwireCollection::class
					]);
				})
			];
		}

		protected function getActiveEntity ():string {

			return Employment::class;
		}

		protected function preDatabaseFreeze ():void {

			[$this->employment1, $this->employment2] = $this->replicator->getRandomEntities(2);
		}

		/**
		 * @dataProvider userAgentHeaders
		 * @depends HttpValidationTest
		 */
		public function test_regular_renderer_failure_yields_non_hotwire_response (?string $agentHeader) {

			$this->sendFailedValidationRequest(

				"/regular-markup", self::POST_METHOD, $agentHeader
			);
		}

		public function userAgentHeaders ():array {

			return [

				[null], [BaseHotwireStream::TURBO_INDICATOR]
			];
		}

		protected function sendFailedValidationRequest (string $url, string $httpMethod, ?string $agentHeader = null):TestResponseBridge {

			return $this->from(self::INITIAL_URL)

			->$httpMethod($url, array_merge($this->csrfField, [

				"id" => $this->employment1->id,

				// given // missing id2 field
			]), [

				PayloadStorage::ACCEPTS_KEY => $agentHeader
			]) // when
			// then
			->assertUnprocessable()->assertSee("Edit form"); // "reloads", rendering initial content
		}

		public function urlsToHotwireRequests ():array {

			return [

				[
					self::DUAL_REDIRECT, self::POST_METHOD
				], [
					self::DUAL_RELOAD, self::PUT_METHOD
				]
			];
		}

		/**
		 * @dataProvider urlsToHotwireRequests
		 */
		public function test_regular_request_to_dual_failure_reverts_to_previous (string $url, string $httpMethod) {

			$this->sendFailedValidationRequest($url, $httpMethod);
		}

		public function test_hotwire_to_dual_failure_filters_current () {

			$this->dataProvider([
			
				$this->hotwireFailureContent(...)
			], function (
				string $url, string $httpMethod, callable $outputAsserter
			) {

				$response = $this->from(self::INITIAL_URL)				

				->$httpMethod($url, array_merge($this->csrfField, [

					"id" => $this->employment1->id,

					// given // missing id2 field
				]), [

					PayloadStorage::ACCEPTS_KEY => BaseHotwireStream::TURBO_INDICATOR
				]) // when
				// then
				->assertUnprocessable() // sanity check

				->assertHeader(PayloadStorage::CONTENT_TYPE_KEY, BaseHotwireStream::TURBO_INDICATOR);

				$outputAsserter($response);
			});
		}

		public function hotwireFailureContent ():array {

			return [ // these frames/forms are expected to check the presence of validation errors and render them appropriately
				[
					self::DUAL_REDIRECT, self::POST_METHOD,

					function (TestResponseBridge $response) {

						$this->assertStreamNode(BaseHotwireStream::REPLACE_ACTION, "employment_". $this->employment1->id);

						$this->assertEmploymentFrame(
							$response, BaseHotwireStream::REPLACE_ACTION,

							$this->employment1
						);
					}
				], [
					self::DUAL_RELOAD, self::PUT_METHOD,

					function (TestResponseBridge $response) {

						$this->assertStreamNode(BaseHotwireStream::UPDATE_ACTION, "employment_". $this->employment2->id);

						$this->assertEmploymentFrame(
							$response, BaseHotwireStream::UPDATE_ACTION,

							$this->employment2
						);
					}
				]
			];
		}

		protected function assertEmploymentFrame (TestResponseBridge $response, string $hotwireAction, Employment $employment):void {

			$response->assertSee(ucfirst($hotwireAction))

			->assertSee($employment->id)

			->assertSee($employment->title);
		}

		public function hotwireSuccessContent ():array {

			$employment1Id = $this->employment1->id;

			$employment2Id = $this->employment2->id;

			return [

				[
					self::DUAL_REDIRECT, self::POST_METHOD,

					function (TestResponseBridge $response) use ($employment1Id, $employment2Id) {

						$this->assertStreamNode(BaseHotwireStream::REPLACE_ACTION, "employment_". $employment1Id)

						->assertStreamNode(BaseHotwireStream::BEFORE_ACTION, "employment_". $employment2Id);

						$this->assertEmploymentFrame(
							$response, BaseHotwireStream::REPLACE_ACTION,

							$this->employment1
						)->assertEmploymentFrame(
							$response, BaseHotwireStream::BEFORE_ACTION,

							$this->employment2
						);
					}
				], [
					self::DUAL_RELOAD, self::PUT_METHOD,

					function (TestResponseBridge $response) use ($employment1Id, $employment2Id) {

						$this->assertStreamNode(BaseHotwireStream::AFTER_ACTION, "employment_". $employment1Id)

						->assertStreamNode(BaseHotwireStream::UPDATE_ACTION, "employment_". $employment2Id);

						$this->assertEmploymentFrame(
							$response, BaseHotwireStream::AFTER_ACTION,

							$this->employment1
						)->assertEmploymentFrame(
							$response, BaseHotwireStream::UPDATE_ACTION,

							$this->employment2
						);
					}
				]
			];
		}

		public function test_hotwire_to_dual_success_yields_all () {

			$this->dataProvider([
			
				$this->hotwireSuccessContent(...)
			], function (string $url, string $httpMethod, callable $outputAsserter) {

				$this->from(self::INITIAL_URL)				

				->$httpMethod($url, array_merge($this->csrfField, [

					"id" => $this->employment1->id,

					"id2" => $this->employment2->id
				]), [

					PayloadStorage::ACCEPTS_KEY => BaseHotwireStream::TURBO_INDICATOR
				]) // when
				// then
				->assertOk() // sanity check

				->assertHeader(PayloadStorage::CONTENT_TYPE_KEY, BaseHotwireStream::TURBO_INDICATOR);

				$outputAsserter($response);
			});
		}

		public function test_regular_request_to_dual_success_proceeds () {

			$this->dataProvider([
			
				$this->regularSuccessContent(...)
			], function (string $url, string $httpMethod, int $statusCode, array $expectedHeaders) {

				$response = $this->from(self::INITIAL_URL)

				->$httpMethod($url, array_merge($this->csrfField, [

					"id" => $this->employment1->id,

					"id2" => $this->employment2->id
				])) // when
				// then
				->assertOk()->assertStatus($statusCode);

				foreach ($expectedHeaders as $header => $value)

					$response->assertHeader($header, $value);
			});
		}

		public function regularSuccessContent ():array {

			return [

				[
					self::DUAL_REDIRECT, self::POST_METHOD,

					Redirect::STATUS_CODE,
					[
						PayloadStorage::LOCATION_KEY => "/"
					]
				], [
					self::DUAL_RELOAD, self::PUT_METHOD,

					Reload::STATUS_CODE,
					[
						PayloadStorage::CONTENT_TYPE_KEY => PayloadStorage::HTML_HEADER_VALUE
					]
				]
			];
		}

		/**
		 * Low-level to confirm actual behavior instead of relying on the user-facing asserter
		*/
		public function test_dual_renderer_correctly_wraps_content () {

			$this->from(self::INITIAL_URL)				

			->post(self::DUAL_REDIRECT, array_merge($this->csrfField, [

				"id" => $this->employment1->id,

				"id2" => $this->employment2->id
			]), [

				PayloadStorage::ACCEPTS_KEY => BaseHotwireStream::TURBO_INDICATOR
			]) // when
			// then
			->assertOk() // sanity check

			->assertSee(
				$this->frameOuterHtml(BaseHotwireStream::REPLACE_ACTION, $this->employment1),

				false
			);
		}

		protected function frameOuterHtml (string $hotwireAction, Employment $employment):string {

			return (string) (new HotwireStreamBuilder($hotwireAction, "employment_". $employment->id))

			->wrapContent(
				"<div class=\"outer-container\">
					<h3>{ucfirst($hotwireAction)}</h3>
					<span class=\"id-holder\">{$employment->id}</span>
					<span class=\"title\">{$employment->title}</span>
				</div>"
			);
		}

		public function test_absence_of_create_node_falls_back_to_available_on_failure () {

			$employment1Id = $this->employment1->id;

			$response = $this->from(self::INITIAL_URL)				

			->post("/no-replace-node", array_merge($this->csrfField, [

				"id" => $employment1Id,

				// given // missing id2 field
			]), [

				PayloadStorage::ACCEPTS_KEY => BaseHotwireStream::TURBO_INDICATOR
			]) // when
			// then
			->assertUnprocessable() // sanity check

			->assertHeader(PayloadStorage::CONTENT_TYPE_KEY, BaseHotwireStream::TURBO_INDICATOR);

			foreach ([
				BaseHotwireStream::APPEND_ACTION,

				BaseHotwireStream::BEFORE_ACTION
			] as $hotwireAction) {

				$this->assertStreamNode($hotwireAction, "employment_". $employment1Id);

				$this->assertEmploymentFrame($response, $hotwireAction, $this->employment1);
			}
		}

		public function test_delete_node_renders_correctly () {

			//
		}
	}
?>
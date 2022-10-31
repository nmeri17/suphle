<?php
	namespace Suphle\Tests\Integration\Flows;

	use Suphle\Contracts\Auth\{UserContract, AuthStorage};

	use Suphle\Contracts\Config\Router;

	use Suphle\Auth\Storage\TokenStorage;

	use Suphle\Flows\{FlowHydrator, Structures\PendingFlowDetails};

	use Suphle\Testing\{Proxies\WriteOnlyContainer, Condiments\EmittedEventsCatcher};

	use Suphle\Tests\Integration\Flows\Jobs\RouteBranches\JobFactory;

	use Suphle\Tests\Mocks\Modules\ModuleOne\{Routes\Flows\OriginCollection, Meta\ModuleOneDescriptor, Config\RouterMock};

	/**
	 * These are low-level tests probably redundant now. But during those early times, I think they offered granular access to Flow task creation
	*/
	class FlowRoutesTest extends JobFactory {

		use EmittedEventsCatcher;

		protected function getModules():array {

			return [

				$this->replicateModule(ModuleOneDescriptor::class, function (WriteOnlyContainer $container) {

					$container->replaceWithMock(Router::class, RouterMock::class, [

						"browserEntryRoute" => OriginCollection::class
					]);
				})
			];
		}
		
		public function test_specialized_user_can_access_his_content () {

			$this->dataProvider([

				$this->specializedUser(...)
			], function (PendingFlowDetails $context, ?UserContract $visitor) {

				// this guy makes the internal requests for us i.e. to locate renderer for each flow, provided it exists on active route collection
				$this->makeRouteBranches($context)->handle(); // when

				$resourceId = $this->expectedSavedResource($context);

				$this->setRequestVisitor($visitor); // given

				$this->assertHandledByFlow("/user-content/$resourceId"); // then
			});
		}

		public function specializedUser ():array {

			return [

				[
					$this->makePendingFlowDetails($this->contentOwner),

					$this->contentOwner
				],

				[$this->makePendingFlowDetails(), null] // create content to be mass consumed. Visiting user 5's resource as nobody should access it
			];
		}

		protected function setRequestVisitor (?UserContract $visitor):void {

			$isGuest = is_null($visitor);

			if (!$isGuest) $this->actingAs($visitor); // remove any user from preceding provider run

			else $this->getAuthStorage()->logout();
		}
		
		public function test_other_users_cant_access_specialized_user_content () {

			$this->dataProvider([

				$this->strangeUsers(...)
			], function (PendingFlowDetails $context, ?UserContract $visitor) {

				$this->makeRouteBranches($context)->handle(); // when

				$resourceId = $this->expectedSavedResource($context);

				$this->setRequestVisitor($visitor); // given

				$this->assertNotHandledByFlow("/user-content/$resourceId"); // then
			});
		}

		public function strangeUsers ():array {

			return [

				[

					$this->makePendingFlowDetails($this->contentOwner),

					$this->contentVisitor
				], // create for user 5 and visit it as user 3; should see nothing

				[

					$this->makePendingFlowDetails($this->contentOwner), null
				] // create content for user 5. Visiting as nobody should hit a brick wall
			];
		}
		
		public function test_all_can_access_generalized_content () {

			$this->dataProvider([
				$this->specializedUser(...),
				$this->strangeUsers(...)
			], function (PendingFlowDetails $context, ?UserContract $visitor) {

				$this->makeRouteBranches($context)->handle(); // when

				$resourceId = $this->expectedSavedResource($context);

				$this->setRequestVisitor($visitor); // given

				// then
				$this->assertHandledByFlow("/user-content/$resourceId");
			});
		}

		protected function expectedSavedResource (PendingFlowDetails $context):int {

			$payload = $context->getRenderer()->getRawResponse();

			return $payload[$this->originDataName]->random()["id"];
		}

		/**
		 * @dataProvider getOriginUrls
		 * @coverss RoutedRendererManager::afterRender Fudging, since this is said to be unrecommended
		*/
		public function test_visiting_origin_path_pushes_caching_job (string $url) {

			$this->assertPushedToFlow($url);
		}

		public function getOriginUrls ():array {

			return [
				["/single-node"],
				["/combine-flows"],
				["/from-service"],
				["/pipe-to"],
				["/one-of"]
			];
		}

		public function test_will_emitEvent_after_returning_flow_request() {

			$context = $this->handleDefaultPendingFlowDetails();

			$this->get("/user-content/" . $this->expectedSavedResource($context)); // when

			$this->assertHandledEvent ($this->rendererController); // then
		}

		/**
		 * Hydration doesn't even run for same wildcard same/different user, different mechanism
		*/
		public function test_wildcard_is_locked_to_mechanism () {

			$this->dataProvider([

				[$this, "userDatabase"]
			], function (UserContract $visitor) {

				$initializingContext = $this->makePendingFlowDetails($visitor);

				$this->makeRouteBranches($initializingContext)->handle();

				$hydrator = FlowHydrator::class;

				// then
				$this->massProvide([

					$hydrator => $this->negativeDouble($hydrator, [], [

						"runNodes" => [0, []]
					])
				]);

				$context = $this->makePendingFlowDetails(

					$visitor, TokenStorage::class
				);

				$this->makeRouteBranches($context)->handle(); // when

				$this->setRequestVisitor($visitor); // given
			});
		}

		public function userDatabase ():array {

			return [
				//[$this->contentOwner],
				
				[$this->contentVisitor]
			];
		}
	}
?>
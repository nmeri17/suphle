<?php
	namespace Suphle\Tests\Integration\Flows;

	use Suphle\Contracts\{Auth\UserContract, Config\Router};

	use Suphle\Flows\{OuterFlowWrapper, Structures\PendingFlowDetails};

	use Suphle\Testing\Proxies\{WriteOnlyContainer, SecureUserAssertions};

	use Suphle\Testing\Condiments\EmittedEventsCatcher;

	use Suphle\Tests\Integration\Flows\Jobs\RouteBranches\JobFactory;

	use Suphle\Tests\Mocks\Modules\ModuleOne\{Routes\Flows\OriginCollection, Meta\ModuleOneDescriptor, Config\RouterMock};

	class FlowRoutesTest extends JobFactory {

		use SecureUserAssertions, EmittedEventsCatcher;

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

				[$this, "specializedUser"]
			], function (PendingFlowDetails $context, ?UserContract $visitor, int $userId) {

				$isGuest = is_null($visitor);

				if (!$isGuest) $this->actingAs($visitor); // given

				// this guy makes the internal requests for us i.e. to locate renderer for each flow, provided it exists on active route collection
				$this->makeRouteBranches($context)->handle(); // when

				$this->assertHandledByFlow("/user-content/$userId"); // then
			});
		}

		public function specializedUser ():array {

			$contentOwner = $this->makeUser();

			return [

				[$this->makePendingFlowDetails($contentOwner), $contentOwner, $contentOwner->id],

				[$this->makePendingFlowDetails(), null, $contentOwner->id] // create content to be mass consumed. Visiting user 5's resource as nobody should access it
			];
		}
		
		public function test_other_users_cant_access_specialized_user_content () {

			$this->dataProvider([

				[$this, "strangeUsers"]
			], function (PendingFlowDetails $context, ?UserContract $visitor, int $userId) {

				if (!is_null($visitor))

					$this->actingAs($visitor); // given

				$this->makeRouteBranches($context)->handle(); // when

				$this->assertNotHandledByFlow("/user-content/$userId"); // then
			});
		}

		public function strangeUsers ():array {

			$contentOwner = $this->makeUser();

			$contentVisitor = $this->makeUser();

			return [

				[

					$this->makePendingFlowDetails($contentOwner), $contentVisitor, $contentOwner->id
				], // create for user 5 and visit it as user 3; should see nothing

				[

					$this->makePendingFlowDetails($contentOwner), null, $contentOwner->id
				] // create content for user 5. Visiting as nobody should hit a brick wall
			];
		}
		
		public function test_all_can_access_generalized_content () {

			$this->dataProvider([
				[$this, "specializedUser"],
				[$this, "strangeUsers"]
			], function (PendingFlowDetails $dummyContext, ?UserContract $visitor, int $userId) {

				if (!is_null($visitor))

					$this->actingAs($visitor);

				$this->handleDefaultPendingFlowDetails(); // when

				// then
				$this->assertHandledByFlow("/user-content/$userId");

				$this->assertHandledByFlow("/user-content/3");
			});
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

			$this->handleDefaultPendingFlowDetails();

			$this->get($this->userUrl); // when

			// OuterFlowWrapper::HIT_EVENT
			$this->assertFiredEvent ($this->rendererController); // then
		}
	}
?>
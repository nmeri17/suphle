<?php
	namespace Tilwa\Tests\Integration\Flows;

	use Tilwa\Contracts\{Auth\UserContract, Config\Router};

	use Tilwa\Flows\Structures\BranchesContext;

	use Tilwa\Testing\Proxies\{WriteOnlyContainer, SecureUserAssertions};

	use Tilwa\Tests\Integration\Flows\Jobs\RouteBranches\JobFactory;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\{Routes\Flows\OriginCollection, Meta\ModuleOneDescriptor, Config\RouterMock};

	class FlowRoutesTest extends JobFactory {

		use SecureUserAssertions;

		protected function getModules():array { // using this since we intend to make front door requests

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
			], function (BranchesContext $context, ?UserContract $visitor) {

				$isGuest = is_null($visitor);

				if (!$isGuest) $this->actingAs($visitor); // given

				// this guy makes the internal requests for us i.e. to locate renderer for each flow, provided it exists on active route collection
				$this->makeJob($context)->handle(); // when

				$this->assertHandledByFlow($this->userUrl); // then
			});
		}

		public function specializedUser ():array {

			$user = $this->makeUser(5);

			return [

				[$this->makeBranchesContext($user), $user],

				[$this->makeBranchesContext(), null] // create content to be mass consumed. Visiting user 5's resource as nobody should access it
			];
		}
		
		public function test_other_users_cant_access_specialized_user_content () {

			$this->dataProvider([

				[$this, "strangeUsers"]
			], function (BranchesContext $context, ?UserContract $visitor) {

				if (!is_null($visitor))

					$this->actingAs($visitor); // given

				$this->makeJob($context)->handle(); // when

				$this->assertNotHandledByFlow($this->userUrl); // then
			});
		}

		public function strangeUsers ():array {

			$owner5 = $this->makeUser(5);

			return [

				[$this->makeBranchesContext($owner5), $this->makeUser(3)], // create for user 5 and visit it as user 3; should see nothing

				[$this->makeBranchesContext($owner5), null] // create content for user 5. Visiting as nobody should hit a brick wall
			];
		}
		
		public function test_all_can_access_generalized_content () {

			$this->dataProvider([
				[$this, "specializedUser"],
				[$this, "strangeUsers"]
			], function (BranchesContext $dummyContext, ?UserContract $visitor) {

				if (!is_null($visitor))

					$this->actingAs($visitor);

				$this->makeJob($this->makeBranchesContext()) // given
				->handle(); // when

				// then
				$this->assertHandledByFlow($this->userUrl);

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
	}
?>
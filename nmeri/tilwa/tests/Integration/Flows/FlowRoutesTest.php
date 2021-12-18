<?php
	namespace Tilwa\Tests\Integration\Flows;

	use Tilwa\Testing\{TestTypes\ModuleLevelTest, Condiments\QueueInterceptor, Proxies\WriteOnlyContainer};

	use Tilwa\Contracts\{Auth\User, Config\Router};

	use Tilwa\Flows\BranchesContext;

	use Tilwa\Tests\Integration\Flows\Jobs\RouteBranches\JobFactory;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\{Routes\Flows\OriginCollection, ModuleOneDescriptor, Config\RouterMock};

	class FlowRoutesTest extends JobFactory {

		use QueueInterceptor;

		protected function getModules():array { // using this since we intend to make front door requests

			return [

				$this->replicateModule(ModuleOneDescriptor::class, function (WriteOnlyContainer $container) {

					$container->replaceWithMock(Router::class, RouterMock::class, [

						"browserEntryRoute" => OriginCollection::class // used by `test_visiting_origin_path_pushes_caching_job`
					]);
				})
			];
		}
		
		/**
		 * @dataProvider specializedUser
		*/
		public function test_specialized_user_can_access_his_content (BranchesContext $context, User $visitor) {

			$this->actingAs($visitor); // given

			$this->makeJob($context)->handle(); // when

			$this->assertHandledByFlow($this->userUrl); // then
		}

		public function specializedUser ():array {

			$user = $this->makeUser(5);

			return [

				[$this->makeBranchesContext($user), $user],

				[$this->makeBranchesContext(null), null] // create content to be mass consumed. Visiting user 5's resource as nobody should access it
			];
		}
		
		/**
		 * @dataProvider strangeUsers
		*/
		public function test_other_users_cant_access_specialized_user_content (BranchesContext $context, ?User $visitor) {

			if (!is_null($visitor))

				$this->actingAs($visitor); // given

			$this->makeJob($context)->handle(); // when

			$this->assertNotHandledByFlow($this->userUrl); // then
		}

		public function strangeUsers ():array {

			$user3 = $this->makeUser(3);

			return [

				[$this->makeBranchesContext($user3), $user3], // create for user 3. Visit user 5's content as user 3 should hit a brick wall

				[$this->makeBranchesContext($this->makeUser(5)), null] // create content for user 5. Visiting as nobody should hit a brick wall
			];
		}
		
		/**
		 * @dataProvider specializedUser
		 * @dataProvider strangeUsers
		*/
		public function test_all_can_access_generalized_content (BranchesContext $dummyContext, ?User $visitor) {

			if (!is_null($visitor))

				$this->actingAs($visitor);

			$this->makeJob($this->makeBranchesContext(null)) // given
			->handle(); // when

			// then
			$this->assertHandledByFlow($this->userUrl);

			$this->assertHandledByFlow("/user-content/3");
		}

		/**
		 * @dataProvider getOriginUrls
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
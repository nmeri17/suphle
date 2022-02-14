<?php
	namespace Tilwa\Tests\Integration\Authorization;

	use Tilwa\Testing\Condiments\{IsolatedComponentSecurity, PopulatesDatabaseTest};

	use Tilwa\Contracts\Config\Auth as AuthContract;

	use Tilwa\Adapters\Orms\Eloquent\Models\User;

	use Tilwa\Exception\Explosives\UnauthorizedServiceAccess;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\Authorization\Models\EmploymentsAuthorizer;

	use Tilwa\Tests\Mocks\Models\Eloquent\{Employment, Employer};

	class ModelAuthorizationTest extends TestsRouter {

		use PopulatesDatabaseTest, IsolatedComponentSecurity;

		protected function getActiveEntity ():string {

			return Employment::class;
		}

		public function test_authorized_user_can_perform_operation () {

			// given
			$user1 = User::factory()->make();

			$this->mockAuthContract();

			$employment = $this->getEmployment($user1);

			$this->actingAs($user1); // might have to mock storage method, as well

			$this->assertNull( // then

				$employment->update(["status" => "taken"]) // when
			);
		}

		private function mockAuthContract ():void {

			$authContract = AuthContract::class;

			$mockAuth = $this->positiveStub($authContract, [

				"getModelObservers" => [

					Employment::class => EmploymentsAuthorizer::class
				]
			]);

			$this->container->whenTypeAny()->needsAny([

				$authContract => $mockAuth
			]);
		}

		private function getEmployment (User $user):Employment {

			return $this->replicator->getBeforeInsertion(1, null, function ($builder) use ($user) {

				return $builder->for(Employer::factory()->for($user));
			});
		}

		public function test_unauthorized_user_cant_perform_operation () {

			$this->setExpectedException(UnauthorizedServiceAccess::class); // then

			// given
			$user1 = User::factory()->make();

			$user2 = User::factory()->make();

			$this->mockAuthContract();

			$employment = $this->getEmployment($user1);

			$this->actingAs($user2); // might have to mock storage method, as well

			$employment->update(["status" => "taken"]); // when
		}
	}
?>
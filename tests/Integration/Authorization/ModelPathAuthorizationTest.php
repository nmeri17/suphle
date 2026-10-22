<?php

namespace Suphle\Tests\Integration\Authorization;

use Suphle\Contracts\{Config\Router as RouterContract, Auth\UserContract};

use Suphle\Config\Router;

use Suphle\Tests\Mocks\Models\Eloquent\User as EloquentUser;

use Suphle\Testing\{TestTypes\ModuleLevelTest, Condiments\BaseDatabasePopulator};

use Suphle\Testing\Proxies\{WriteOnlyContainer, SecureUserAssertions, Extensions\TestResponseBridge};

use Suphle\Tests\Mocks\Models\Eloquent\{Employment, Employer};

use Suphle\Tests\Mocks\Modules\ModuleOne\{Coordinators\EmploymentEditCoordinator, Meta\ModuleOneDescriptor};

class ModelPathAuthorizationTest extends ModuleLevelTest
{
    use BaseDatabasePopulator, SecureUserAssertions {

        BaseDatabasePopulator::setUp as databaseAllSetup;
    }

    protected const EDIT_PATH = "/admin/gmulti-edit/";

    protected Employment $employment;

    protected UserContract $admin;

    protected int $randomEmploymentId;

    protected string $coordinatorName = EmploymentEditCoordinator::class;

    protected function setUp(): void
    {

        $this->databaseAllSetup();

        $this->randomEmploymentId = $this->replicator->getRandomEntity()->id;

        $this->employment = $this->replicator->modifyInsertion( // User must be an admin, otherwise the admin rule attached to the outer prefix will cause requests to fail

            1,
            [],
            function ($builder) {

                $employer = Employer::factory()

                ->for(EloquentUser::factory()->state([

                    "is_admin" => true
                ]))->create();

                return $builder->for($employer);
            }
        )->first();

        $this->admin = $this->employment->employer->user;
    }

    protected function getModules(): array
    {

        return [
            $this->replicateModule(ModuleOneDescriptor::class, function (WriteOnlyContainer $container) {

                $container->replaceWithMock(RouterContract::class, Router::class, [

                    "getCoordinatorClassesToScan" => [$this->coordinatorName]
                ]);
            })
        ];
    }

    protected function getActiveEntity(): string
    {

        return Employment::class;
    }

    public function test_nested_can_add_more_locks()
    {

        $this->actingAs($this->admin); // given

        $this->sendEditRequest($this->employment->id) // when

        ->assertOk(); // then
    }

    protected function sendEditRequest (string $employmentId):TestResponseBridge {

        return $this->get($this->expandUrl("getEmploymentDetails", [

            "id" => $employmentId
        ]));
    }

    public function test_nested_missing_all_rules_fails()
    {

        $this->actingAs($this->admin); // given

        $this->sendEditRequest($this->randomEmploymentId) // when

        ->assertForbidden(); // then
    }
}

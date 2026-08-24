<?php

namespace Suphle\Tests\Integration\Services\Proxies\MultiUserModel;

use Suphle\Contracts\{Services\Models\IntegrityModel, Config\Router as RouterContract};

use Suphle\Config\Router;

use Suphle\Contracts\Modules\DescriptorInterface;

use Suphle\Exception\Explosives\EditIntegrityException;

use Suphle\Services\DecoratorHandlers\MultiUserEditHandler;

use Suphle\Testing\{TestTypes\InvestigateSystemCrash, Condiments\BaseDatabasePopulator};

use Suphle\Testing\Proxies\{WriteOnlyContainer, SecureUserAssertions};

use Suphle\Tests\Mocks\Models\Eloquent\User as EloquentUser;

use Suphle\Tests\Mocks\Models\Eloquent\{Employment, Employer};

use Suphle\Tests\Mocks\Modules\ModuleOne\{Coordinators\EmploymentEditCoordinator, Meta\ModuleOneDescriptor};

class MultiEditGetTest extends InvestigateSystemCrash
{
    use BaseDatabasePopulator, SecureUserAssertions {

        BaseDatabasePopulator::setUp as databaseAllSetup;
    }

    protected bool $softenDisgraceful = true;

    protected Employment $employment;

    protected function setUp(): void
    {

        $this->databaseAllSetup();

        $this->employment = $this->replicator->modifyInsertion(
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
    }

    protected function getModule(): DescriptorInterface
    {

        return $this->replicateModule(ModuleOneDescriptor::class, function (WriteOnlyContainer $container) {

            $container->replaceWithMock(RouterContract::class, Router::class, [

                "getCoordinatorClassesToScan" => EmploymentEditCoordinator::class
            ]);
        });
    }

    protected function getActiveEntity(): string
    {

        return Employment::class;
    }

    public function test_unauthorized_getter_throws_error()
    {

        $this->assertWillCatchException(EditIntegrityException::class, function () { // then

            $this->get("admin/gmulti-edit-unauth"); // when
        }, EditIntegrityException::NO_AUTHORIZER);
    }

    public function test_authorized_getter_is_successful() { // analogous to above test

    	$this->actingAs($this->employment->employer->user); // given

        //$this->debugCaughtException();

        $this->get("/admin/gmulti-edit/". $this->employment->id) // when

        ->assertOk(); // then
    }
}

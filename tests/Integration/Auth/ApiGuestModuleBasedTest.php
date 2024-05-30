<?php

namespace Suphle\Tests\Integration\Auth;

use Suphle\Hydration\Container;

use Suphle\Auth\Storage\{SessionStorage, TokenStorage};

use Suphle\Tests\Mocks\Models\Eloquent\User as EloquentUser;

use Suphle\Testing\{TestTypes\ModuleLevelTest, Condiments\BaseDatabasePopulator};

use Suphle\Testing\Proxies\SecureUserAssertions;

use Suphle\Tests\Mocks\Modules\ModuleOne\Meta\ModuleOneDescriptor;

class ApiGuestModuleBasedTest extends ModuleLevelTest
{
    use BaseDatabasePopulator;
    use SecureUserAssertions;

    protected bool $debugCaughtExceptions = true;

    protected function getModules(): array
    {

        return [
            new ModuleOneDescriptor(new Container())
        ];
    }

    protected function getActiveEntity(): string
    {

        return EloquentUser::class;
    }

    public function test_cant_access_guest_midd_route_when_authed()
    {

        $user = $this->replicator->getRandomEntity();

        $this->actingAs($user, TokenStorage::class); // given

        $this->get("/api/v1/strictly-guest") // when

        ->assertOk(/*401*/); // then
    }
}

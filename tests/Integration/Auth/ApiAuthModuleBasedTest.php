<?php

namespace Suphle\Tests\Integration\Auth;

use Suphle\Hydration\Container;

use Suphle\Auth\Storage\SessionStorage;

use Suphle\Tests\Mocks\Models\Eloquent\User as EloquentUser;

use Suphle\Testing\{TestTypes\ModuleLevelTest, Condiments\BaseDatabasePopulator};

use Suphle\Testing\Proxies\SecureUserAssertions;

use Suphle\Tests\Mocks\Modules\ModuleOne\Meta\ModuleOneDescriptor;

class ApiAuthModuleBasedTest extends ModuleLevelTest
{
    use BaseDatabasePopulator;
    use SecureUserAssertions;

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

    public function test_cant_access_api_auth_route_with_session()
    {

        $user = $this->replicator->getRandomEntity();

        $this->actingAs($user, SessionStorage::class); // given

        $this->get("/api/v1/secure-segment") // when

        ->assertUnauthorized(); // then
    }
}

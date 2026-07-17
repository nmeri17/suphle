<?php

namespace Suphle\Tests\Integration\Auth;

use Suphle\Contracts\{Auth\AuthStorage, Config\Router as RouterContract};

use Suphle\Config\Router;

use Suphle\Tests\Mocks\Models\Eloquent\User as EloquentUser;

use Suphle\Testing\{TestTypes\ModuleLevelTest, Condiments\BaseDatabasePopulator};

use Suphle\Testing\Proxies\{WriteOnlyContainer, SecureUserAssertions};

use Suphle\Tests\Mocks\Modules\ModuleOne\{Meta\ModuleOneDescriptor};

use Suphle\Tests\Mocks\Modules\ModuleOne\Routes\ApiRoutes\{V2\ApiUpdate2Entry};

class BrowserVersioningTest extends ModuleLevelTest
{
    use BaseDatabasePopulator, SecureUserAssertions;

    protected function getModules(): array
    {

        return [
            $this->replicateModule(ModuleOneDescriptor::class, function (WriteOnlyContainer $container) {

                $container->replaceWithMock(
                    RouterContract::class, Router::class,
                    [
                        "getCoordinatorClassesToScan" => ApiUpdate2Entry::class
                    ]
                );
            })
        ];
    }

    protected function getActiveEntity(): string
    {

        return EloquentUser::class;
    }

    public function test_original_pattern_requires_auth()
    {

        // given no given user

        $this->get("/api/v1/cascade") // when

        ->assertUnauthorized(); // then
    }

    /**
     * This is expected behavior since lower ones are not loaded and thus can't know its auth requirements
     *
     * @depends test_original_pattern_requires_auth
    */
    public function test_overriden_pattern_doesnt_require_auth()
    {

        // given no given user

        $responseAsserter = $this->get("/api/v2/cascade"); // when

        $responseAsserter->assertOk(); // then
    }
}

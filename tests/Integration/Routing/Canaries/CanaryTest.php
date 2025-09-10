<?php

namespace Suphle\Tests\Integration\Routing\Canaries;

use Suphle\Routing\Attributes\HttpMethod;
use Suphle\Contracts\Config\Router;
use Suphle\Testing\{TestTypes\ModuleLevelTest, Proxies\WriteOnlyContainer, Condiments\BaseDatabasePopulator};
use Suphle\Tests\Mocks\Modules\ModuleOne\{Meta\ModuleOneDescriptor, Config\RouterMock};
use Suphle\Tests\Mocks\Modules\ModuleOne\Coordinators\CanaryCoordinator;
use Suphle\Tests\Mocks\Models\Eloquent\User as EloquentUser;

class CanaryTest extends ModuleLevelTest
{
    use BaseDatabasePopulator;

    protected function getModules(): array
    {
        return [
            $this->replicateModule(ModuleOneDescriptor::class, function (WriteOnlyContainer $container) {
                $container->replaceWithMock(Router::class, RouterMock::class, [
                    "getCoordinatorClassesToScan" => [
                        CanaryCoordinator::class
                    ]
                ]);
            })
        ];
    }

    protected function getActiveEntity(): string
    {
        return EloquentUser::class;
    }

    public function test_canary_route_evaluation_beta_user()
    {
        // Given: create a user in the beta group (userId < 1000)
        $betaUser = $this->replicator->modifyInsertion(1)[0];
        $this->actingAs($betaUser);

        // When
        $response = $this->get("/api/v1/beta");

        // Then
        $this->assertNotNull($response);
        $this->assertEquals(['beta' => true], $response->getData());
    }

    public function test_canary_route_evaluation_stable_user()
    {
        // Given: create a user NOT in the beta group (userId >= 1000)
        $stableUser = $this->replicator->modifyInsertion(1, ["id" => 1001])[0];
        $this->actingAs($stableUser);

        // When
        $response = $this->get("/api/v1/beta");

        // Then
        $this->assertNotNull($response);
        $this->assertEquals(['stable' => true], $response->getData());
    }

    public function test_stable_route_without_canary()
    {
        // Given
        $user = $this->replicator->modifyInsertion(1, ["id" => 1001])[0];
        $this->actingAs($user);

        // When
        $response = $this->get("/api/v1/stable");

        // Then
        $this->assertNotNull($response);
        $this->assertEquals(['stable' => true], $response->getData());
    }
}

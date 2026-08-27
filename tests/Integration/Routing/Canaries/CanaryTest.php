<?php
namespace Suphle\Tests\Integration\Routing\Canaries;

use Suphle\Contracts\Config\Router as RouterContract;

use Suphle\Config\Router;

use Suphle\Testing\{TestTypes\ModuleLevelTest, Proxies\WriteOnlyContainer, Condiments\BaseDatabasePopulator};
use Suphle\Tests\Mocks\Modules\ModuleOne\{Meta\ModuleOneDescriptor, Coordinators\CanaryCoordinator};

use Suphle\Tests\Mocks\Models\Eloquent\User as EloquentUser;

class CanaryTest extends ModuleLevelTest
{
    use BaseDatabasePopulator;

    protected function getModules(): array
    {
        return [
            $this->replicateModule(ModuleOneDescriptor::class, function (WriteOnlyContainer $container) {
                $container->replaceWithMock(RouterContract::class, Router::class, [
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

    public function test_canary_route_evaluation_beta_user() {
        
        $this->dataProvider([$this->getCanaryStates(...)], function (array $response, array $users, array $payload = []) {
            
            if (!empty($users)) $this->actingAs($users[0]); // Given
            
            $this->get("/canary/by-condition", $payload)// When
            
            ->assertFragment($response);// Then
        });
    }

    public function getCanaryStates():array {
        
        return [
            [
                ["beta" => true, "feature" => "experimental"],
                $this->replicator->getSpecificEntities(1, [
                    ["id", "<", 1000]
                ])
            ],
            [
                ["profile" => "FOO user profile!"], [], [CanaryRequestHasFoo::Marker => ""]
            ],
            [
                ['profile' => 'USER5 user profile!'],
                $this->replicator->getSpecificEntities(1, ["id" => 5])
            ],
            [
                ["stable" => true, "feature" => "production"], []
            ]
        ];
    }
}

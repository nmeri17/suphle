<?php

namespace Suphle\Tests\Integration\Routing;

use Suphle\Routing\{RouteManager, Attributes\HttpMethod};
use Suphle\Contracts\{Config\Router, Presentation\BaseRenderer};
use Suphle\Testing\{TestTypes\ModuleLevelTest, Proxies\WriteOnlyContainer};
use Suphle\Tests\Mocks\Modules\ModuleOne\{Meta\ModuleOneDescriptor, Config\RouterMock};
use Suphle\Tests\Mocks\Modules\ModuleOne\Coordinators\{TestCoordinator, PrefixedCoordinator, CanaryCoordinator};

class AttributeRoutingIntegrationTest extends ModuleLevelTest
{
    protected function getModules(): array
    {
        return [
            $this->replicateModule(ModuleOneDescriptor::class, function (WriteOnlyContainer $container) {
                $container->replaceWithMock(Router::class, RouterMock::class, [
                    "getCoordinatorPath" => "Coordinators",
                    "getCoordinatorClassesToScan" => [
                        TestCoordinator::class,
                        PrefixedCoordinator::class,
                        CanaryCoordinator::class
                    ]
                ]);
            })
        ];
    }

    protected function fakeRequest(string $url, HttpMethod $httpMethod = HttpMethod::GET, array $payload = null): ?BaseRenderer
    {
        $method = strtolower($httpMethod->value);
        
        if (is_null($payload)) {
            $this->$method($url);
        } else {
            $this->$method($url, $payload);
        }

        $router = $this->getContainer()->getClass(RouteManager::class);
        $router->findRenderer();

        return $router->getActiveRenderer();
    }

    public function test_basic_route_matching()
    {
        $url = "/api/v1/users";
        // Given
        $this->setHttpParams($url, HttpMethod::GET);

        // When
        $renderer = $this->fakeRequest($url, HttpMethod::GET);

        // Then
        $this->assertNotNull($renderer);
    }

    public function test_route_with_parameters()
    {
        $url = "/api/v1/users/123";
        // Given
        $this->setHttpParams($url, HttpMethod::GET);

        // When
        $renderer = $this->fakeRequest($url, HttpMethod::GET);

        // Then
        $this->assertNotNull($renderer);
    }

    public function test_route_with_prefix()
    {
        $url = "/api/v1/admin/users";
        // Given
        $this->setHttpParams($url, HttpMethod::GET);

        // When
        $renderer = $this->fakeRequest($url, HttpMethod::GET);

        // Then
        $this->assertNotNull($renderer);
    }

    public function test_route_with_middleware()
    {
        $url = "/api/v1/secure";
        // Given
        $this->setHttpParams($url, HttpMethod::GET);

        // When
        $renderer = $this->fakeRequest($url, HttpMethod::GET);

        // Then
        $this->assertNotNull($renderer);
        // Middleware should have been executed
    }

    public function test_canary_route_evaluation()
    {
        $url = "/api/v1/beta";
        // Given: simulate a user in the beta group (userId < 1000)
        $this->getContainer()->replaceWithMock(
            \Suphle\Contracts\Auth\AuthStorage::class,
            new class implements \Suphle\Contracts\Auth\AuthStorage {
                public function getId() { return 5; }
                public function getUser() { return null; }
                public function isLoggedIn() { return true; }
                public function get($key) { return null; }
                public function set($key, $value) {}
                public function remove($key) {}
                public function clear() {}
            }
        );
        $this->setHttpParams($url, HttpMethod::GET);
        $renderer = $this->fakeRequest($url, HttpMethod::GET);
        $this->assertNotNull($renderer);
        $response = $renderer->getContent();
        $this->assertStringContainsString('"beta":true', $response);
        $this->assertStringContainsString('"feature":"experimental"', $response);

        // Given: simulate a user NOT in the beta group (userId >= 1000)
        $this->getContainer()->replaceWithMock(
            \Suphle\Contracts\Auth\AuthStorage::class,
            new class implements \Suphle\Contracts\Auth\AuthStorage {
                public function getId() { return 1001; }
                public function getUser() { return null; }
                public function isLoggedIn() { return true; }
                public function get($key) { return null; }
                public function set($key, $value) {}
                public function remove($key) {}
                public function clear() {}
            }
        );
        $renderer = $this->fakeRequest($url, HttpMethod::GET);
        $this->assertNotNull($renderer);
        $response = $renderer->getContent();
        $this->assertStringContainsString('"stable":true', $response);
        $this->assertStringContainsString('"feature":"production"', $response);
    }

    public function test_different_http_methods()
    {
        $usersUrl = "/api/v1/users";
        $userUrl = "/api/v1/users/123";
        
        // Given & When & Then
        $getRenderer = $this->fakeRequest($usersUrl, HttpMethod::GET);
        $this->assertNotNull($getRenderer);

        $postRenderer = $this->fakeRequest($usersUrl, HttpMethod::POST, []);
        $this->assertNotNull($postRenderer);

        $putRenderer = $this->fakeRequest($userUrl, HttpMethod::PUT, []);
        $this->assertNotNull($putRenderer);

        $deleteRenderer = $this->fakeRequest($userUrl, HttpMethod::DELETE);
        $this->assertNotNull($deleteRenderer);
    }

    public function test_returns_null_for_non_existent_route()
    {
        $url = "/non-existent";
        // Given
        $this->setHttpParams($url, HttpMethod::GET);

        // When
        $renderer = $this->fakeRequest($url, HttpMethod::GET);

        // Then
        $this->assertNull($renderer);
    }

    public function test_coordinator_discovery_works()
    {
        $url = "/api/v1/test";
        // Given
        $this->setHttpParams($url, HttpMethod::GET);

        // When
        $renderer = $this->fakeRequest($url, HttpMethod::GET);

        // Then
        $this->assertNotNull($renderer);
    }

    public function test_route_prefix_inheritance()
    {
        $url = "/api/v1/admin/secure";
        // Given
        $this->setHttpParams($url, HttpMethod::GET);

        // When
        $renderer = $this->fakeRequest($url, HttpMethod::GET);

        // Then
        $this->assertNotNull($renderer);
    }
} 
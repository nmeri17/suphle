<?php

namespace Suphle\Tests\Integration\Routing;

use Suphle\Routing\{RouteManager, Attributes\HttpMethod};
use Suphle\Contracts\{Config\Router, Presentation\BaseRenderer};
use Suphle\Testing\{TestTypes\ModuleLevelTest, Proxies\WriteOnlyContainer};
use Suphle\Tests\Mocks\Modules\ModuleOne\{Meta\ModuleOneDescriptor, Config\RouterMock};
use Suphle\Tests\Mocks\Modules\ModuleOne\Coordinators\{TestCoordinator, PrefixedCoordinator};

class BasicRoutingTest extends ModuleLevelTest
{
    protected function getModules(): array
    {
        return [
            $this->replicateModule(ModuleOneDescriptor::class, function (WriteOnlyContainer $container) {
                $container->replaceWithMock(Router::class, RouterMock::class, [
                    "getCoordinatorClassesToScan" => [
                        TestCoordinator::class,
                        PrefixedCoordinator::class
                    ]
                ]);
            })
        ];
    }

    public function test_basic_route_matching()
    {
        $url = "/api/v1/users";
        
        // Given
        $this->setHttpParams($url, HttpMethod::GET);

        // When
        $response = $this->get($url);

        // Then
        $this->assertNotNull($response);
        $this->assertInstanceOf(BaseRenderer::class, $response);
    }

    public function test_route_with_parameters()
    {
        $url = "/api/v1/users/123";
        
        // Given
        $this->setHttpParams($url, HttpMethod::GET);

        // When
        $response = $this->get($url);

        // Then
        $this->assertNotNull($response);
        $this->assertInstanceOf(BaseRenderer::class, $response);
    }

    public function test_route_with_prefix()
    {
        $url = "/api/v1/admin/users";
        
        // Given
        $this->setHttpParams($url, HttpMethod::GET);

        // When
        $response = $this->get($url);

        // Then
        $this->assertNotNull($response);
        $this->assertInstanceOf(BaseRenderer::class, $response);
    }

    public function test_route_with_middleware()
    {
        $url = "/api/v1/secure";
        
        // Given
        $this->setHttpParams($url, HttpMethod::GET);

        // When
        $response = $this->get($url);

        // Then
        $this->assertNotNull($response);
        $this->assertInstanceOf(BaseRenderer::class, $response);
    }

    public function test_different_http_methods()
    {
        $usersUrl = "/api/v1/users";
        $userUrl = "/api/v1/users/123";
        
        // Given & When & Then
        $response = $this->get($usersUrl);
        $this->assertNotNull($response);

        $response = $this->post($usersUrl, []);
        $this->assertNotNull($response);

        $response = $this->put($userUrl, []);
        $this->assertNotNull($response);

        $response = $this->delete($userUrl);
        $this->assertNotNull($response);
    }

    public function test_returns_null_for_non_existent_route()
    {
        $url = "/non-existent";
        
        // Given
        $this->setHttpParams($url, HttpMethod::GET);

        // When
        $response = $this->get($url);

        // Then
        $this->assertNull($response); // Should return null for non-existent routes
    }

    public function test_coordinator_discovery_works()
    {
        $url = "/api/v1/test";
        
        // Given
        $this->setHttpParams($url, HttpMethod::GET);

        // When
        $response = $this->get($url);

        // Then
        $this->assertNotNull($response);
        $this->assertInstanceOf(BaseRenderer::class, $response);
    }

    public function test_route_prefix_inheritance()
    {
        $url = "/api/v1/admin/secure";
        
        // Given
        $this->setHttpParams($url, HttpMethod::GET);

        // When
        $response = $this->get($url);

        // Then
        $this->assertNotNull($response);
        $this->assertInstanceOf(BaseRenderer::class, $response);
    }
} 
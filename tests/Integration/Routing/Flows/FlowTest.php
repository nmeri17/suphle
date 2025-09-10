<?php

namespace Suphle\Tests\Integration\Routing\Flows;

use Suphle\Routing\Attributes\HttpMethod;
use Suphle\Contracts\Config\Router;
use Suphle\Testing\{TestTypes\ModuleLevelTest, Proxies\WriteOnlyContainer};
use Suphle\Tests\Mocks\Modules\ModuleOne\{Meta\ModuleOneDescriptor, Config\RouterMock};
use Suphle\Tests\Mocks\Modules\ModuleOne\Coordinators\FlowCoordinator;

class FlowTest extends ModuleLevelTest
{
    protected function getModules(): array
    {
        return [
            $this->replicateModule(ModuleOneDescriptor::class, function (WriteOnlyContainer $container) {
                $container->replaceWithMock(Router::class, RouterMock::class, [
                    "getCoordinatorClassesToScan" => [
                        FlowCoordinator::class
                    ]
                ]);
            })
        ];
    }

    public function test_collection_flow_pipe_to()
    {
        $url = "/api/v1/catalog/123";
        
        // Given
        $this->setHttpParams($url, HttpMethod::GET);

        // When
        $response = $this->get($url);

        // Then
        $this->assertNotNull($response);
        $this->assertEquals(['flow' => 'pipe_to'], $response->getData());
    }

    public function test_collection_flow_as_one()
    {
        $url = "/api/v1/catalog/special";
        
        // Given
        $this->setHttpParams($url, HttpMethod::GET);

        // When
        $response = $this->get($url);

        // Then
        $this->assertNotNull($response);
        $this->assertEquals(['flow' => 'as_one'], $response->getData());
    }

    public function test_collection_flow_in_range()
    {
        $url = "/api/v1/catalog/range";
        
        // Given
        $this->setHttpParams($url, HttpMethod::GET);

        // When
        $response = $this->get($url);

        // Then
        $this->assertNotNull($response);
        $this->assertEquals(['flow' => 'in_range'], $response->getData());
    }

    public function test_single_flow_alters_query()
    {
        $url = "/api/v1/products/123";
        
        // Given
        $this->setHttpParams($url, HttpMethod::GET);

        // When
        $response = $this->get($url);

        // Then
        $this->assertNotNull($response);
        $this->assertEquals(['flow' => 'alters_query'], $response->getData());
    }

    public function test_collection_flow_with_service()
    {
        $url = "/api/v1/catalog/service";
        
        // Given
        $this->setHttpParams($url, HttpMethod::GET);

        // When
        $response = $this->get($url);

        // Then
        $this->assertNotNull($response);
        $this->assertEquals(['flow' => 'with_service'], $response->getData());
    }
} 
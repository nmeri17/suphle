<?php

namespace Suphle\Tests\Integration\Routing\Commands;

use Suphle\Contracts\Config\Console;
use Suphle\Testing\Proxies\WriteOnlyContainer;
use Suphle\Tests\Mocks\Modules\ModuleOne\{Meta\ModuleOneDescriptor, Coordinators\TestCoordinator};
use Suphle\Tests\Integration\Console\TestCliRunner;
use Symfony\Component\Console\Command\Command;

class RouteDetailsCommandTest extends TestCliRunner
{
    protected function setUp(): void
    {
        $this->setAllDescriptors();
        parent::setUp();
    }

    protected function setModuleOne(): void
    {
        $this->moduleOne = $this->replicateModule(ModuleOneDescriptor::class, function (WriteOnlyContainer $container) {
            $consoleConfig = Console::class;
            
            $container->replaceWithMock($consoleConfig, $consoleConfig, [
                "commandsList" => [RouteDetailsCommand::class]
            ])
            ->replaceWithMock(RouterConfig::class, RouterConfig::class, [
                "getCoordinatorClassesToScan" => [TestCoordinator::class]
            ]);
        });
    }

    protected function getModules(): array
    {
        return [$this->moduleOne];
    }

    // Minimal test for table format - just verify it runs without error
    public function test_can_output_table_format()
    {
        $command = $this->consoleRunner->findHandler("route:details");
        $commandTester = new CommandTester($command);

        $commandTester->execute([]);

        $commandTester->assertCommandIsSuccessful();
        
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('Route Details', $output);
        $this->assertStringContainsString('Method', $output);
        $this->assertStringContainsString('Path', $output);
    }

    // Extensive tests for JSON format
    public function test_can_output_detailed_routes_in_json()
    {
        $command = $this->consoleRunner->findHandler("route:details");
        $commandTester = new CommandTester($command);

        $commandTester->execute([
            '--json' => true
        ]);

        $commandTester->assertCommandIsSuccessful();
        
        $output = $commandTester->getDisplay();
        $this->assertStringStartsWith('[', $output);
        $this->assertStringEndsWith(']', $output);
        
        $jsonData = json_decode($output, true);
        $this->assertIsArray($jsonData);
        
        if (!empty($jsonData)) {
            $route = $jsonData[0];
            $this->assertArrayHasKey('method', $route);
            $this->assertArrayHasKey('path', $route);
            $this->assertArrayHasKey('handler', $route);
            $this->assertArrayHasKey('renderer', $route);
            $this->assertArrayHasKey('flows', $route);
            $this->assertArrayHasKey('canary_state', $route);
            $this->assertArrayHasKey('validation_rules', $route);
            $this->assertArrayHasKey('parameters', $route);
            $this->assertArrayHasKey('response_shape', $route);
            $this->assertArrayHasKey('summary', $route);
            $this->assertArrayHasKey('description', $route);
        }
    }

    public function test_json_output_contains_validation_rules()
    {
        $command = $this->consoleRunner->findHandler("route:details");
        $commandTester = new CommandTester($command);

        $commandTester->execute([
            '--json' => true
        ]);

        $commandTester->assertCommandIsSuccessful();
        
        $output = $commandTester->getDisplay();
        $jsonData = json_decode($output, true);
        
        if (!empty($jsonData)) {
            $route = $jsonData[0];
            $this->assertIsArray($route['validation_rules']);
            $this->assertIsArray($route['parameters']);
            $this->assertIsArray($route['flows']);
            $this->assertIsArray($route['placeholders']);
        }
    }

    public function test_json_output_contains_response_shape()
    {
        $command = $this->consoleRunner->findHandler("route:details");
        $commandTester = new CommandTester($command);

        $commandTester->execute([
            '--json' => true
        ]);

        $commandTester->assertCommandIsSuccessful();
        
        $output = $commandTester->getDisplay();
        $jsonData = json_decode($output, true);
        
        if (!empty($jsonData)) {
            $route = $jsonData[0];
            $this->assertIsArray($route['response_shape']);
            $this->assertArrayHasKey('type', $route['response_shape']);
        }
    }

    public function test_can_filter_routes_by_module()
    {
        $command = $this->consoleRunner->findHandler("route:details");
        $commandTester = new CommandTester($command);

        $commandTester->execute([
            '--module' => 'ModuleOne',
            '--json' => true
        ]);

        $commandTester->assertCommandIsSuccessful();
        
        $output = $commandTester->getDisplay();
        $jsonData = json_decode($output, true);
        
        foreach ($jsonData as $route) {
            $this->assertStringContainsString('ModuleOne', $route['coordinator']);
        }
    }

    public function test_can_filter_routes_by_method()
    {
        $command = $this->consoleRunner->findHandler("route:details");
        $commandTester = new CommandTester($command);

        $commandTester->execute([
            '--method' => 'GET',
            '--json' => true
        ]);

        $commandTester->assertCommandIsSuccessful();
        
        $output = $commandTester->getDisplay();
        $jsonData = json_decode($output, true);
        
        foreach ($jsonData as $route) {
            $this->assertEquals('GET', $route['method']);
        }
    }

    public function test_can_filter_routes_by_path()
    {
        $command = $this->consoleRunner->findHandler("route:details");
        $commandTester = new CommandTester($command);

        $commandTester->execute([
            '--path' => 'test',
            '--json' => true
        ]);

        $commandTester->assertCommandIsSuccessful();
        
        $output = $commandTester->getDisplay();
        $jsonData = json_decode($output, true);
        
        foreach ($jsonData as $route) {
            $this->assertStringContainsString('test', $route['path']);
        }
    }

    public function test_combines_multiple_filters()
    {
        $command = $this->consoleRunner->findHandler("route:details");
        $commandTester = new CommandTester($command);

        $commandTester->execute([
            '--module' => 'ModuleOne',
            '--method' => 'GET',
            '--json' => true
        ]);

        $commandTester->assertCommandIsSuccessful();
        
        $output = $commandTester->getDisplay();
        $jsonData = json_decode($output, true);
        
        foreach ($jsonData as $route) {
            $this->assertStringContainsString('ModuleOne', $route['coordinator']);
            $this->assertEquals('GET', $route['method']);
        }
    }

    public function test_returns_success_when_no_routes_found()
    {
        $this->moduleOne = $this->replicateModule(ModuleOneDescriptor::class, function (WriteOnlyContainer $container) {
            $consoleConfig = Console::class;
            
            $container->replaceWithMock($consoleConfig, $consoleConfig, [
                "commandsList" => [RouteDetailsCommand::class]
            ])
            ->replaceWithMock(RouterConfig::class, RouterConfig::class, [
                "getCoordinatorClassesToScan" => []
            ]);
        });

        $command = $this->consoleRunner->findHandler("route:details");
        $commandTester = new CommandTester($command);

        $commandTester->execute([]);

        $commandTester->assertCommandIsSuccessful();
        
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('No routes found', $output);
    }

    public function test_json_output_structure_is_consistent()
    {
        $command = $this->consoleRunner->findHandler("route:details");
        $commandTester = new CommandTester($command);

        $commandTester->execute([
            '--json' => true
        ]);

        $commandTester->assertCommandIsSuccessful();
        
        $output = $commandTester->getDisplay();
        $jsonData = json_decode($output, true);
        
        if (!empty($jsonData)) {
            $firstRoute = $jsonData[0];
            $expectedStructure = [
                'method' => 'string',
                'path' => 'string', 
                'handler' => 'string',
                'renderer' => 'string',
                'flows' => 'array',
                'canary_state' => 'array',
                'validation_rules' => 'array',
                'parameters' => 'array',
                'response_shape' => 'array',
                'summary' => 'string',
                'description' => 'string',
                'coordinator' => 'string'
            ];
            
            foreach ($expectedStructure as $key => $expectedType) {
                $this->assertArrayHasKey($key, $firstRoute, "Route missing key: {$key}");
                
                if ($expectedType === 'array') {
                    $this->assertIsArray($firstRoute[$key], "Key {$key} should be an array");
                } elseif ($expectedType === 'string') {
                    $this->assertIsString($firstRoute[$key], "Key {$key} should be a string");
                }
            }
        }
    }
} 
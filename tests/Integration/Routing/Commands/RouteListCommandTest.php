<?php

namespace Suphle\Tests\Integration\Routing\Commands;

use Suphle\Contracts\Config\Console;
use Suphle\Testing\Proxies\WriteOnlyContainer;
use Suphle\Tests\Mocks\Modules\ModuleOne\{Meta\ModuleOneDescriptor, Coordinators\TestCoordinator};
use Suphle\Tests\Integration\Console\TestCliRunner;
use Symfony\Component\Console\Command\Command;

class RouteListCommandTest extends TestCliRunner
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
                "commandsList" => [RouteListCommand::class]
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

    public function test_can_list_all_routes()
    {
        $command = $this->consoleRunner->findHandler("route:list");
        $commandTester = new CommandTester($command);

        $commandTester->execute([]);

        $commandTester->assertCommandIsSuccessful();
        
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('Route List', $output);
        $this->assertStringContainsString('Method', $output);
        $this->assertStringContainsString('Path', $output);
        $this->assertStringContainsString('Handler', $output);
    }

    public function test_can_filter_routes_by_module()
    {
        $command = $this->consoleRunner->findHandler("route:list");
        $commandTester = new CommandTester($command);

        $commandTester->execute([
            '--module' => 'ModuleOne'
        ]);

        $commandTester->assertCommandIsSuccessful();
        
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('ModuleOne', $output);
    }

    public function test_can_filter_routes_by_method()
    {
        $command = $this->consoleRunner->findHandler("route:list");
        $commandTester = new CommandTester($command);

        $commandTester->execute([
            '--method' => 'GET'
        ]);

        $commandTester->assertCommandIsSuccessful();
        
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('GET', $output);
    }

    public function test_can_output_json_format()
    {
        $command = $this->consoleRunner->findHandler("route:list");
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
    }

    public function test_returns_success_when_no_routes_found()
    {
        $this->moduleOne = $this->replicateModule(ModuleOneDescriptor::class, function (WriteOnlyContainer $container) {
            $consoleConfig = Console::class;
            
            $container->replaceWithMock($consoleConfig, $consoleConfig, [
                "commandsList" => [RouteListCommand::class]
            ])
            ->replaceWithMock(RouterConfig::class, RouterConfig::class, [
                "getCoordinatorClassesToScan" => []
            ]);
        });

        $command = $this->consoleRunner->findHandler("route:list");
        $commandTester = new CommandTester($command);

        $commandTester->execute([]);

        $commandTester->assertCommandIsSuccessful();
        
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('No routes found', $output);
    }
} 
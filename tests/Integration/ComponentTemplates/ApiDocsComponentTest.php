<?php

namespace Suphle\Tests\Integration\ComponentTemplates;

use Suphle\Contracts\Config\ComponentTemplates;
use Suphle\Hydration\Container;
use Suphle\ComponentTemplates\ApiDocsComponent\ApiDocsComponentEntry;
use Suphle\Testing\{TestTypes\InstallComponentTest, Proxies\WriteOnlyContainer};
use Suphle\Tests\Mocks\Modules\ModuleOne\Meta\ModuleOneDescriptor;
use Suphle\Tests\Mocks\Interactions\ModuleOne;
use Suphle\ComponentTemplates\Commands\InstallComponentCommand;

class ApiDocsComponentTest extends InstallComponentTest
{
    protected Container $container;

    protected function setUp(): void
    {
        parent::setUp();
        $this->container = $this->getContainer();
    }

    protected function getModules(): array
    {
        return [
            $this->replicateModule(ModuleOneDescriptor::class, function (WriteOnlyContainer $container) {
                $config = ComponentTemplates::class;
                $container->replaceWithMock($config, $config, [
                    "getTemplateEntries" => [
                        $this->componentEntry()
                    ]
                ]);
            })
        ];
    }

    protected function componentEntry(): string
    {
        return ApiDocsComponentEntry::class;
    }

    public function test_can_install_component()
    {
        $this->assertInstalledComponent($this->getCommandOptions());
    }

    public function test_installed_component_has_controller_and_template()
    {
        $this->assertInstalledComponent($this->getCommandOptions());

        $modulePath = $this->getModulePath();
        $controllerPath = $modulePath . DIRECTORY_SEPARATOR . "Coordinators" . DIRECTORY_SEPARATOR . "ApiDocsController.php";
        $templatePath = $modulePath . DIRECTORY_SEPARATOR . "Markup" . DIRECTORY_SEPARATOR . "api-docs.blade.php";

        $this->assertFileExists($controllerPath);
        $this->assertFileExists($templatePath);

        $controllerContent = file_get_contents($controllerPath);
        $this->assertStringContainsString('class ApiDocsController', $controllerContent);
        $this->assertStringContainsString('OpenApiGeneratorService', $controllerContent);

        $templateContent = file_get_contents($templatePath);
        $this->assertStringContainsString('API Documentation', $templateContent);
        $this->assertStringContainsString('Route Details', $templateContent);
    }

    /**
     * @dataProvider overrideOptions
     */
    public function test_override_option_unserializes_properly(array $customOptions, ?array $depositArguments)
    {
        $this->assertInstalledComponent($this->getCommandOptions($customOptions), true);
    }

    protected function getCommandOptions(array $otherOverrides = []): array
    {
        return array_merge([
            InstallComponentCommand::HYDRATOR_MODULE_OPTION => ModuleOne::class
        ], $otherOverrides);
    }

    protected function getModulePath(): string
    {
        return $this->container->getClass(ModuleOneDescriptor::class)->userLandMirror();
    }
} 
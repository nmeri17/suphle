<?php
namespace Suphle\Routing;

use Suphle\Hydration\{Container, Structures\ObjectDetails};

use Suphle\Modules\Structures\ActiveDescriptors;

use Suphle\Contracts\Config\{ModuleFiles, Router as RouterConfig};

use Suphle\Routing\Analysis\ResponseSchemaAnalyzer;

use RecursiveDirectoryIterator, RecursiveIteratorIterator, RegexIterator, RecursiveRegexIterator;

class AttributeRouteScanner
{
    public function __construct(
        protected readonly ResponseSchemaAnalyzer $analyzerService,
        protected readonly ActiveDescriptors $activeDescriptors,
        protected readonly ObjectDetails $objectMeta
    ) {}

    /**
     * HTTP-specific entry point
     */
    public function scanAllModules(?string $moduleToScan = null): array
    {
        return $this->scanModulesByPath(

            fn (Container $container) => $container->getClass(RouterConfig::class)
            ->getCoordinatorPath(),
            
            $this->analyzerService->analyzeCoordinator(...),

            $moduleToScan
        );
    }

    /**
     * Scans directories and delegates analysis to a callback.
     */
    public function scanModulesByPath(callable $pathToScan, callable $onClassFound, ?string $moduleToScan = null): array
    {
        $allResults = [];

        foreach ($this->activeDescriptors->getOriginalDescriptors() as $module) {

            $runningModuleName = $module->exportsImplements();

            if (!is_null($moduleToScan) && $moduleToScan != $runningModuleName) continue;

            $container = $module->getContainer();

            $testCoordinators = $container->getClass(RouterConfig::class)->getCoordinatorClassesToScan();

            if (!empty($testCoordinators))

                $routeList = $this->scanFixedRoutes($testCoordinators, $runningModuleName);

            else {
                $phpFiles = $this->getModulePhpFiles($container, $pathToScan);

                $routeList = $this->iteratePhpFileHandles($phpFiles, $onClassFound, $runningModuleName);
            }
            $allResults = array_merge($allResults, ...$routeList );// either path returns a collection of routes under each file, whereas what we want is a flat list of all routes irrespective of parent file
        }
        return $allResults;
    }

    protected function scanFixedRoutes (array $testCoordinators, string $runningModuleName):array {

        return array_map(

            fn ($coordinatorName) => $this->analyzerService->analyzeCoordinator(
                $coordinatorName, $runningModuleName
            ),
            $testCoordinators
        );
    }

    protected function getModulePhpFiles (Container $container, callable $pathToScan):RegexIterator {

        $moduleRoot = $container->getClass(ModuleFiles::class)->activeModulePath();

        $dir = $moduleRoot . DIRECTORY_SEPARATOR . trim($pathToScan($container), DIRECTORY_SEPARATOR);

        if (!is_dir($dir)) return [];

        $directory = new RecursiveDirectoryIterator($dir);
        $iterator = new RecursiveIteratorIterator($directory);
        
        return new RegexIterator($iterator, '/^.+\.php$/i', RecursiveRegexIterator::GET_MATCH);
    }

    protected function iteratePhpFileHandles (iterable $phpFiles, callable $onClassFound, string $runningModuleName):array {

        return array_map(function($fileInfo) use ($onClassFound, $runningModuleName) {
            
            $className = $this->objectMeta->classNameFromFile($fileInfo[0]);
            
            if (!$className) return;
            
            return $onClassFound($className, $runningModuleName);
        }, $phpFiles);
    }
}
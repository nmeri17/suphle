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
        $allResults = []; // must be a flat list of all routes/methods irrespective of parent file

        foreach ($this->activeDescriptors->getOriginalDescriptors() as $module) {

            $runningModuleName = $module->exportsImplements();

            if (!is_null($moduleToScan) && $moduleToScan != $runningModuleName) continue;

            $container = $module->getContainer();

            $testCoordinators = $container->getClass(RouterConfig::class)->getCoordinatorClassesToScan();

            if (!empty($testCoordinators))

                $routeList = $this->scanFixedRoutes($testCoordinators, $runningModuleName);
            else {
                $phpFilesMatcher = $this->getModulePhpFiles($container, $pathToScan);

                if (is_null($phpFilesMatcher)) continue;

                $routeList = $this->iteratePhpFileHandles($phpFilesMatcher, $onClassFound, $runningModuleName);
            }
            $allResults = array_merge($allResults, $routeList );
        }
        return $allResults;
    }

    /**
     * @return aggregated list of all routes from each file eg. [
     *      0 => [details for method one],
            1 => [details for method two]
     *  ]
     * */
    protected function scanFixedRoutes (array $testCoordinators, string $runningModuleName):array {

        $filesRoutes = array_map( /*[
              0 => [ // coordinator 1
                    0 => [details for method one],
                    1 => [details for method two]
                    ...
                ]
                ...
          ]*/
            fn ($coordinatorName) => $this->analyzerService->analyzeCoordinator(
                $coordinatorName, $runningModuleName
            ),
            $testCoordinators
        );
        return array_merge(...$filesRoutes); // flatten them by spreading each file's list
    }

    protected function getModulePhpFiles (Container $container, callable $pathToScan):?RegexIterator {

        $moduleRoot = $container->getClass(ModuleFiles::class)->activeModulePath();

        $dir = $moduleRoot . DIRECTORY_SEPARATOR . trim($pathToScan($container), DIRECTORY_SEPARATOR);

        if (!is_dir($dir)) return null;

        $directory = new RecursiveDirectoryIterator($dir);
        $iterator = new RecursiveIteratorIterator($directory);
        
        return new RegexIterator($iterator, '/^.+\.php$/i', RecursiveRegexIterator::GET_MATCH);
    }

    protected function iteratePhpFileHandles (RegexIterator $phpFilesMatcher, callable $onClassFound, string $runningModuleName):array {

        $analyzedFileDetails = [];

        foreach ($phpFilesMatcher as $fileInfo) {
            
            $className = $this->objectMeta->classNameFromFile($fileInfo[0]);
            
            if (!$className) continue;
            
            $analyzedFileDetails = array_merge(
                $analyzedFileDetails,
                $onClassFound($className, $runningModuleName)
            );
        }
        return $analyzedFileDetails;
    }
}
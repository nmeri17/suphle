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
    public function scanAllModules(): array
    {
        return $this->scanModulesByPath(
            fn (Container $container) => $container->getClass(RouterConfig::class)
            ->getCoordinatorPath(),
            
            $this->analyzerService->analyzeCoordinator(...)
        );
    }

    /**
     * Scans directories and delegates analysis to a callback.
     */
    public function scanModulesByPath(callable $pathToScan, callable $onClassFound, ?string $moduleToScan = null): array
    {
        $allResults = [];

        foreach ($this->activeDescriptors->getOriginalDescriptors() as $module) {

            if (!is_null($moduleToScan) && $moduleToScan != $module) continue;

            $container = $module->getContainer();

            $moduleRoot = $container->getClass(ModuleFiles::class)->activeModulePath();

            $dir = $moduleRoot . DIRECTORY_SEPARATOR . trim($pathToScan($container), DIRECTORY_SEPARATOR);

            if (is_dir($dir)) {
                $directory = new RecursiveDirectoryIterator($dir);
                $iterator = new RecursiveIteratorIterator($directory);
                $phpFiles = new RegexIterator($iterator, '/^.+\.php$/i', RecursiveRegexIterator::GET_MATCH);

                foreach ($phpFiles as $fileInfo) {
                    
                    $className = $this->objectMeta->classNameFromFile($fileInfo[0]);
                    
                    if ($className) {
                        $allResults = array_merge(
                            $allResults,
                            $onClassFound($className, $module->exportsImplements())
                        );
                    }
                }
            }
        }
        return $allResults;
    }
}
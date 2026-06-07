<?php
namespace Suphle\Routing\Analysis;

use Suphle\Hydration\{BaseInterfaceLoader, Container};

use Suphle\Contracts\Config\ModuleFiles;

use Psalm\Config;

use Composer\InstalledVersions;

class PsalmCodebaseLoader extends BaseInterfaceLoader
{
    public function __construct(
        protected readonly ModuleFiles $fileConfig,

        protected readonly Container $container
    ) { }

    public function bindArguments():array {

        $versionName = "PSALM_VERSION";

        if (!defined($versionName)) { // they internally want this

            $versionNum = InstalledVersions::getPrettyVersion('vimeo/psalm') ?? '5.0.0';
            
            define($versionName, $versionNum);
        }

        $baseDir = $this->fileConfig->getRootPath();

        $configBinding = [
            Config::class => Config::getConfigForPath($baseDir, $baseDir) // where to find psalm.xml, folder to scan
        ];

        $this->container->whenTypeAny()->needsArguments($configBinding); // some other internal types break in the absence of this
       
        return $configBinding;
    }

    public function afterBind ($initialized):void {

        $initialized->bootstrapEnv();
    }

    public function concreteName(): string
    {
        return PsalmCodebaseWrapper::class;
    }
}
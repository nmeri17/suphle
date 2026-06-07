<?php
namespace Suphle\Routing\Analysis;

use Suphle\Contracts\PsalmCodebase;

use Psalm\{Codebase, Config};

use Psalm\Internal\{IncludeCollector, ErrorHandler, Codebase\Methods, Analyzer\ProjectAnalyzer};

use Psalm\Internal\Provider\{FileProvider, Providers};

use Composer\InstalledVersions;

use ReflectionClass;

class PsalmCodebaseWrapper implements PsalmCodebase {

	protected Codebase $cantExtend;

	public function __construct(
        protected readonly Config $config,
        protected readonly Providers $providers
    ) {
    	$this->cantExtend = new Codebase($config, $providers, null);
    }

	public function bootstrapEnv(): void
	{
		$versionName = "PHP_PARSER_VERSION";

		if (!defined($versionName)) {

			$versionNum = InstalledVersions::getPrettyVersion('nikic/php-parser') ?? 'unknown';

			define($versionName, $versionNum);
		}
		$this->config->setIncludeCollector(new IncludeCollector());
	}
		public function scanSingleClass(string $fqcn): void {

		$projectAnalyzer = new ProjectAnalyzer($this->config, $this->providers);

		$codebase = $projectAnalyzer->getCodebase();

		// Find the actual file path on disk using reflection
		$reflection = new ReflectionClass($fqcn);
		$filePath = $reflection->getFileName();

		if ($filePath) {
			
			// Force Psalm to register and parse ONLY this specific file's AST
			// This satisfies Psalm's internal list indexing loops.
			$codebase->scanner->addFilesToDeepScan([
				$filePath => true
			]);
			
			$codebase->scanner->scanFiles($codebase->classlikes);
			
			// Populates the internal ClassLikeStorageProvider maps for this file
			$projectAnalyzer->check($filePath);
		}
	}

	public function scanEntireProject ():void {

		$projectAnalyzer = new ProjectAnalyzer($this->config, $this->providers);

		$codebase = $projectAnalyzer->getCodebase();

        // DYNAMIC FIX: Fetch only the specific target directories defined in psalm.xml
        // (e.g., <directory name="src" />, <directory name="app" />)
        $targetDirectories = $this->config->getProjectDirectories();

        if (!empty($targetDirectories)) {
            // Queue only those safe directories, filtering out 'vendor' automatically
			$codebase->scanner->addFilesToDeepScan($targetDirectories);
			$codebase->scanner->scanFiles($codebase->classlikes);
			
			// Execute the analysis specifically on your scoped userland directories
			foreach ($targetDirectories as $directory)
				$projectAnalyzer->check($directory);
        } else {
            // Fallback safety if the user's psalm.xml has no directory targets configured
            $projectAnalyzer->check($this->config->base_dir);
        }
	}

	public function getMethodAnalyzer():Methods {

		return $this->cantExtend->methods;
	}
}
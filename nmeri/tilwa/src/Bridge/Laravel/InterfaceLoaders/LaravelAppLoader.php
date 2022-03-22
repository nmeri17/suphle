<?php
	namespace Tilwa\Bridge\Laravel\InterfaceLoaders;

	use Tilwa\Hydration\BaseInterfaceLoader;

	use Tilwa\Bridge\Laravel\LaravelAppConcrete;

	use Tilwa\Bridge\Laravel\Config\{ConfigLoader, ConfigFileFinder};

	use Tilwa\Contracts\Config\{ModuleFiles, Laravel as LaravelConfig};

	class LaravelAppLoader extends BaseInterfaceLoader {

		private $fileConfig, $laravelConfig, $configLoader;

		public function __construct (ModuleFiles $fileConfig, LaravelConfig $laravelConfig, ConfigLoader $configLoader) {

			$this->fileConfig = $fileConfig;

			$this->laravelConfig = $laravelConfig;

			$this->configLoader = $configLoader;
		}

		public function afterBind ($initialized):void {

			$initialized->setBasePath($this->getBasePath());

			$initialized->injectBindings($initialized->defaultBindings());

			(new ConfigFileFinder)

			->loadConfigurationFiles($initialized, $this->configLoader);
		}

		public function concrete():string {

			return LaravelAppConcrete::class;
		}

		protected function getBasePath ():string {

			return $this->fileConfig->activeModulePath() . $this->laravelConfig->frameworkDirectory();
		}
	}
?>
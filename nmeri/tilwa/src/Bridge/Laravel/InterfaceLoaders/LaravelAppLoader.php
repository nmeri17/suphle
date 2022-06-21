<?php
	namespace Tilwa\Bridge\Laravel\InterfaceLoaders;

	use Tilwa\Hydration\BaseInterfaceLoader;

	use Tilwa\Bridge\Laravel\LaravelAppConcrete;

	use Tilwa\Bridge\Laravel\Config\{ConfigLoader, ConfigFileFinder};

	use Tilwa\Contracts\Config\{ModuleFiles, Laravel as LaravelConfig};

	use Tilwa\Contracts\Bridge\LaravelContainer;

	class LaravelAppLoader extends BaseInterfaceLoader {

		private $fileConfig, $laravelConfig, $configLoader;

		public function __construct (ModuleFiles $fileConfig, LaravelConfig $laravelConfig, ConfigLoader $configLoader) {

			$this->fileConfig = $fileConfig;

			$this->laravelConfig = $laravelConfig;

			$this->configLoader = $configLoader;
		}

		public function bindArguments():array {

			return [

				"basePath" => $this->getBasePath()
			];
		}

		public function concrete():string {

			return LaravelAppConcrete::class;
		}

		public function afterBind ($initialized):void {

			$this->injectBindings($initialized); // required for below call

			$initialized->ensureHasLoadedHelpers();

			$this->attendToConfig($initialized);

			$initialized->runContainerBootstrappers();
		}

		private function injectBindings (LaravelContainer $laravelContainer):void {

			$laravelContainer->registerConcreteBindings($laravelContainer->concreteBinds());

			$laravelContainer->registerSimpleBindings($laravelContainer->simpleBinds());
		}

		/**
		  * Leaving this here instead of in app bootstrappers so:
			* 1) we can inject custom loader
			* 2) we deliberately want to avoid calling [bootstrap]
		*/
		protected function attendToConfig (LaravelContainer $laravelContainer):void {

			$finder = new ConfigFileFinder;

			$finder->loadConfigurationFiles($laravelContainer, $this->configLoader);

			foreach ($finder->getConfigNames($laravelContainer) as $fileName)

				$this->configLoader->get($fileName);
		}

		protected function getBasePath ():string {

			return $this->fileConfig->activeModulePath() . $this->laravelConfig->frameworkDirectory();
		}
	}
?>
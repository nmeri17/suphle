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

		public function afterBind ($initialized):void {

			$this->injectBindings($initialized); // required for below call

			$initialized->createSandbox(function () use ($initialized) {

				(new ConfigFileFinder)

				->loadConfigurationFiles($initialized, $this->configLoader); // leaving this here instead of in app bootstrappers so we can inject custom loader

				$initialized->runContainerBootstrappers();
			});
		}

		private function injectBindings (LaravelContainer $laravelContainer):void {

			$laravelContainer->registerConcreteBindings($laravelContainer->concreteBinds());

			$laravelContainer->registerSimpleBindings($laravelContainer->simpleBinds());
		}

		public function concrete():string {

			return LaravelAppConcrete::class;
		}

		protected function getBasePath ():string {

			return $this->fileConfig->activeModulePath() . $this->laravelConfig->frameworkDirectory();
		}
	}
?>
<?php
	namespace Tilwa\Bridge\Laravel\Package;

	use Tilwa\Hydration\Container;

	use Tilwa\Bridge\Laravel\Package\Templates\ProvidedServiceWrapper;

	use Tilwa\Contracts\{Bridge\LaravelContainer, Config\Laravel as LaravelConfig, Hydration\ExternalPackageManager};

	use Illuminate\Support\ServiceProvider;

	class LaravelProviderManager implements ExternalPackageManager {

		private $provider, $concrete, $laravelContainer,

		$config, $tilwaContainer;

		public function __construct ( LaravelContainer $laravelContainer, LaravelConfig $config, Container $tilwaContainer) {

			$this->laravelContainer = $laravelContainer;

			$this->tilwaContainer = $tilwaContainer;

			$this->config = $config;
		}

		public function setActiveProvider (string $provider):void {

			$this->provider = new $provider($this->laravelContainer);
		}
		
		public function getActiveProvider ():ServiceProvider {

			return $this->provider;
		}

		public function canProvide (string $fullName):bool {

			$config = $this->config;

			return $config->usesPackages() && array_key_exists( $fullName, $config->getProviders() );
		}

		/**
		 * @return Instance of {fullName}
		*/
		public function manageService (string $fullName) {

			$providerName = $this->config->getProviders()[$fullName];

			$this->setActiveProvider($providerName);

			$this->extractConcrete();
		
			$this->provider->boot();

			return $this->concrete;
		}

		private function extractConcrete ():void {

			$currentBindings = $this->laravelContainer->getBindings();

			$this->provider->register();

			$newBindings = $this->laravelContainer->getBindings();

			$latestKey = array_diff_key($currentBindings, $newBindings);

			$this->concrete = $newBindings[current($latestKey)]["concrete"]();
		}
	}
?>
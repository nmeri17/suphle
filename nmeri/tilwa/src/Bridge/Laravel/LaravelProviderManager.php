<?php
	namespace Tilwa\Bridge\Laravel;

	use Illuminate\Support\ServiceProvider;

	use Illuminate\Foundation\Application;

	use ReflectionClass;

	class LaravelProviderManager {

		private $provider, $concrete, $laravelContainer;

		function __construct (ServiceProvider $provider, Application $laravelContainer) {

			$this->provider = $provider;

			$this->laravelContainer = $laravelContainer;
		}

		public function getConcrete():object {

			return $concrete;
		}

		private function mirrorBehavior ():self {

			require_once $this->getHelperFilePath();

			function app () { // this should live within the scope running the provider

				return $this->laravelContainer;
			}
			// run boot method using our own config implementations
			/*publishes([$configPath => config_path('social-share.php')]);
	        $this->loadViewsFrom(__DIR__.'/../../views', 'social-share');

	        mergeConfigFrom*/
		
		}

		private function extractConcrete ():self {

			$currentBindings = $this->laravelContainer->getBindings();

			$this->provider->register();

			$newBindings = $this->laravelContainer->getBindings();

			$latestKey = array_diff_key($currentBindings, $newBindings);

			$this->concrete = $newBindings[current($latestKey)]["concrete"]();

			return $this;
		}

		public function prepare ():self {

			return $this->extractConcrete()->mirrorBehavior();
		}

		// use a known class within that namespace to pull the file's directory
		private function getHelperFilePath ():string {

			$knownClass = new ReflectionClass(Application::class);

			$namespaces = explode("\\", $knownClass->getFileName(), -1);

			$namespaces[] = "helpers.php";

			return implode(DIRECTORY_SEPARATOR, $namespaces);
		}
	}
?>
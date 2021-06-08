<?php
	namespace Tilwa\Bridge\Laravel;

	use Illuminate\Support\ServiceProvider;

	use Tilwa\Contracts\LaravelApp;

	use ReflectionClass;

	class LaravelProviderManager {

		private $provider, $concrete, $laravelContainer;

		function __construct (ServiceProvider $provider, LaravelApp $laravelContainer) {

			$this->provider = $provider;

			$this->laravelContainer = $laravelContainer;
		}

		public function getConcrete():object {

			return $concrete;
		}

		private function mirrorBehavior ():self {

			require_once $this->getHelperFilePath(); // we need this file active while running their routes so it can pick [view()]

			function app () { // override their definition

				return $this->laravelContainer;
			}
			
			$this->provider->boot();
		
			return $this;
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
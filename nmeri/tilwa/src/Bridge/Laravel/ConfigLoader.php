<?php
	namespace Tilwa\Bridge\Laravel;

	use Illuminate\Config\Repository;

	use Tilwa\Contracts\Config\{Laravel, ConfigMarker};

	use Tilwa\App\Container;

	class ConfigLoader extends Repository {

		private $laravelConfig, $container;

		private $pathSegments = [];

	    public function __construct(array $items = [], Laravel $laravelConfig, Container $container) {

	        $this->items = $items;

	        $this->laravelConfig = $laravelConfig;

	        $this->container = $container;
	    }

		public function get($key, $default = null) {

	        if (is_array($key))

	            return $this->getMany($key);

			$this->setPathSegments($key);

	        $configClass = $this->findEquivalent();

	        if ($configClass) {

	        	$configConcrete = $this->container->getClass($configClass);

	        	$property = $this->findProperty($configConcrete);

	        	if ($property) return $property;
	        }

	        return Arr::get($this->items, $key, $default);
	    }

	    private function setPathSegments(string $dotPath):void {

	        $this->pathSegments = explode(".", $dotPath);
	    }

	    private function findEquivalent ():string {

	        $bridge = $this->laravelConfig->configBridge();

	        $name = array_shift($this->pathSegments);

	        if (array_key_exists($name, $bridge))

	        	return $bridge[$name];
	    }

	    private function findProperty (ConfigMarker $config) {

	    	$currentContext = null;

	    	foreach ($this->pathSegments as $segment) {

	    		if (is_null($currentContext))
	    		
	    			$currentContext = $config->$segment;

	    		else $currentContext = $currentContext->$segment;
	    	}

	    	return $currentContext;
	    }

	    // @TODO override [getMany]
	}
?>
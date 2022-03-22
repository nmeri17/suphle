<?php
	namespace Tilwa\Bridge\Laravel\Config;

	use Illuminate\Config\Repository;

	use Tilwa\Contracts\Config\Laravel;

	use Tilwa\Hydration\Container;

	class ConfigLoader extends Repository {

		private $laravelConfig, $container, $pathSegments = [];

	    public function __construct(Laravel $laravelConfig, Container $container) {

	        $this->laravelConfig = $laravelConfig;

	        $this->container = $container;
	    }

	    /**
	     * Any call by their config function to access value in a file should defer to their paired OOP counterpart
	    */
		public function get($key, $default = null) {

	        if (is_array($key))

	            return $this->getMany($key);

			$this->pathSegments = explode(".", $key);

	        $name = array_shift($this->pathSegments);

	        if ($configClass = $this->findEquivalent($name)) {

	        	$property = $this->findProperty($this->getConfigConcrete($configClass, $name));

	        	if (!is_null($property)) return $property;
	        }

	        return parent::get( $key, $default);
	    }

	    private function findEquivalent (string $name):?string {

	        $bridge = $this->laravelConfig->configBridge();

	        if (array_key_exists($name, $bridge))

	        	return $bridge[$name];
	    }

	    /**
	     * @return mixed. Result of calling methods on the config
	    */
	    private function findProperty (BaseConfigLink $config) {

	    	$currentContext = null;

	    	foreach ($this->pathSegments as $segment) {

	    		if (is_null($currentContext))
	    		
	    			$currentContext = $config->$segment();

	    		else $currentContext = $currentContext->$segment();
	    	}

	    	return $currentContext;
	    }

	    private function getConfigConcrete (string $className, string $configName):BaseConfigLink {

	    	return $this->container->whenType($className)

	    	->needsArguments([

	    		"nativeValues" => parent::get($configName)
	    	])

	    	->getClass($className);
	    }

	    /**
	     *  @todo override [getMany]
	    */
	    public function getMany ($keys):array {

	    	return [];
	    }
	}
?>
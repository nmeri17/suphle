<?php

namespace Suphle\Bridge\Laravel\Config;

use Suphle\Hydration\Container;

use Suphle\Contracts\Config\Laravel;

use Suphle\Services\Decorators\BindsAsSingleton;

use Illuminate\Config\Repository;

#[BindsAsSingleton]
class ConfigLoader extends Repository
{
    protected array $pathSegments = [];

    /**
     * Even though we don't receive $this->items in the constructor like the parent, LaravelAppLoader manually triggers the process by injecting/setting each config
    */
    public function __construct(
        protected readonly Laravel $laravelConfig,
        protected readonly Container $container
    ) {

        //
    }

    /**
     * Any call by their config function to access value in a file should defer to their paired OOP counterpart
    */
    public function get($key, $default = null)
    {

        if (is_array($key)) {

            return $this->getMany($key);
        }

        $this->pathSegments = explode(".", $key);

        $name = array_shift($this->pathSegments);

        if ($configClass = $this->findEquivalent($name)) {

            $property = $this->findProperty($this->getConfigConcrete($configClass, $name));

            if (!is_null($property)) {
                return $property;
            }
        }

        return parent::get($key, $default);
    }

    private function findEquivalent(string $name): ?string
    {

        $bridge = $this->laravelConfig->configBridge();

        if (array_key_exists($name, $bridge)) {

            return $bridge[$name];
        }

        return null;
    }

    /**
     * @return mixed. Result of calling methods on the config
    */
    private function findProperty(BaseConfigLink $config)
    {

        $currentContext = null;

        foreach ($this->pathSegments as $segment) {

            if (!method_exists($config, $segment)) {
                return null;
            }

            if (is_null($currentContext)) {

                $currentContext = $config->$segment();
            } else {
                $currentContext = $currentContext->$segment();
            }
        }

        return $currentContext;
    }

    private function getConfigConcrete(string $className, string $configName): BaseConfigLink
    {

        return $this->container->whenType($className)

        ->needsArguments([

            "nativeValues" => parent::get($configName)

        ])->getClass($className);
    }

    /**
     *  @todo override [getMany]
    */
    public function getMany($keys): array
    {

        return [];
    }
}

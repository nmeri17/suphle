<?php
	namespace Tilwa\Bridge\Laravel\Package\Templates;

    use Tilwa\Hydration\LaravelProviderManager;

	class ProvidedServiceWrapper extends <target> {

        protected $target, $manager;

		function __construct( $target, LaravelProviderManager $manager) {

            $this->target = $target;

            $this->manager = $manager;
        }

        public function __get($property) {
        	
        	return $this->target->$property;
        }

        public function __call($method, $arguments) {

            return $this->manager->createSandbox(function () use ($method, $arguments) {

                return $this->target->$method( ...$arguments);
            });
        }
    }
?>
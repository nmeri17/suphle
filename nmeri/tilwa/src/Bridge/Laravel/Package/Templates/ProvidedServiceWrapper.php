<?php
	namespace Tilwa\Bridge\Laravel\Package\Templates;

    use Tilwa\Contracts\Bridge\LaravelContainer;

	class ProvidedServiceWrapper extends <target> {

        protected $target, $laravelContainer;

		function __construct( $target, LaravelContainer $laravelContainer) {

            $this->target = $target;

            $this->laravelContainer = $laravelContainer;
        }

        public function __get($property) {
        	
        	return $this->target->$property;
        }

        public function __call($method, $arguments) {

            return $this->laravelContainer->createSandbox(function () use ($method, $arguments) {

                return $this->target->$method( ...$arguments);
            });
        }
    }
?>
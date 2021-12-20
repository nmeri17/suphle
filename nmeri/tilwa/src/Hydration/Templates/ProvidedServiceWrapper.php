<?php
	namespace Tilwa\Hydration\Templates;

	class ProvidedServiceWrapper extends <target> {

        protected $target, $sandboxPath;

		function __construct( $target, string $sandboxUrl) {

            $this->target = $target;

            $this->sandboxPath = $sandboxUrl;
        }

        public function __get($property) {
        	
        	return $this->target->$property;
        }

        public function __call($method, $arguments) {

        	require_once $this->sandboxPath;

        	return call_user_func_array([$this->target, $method], $arguments);
        }
    }
?>
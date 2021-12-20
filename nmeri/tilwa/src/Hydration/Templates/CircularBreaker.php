<?php
	namespace Tilwa\Hydration\Templates;

	class CircularBreaker extends <target> {

        protected $pointer; // the [target]

        private $container, $concrete;

		// override extended constructor for this to be instanciated
        function __construct(string $pointer, Container $container) {

            $this->pointer = $pointer;

            $this->container = $container;
        }

        public function __get($property) {

        	$this->setConcrete();
        	
        	return $this->concrete->$property;
        }

        public function __call($method, $arguments) {

        	$this->setConcrete();

        	return call_user_func_array([$this->concrete, $method], $arguments);
        }

        private function setConcrete():void {

        	if (is_null($this->concrete))

        		$this->concrete = $this->container->getClass($this->pointer);
        }
    }
?>
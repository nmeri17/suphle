<?php
	namespace Tilwa\Hydration\Templates;

	class AvoidConstructor extends <target> {

        private $target

        public function __construct(string $target) {

            $this->target = $target;
        }

        public function targetName ():string {

            return $this->target;
        }
    }
?>
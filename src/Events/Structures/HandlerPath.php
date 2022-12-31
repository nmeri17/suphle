<?php

	namespace Suphle\Events\Structures;

	class HandlerPath {

		public function __construct(protected readonly string $emittingEntity, protected readonly string $scope) {

			//
		}

		public function getEmittable():string {
			
			return $this->emittingEntity;
		}

		public function getScope():string {
			
			return $this->scope;
		}
	}
?>
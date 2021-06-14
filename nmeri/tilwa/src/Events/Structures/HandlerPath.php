<?php

	namespace Tilwa\Events\Structures;

	class HandlerPath {

		private $emittingEntity, $scope;

		public function __construct(string $emittingEntity, string $scope) {

			$this->emittingEntity = $emittingEntity;

			$this->scope = $scope;
		}

		public function getEmittable():string {
			
			return $this->emittingEntity;
		}

		public function getScope():string {
			
			return $this->scope;
		}
	}
?>
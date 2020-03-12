<?php

	namespace Models;

	trait MassUpdate {

		public function create ($entity, array $properties) {
			
			foreach (array_intersect($this->exempt, array_keys($properties)) as $prop)

			 	$entity->$prop = $properties[$prop];

			$entity->createdAt = $entity->updatedAt = date('Y-m-d H:i:s');

			return $entity;
		}
	}

?>
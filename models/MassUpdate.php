<?php

	namespace Models;

	trait MassUpdate {

		public function create ($entity, array $properties) {
			
			foreach (array_intersect($this->permit, array_keys($properties)) as $prop)

			 	$entity->$prop = $properties[$prop];

			$entity->createdAt = $entity->updatedAt = new \DateTime(date('Y-m-d H:i:s'));

			return $entity;
		}
	}

?>
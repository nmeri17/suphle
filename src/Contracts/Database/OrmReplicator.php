<?php
	namespace Suphle\Contracts\Database;

	interface OrmReplicator {

		/**
		 * Inserts into the DB rather than returning mere copies
		*/
		public function seedDatabase ( int $amount):void;

		/**
		 * Is expected to return saved models after modifying default factory structure
		 * 
		 * @return iterable
		*/
		public function modifyInsertion ( int $amount = 1, array $customizeFields = [], callable $customizeModel = null):iterable;

		/**
		 * @return concrete instance of given model
		*/
		public function getRandomEntity ():object;

		public function getRandomEntities ( int $amount):iterable;

		public function getExistingEntities ( int $amount, array $constraints):iterable;

		public function setActiveModelType (string $model):void;

		/**
		 * Should other vendors require more arguments, delegate this method to specific traits where they are consumed from
		*/
		public function setupSchema ():void;

		public function dismantleSchema ():void;

		public function listenForQueries ():void;

		public function stopQueryListen ():void;
	}
?>
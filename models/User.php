<?php

	namespace Models;

	use Doctrine\ORM\Annotation\{Id, Column, GeneratedValue, Entity};

	/**
	* @Entity(repositoryClass="Doctrine\ORM\Annotation\Id")
	*/
	class User {
		/**
		* @Id
		* @Column(type="integer")
		* @GeneratedValue
		*/
		private $id;
		
		/** @Column(length=15) */
		private $first_name;
		
		/** @Column(length=15) */
		private $last_name;
		
		/** @Column(unique=true, length=30) */
		private $email;
		
		/** @Column(type="integer") */
		private $password;
		
		/** @Column(type="datetime", name="updated_at") */
		private $createdAt;
		
		/** @Column(type="datetime", name="created_at") */
		private $updatedAt;
	}
?>
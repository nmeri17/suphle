<?php

	namespace Models;

	use Doctrine\ORM\Annotation\{Id, Column, GeneratedValue, Entity, Table};

	/**
	* @Entity(repositoryClass="Models\Repositories\User")
	* @Table(name="users")
	*/
	class User {
		/**
		* @Id
		* @Column(type="integer")
		* @GeneratedValue
		*/
		public $id;
		
		/** @Column(length=15) */
		public $first_name;
		
		/** @Column(length=15) */
		public $last_name;
		
		/** @Column(unique=true, length=30) */
		public $email;
		
		/** @Column */
		public $password;
		
		/** @Column(type="datetime", name="updated_at") */
		public $createdAt;
		
		/** @Column(type="datetime", name="created_at") */
		public $updatedAt;
		
		/** @Column(type="datetime", name="email_verified_at", nullable=true) */
		public $emailVerifiedAt;
		
		/** @Column( name="verification_code") */
		public $verificationCode;
	}
?>
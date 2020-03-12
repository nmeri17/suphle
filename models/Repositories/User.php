<?php

	namespace Models\Repositories;

	use Doctrine\ORM\EntityRepository;

	use Models\MassUpdate;


	class User extends EntityRepository {

		use MassUpdate;

		protected $permit = ['email', 'first_name', 'last_name'];
	}

?>
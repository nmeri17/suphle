<?php

	namespace Get;

	use \Get\GetController;

	use \PDO;

	
	// Read operations on data not to be formatted -- json response via ajax call? string? Plug them here!
	class TilwaGet extends GetController {

		// conn is passed, whether we need it or not
		public static function getFields (PDO $conn, string $rsxName, $dummy=[]) {
			
			$retain = [];

			return parent::getFields($conn, $rsxName, $retain);
		}

		public static function getContents (PDO $conn, string $rsxName, $config=[]) {

		    $uniqCol = ['testimonials' => 'date', 'users' => 'userId', 'settings' => 'id', 'transactions' => 'id', 'vtu-data-catalog' => 'id', 'ecurrencies' => 'currencyFrom']; // 'id' here means we don't intend to `getContents` any rows here but don't want other table calls to complain when they can't find 'name'

			return parent::getContents($conn, $rsxName, ['primaryColumns' => $uniqCol]);
		}

		public static function cancelUnpaid (PDO $conn, $urlSlug) {}

		/**
		*
		* @description: restricted to admin
		*
		* @return:{Array} => [[id-to-name map], requester=> full name]. returns all users in one fell swoop instead of page by page batching because multiple components on the front end will use the response instead of their own individual requests so we can't tell what name may be needed
		*/
		public static function idsToFullName (PDO $conn, $urlSlug) {

			session_start();

			$userData = json_decode(self::getContents($conn, $_SESSION['id']), true);

			if (in_array($userData['role'], ['admin', 'juniorAdmin'])) {

				$res = ['requester' => '', 'others' => []];

				$allUsers = $conn->prepare('SELECT firstName, lastName, userId FROM users');

				$allUsers->execute();

				while(($iter = $allUsers->fetch(PDO::FETCH_ASSOC)) && !empty($iter)) {

					$res['others'][$iter['userId']] = $iter['firstName'] . ' ' . $iter['lastName'];

					if ($_SESSION['id'] == $iter['userId']) $res['requester'] = $iter['firstName'] . ' ' . $iter['lastName'];
				}

				return json_encode($res);
			}
		}
	 }

?>
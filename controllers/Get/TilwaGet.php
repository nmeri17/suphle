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

		    $uniqCol = []; // 'id' here means we don't intend to `getContents` any rows here but don't want other table calls to complain when they can't find 'name'

			return parent::getContents($conn, $rsxName, ['primaryColumns' => $uniqCol]);
		}
	 }

?>
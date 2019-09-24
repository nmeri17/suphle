<?php

	namespace Get;

	use PDO;

	
	// Read operations on data not to be formatted -- json response via ajax call? string? API? Plug them here!
	class TilwaGet extends GetController {

		protected function getContentOptions ( string $rsxName ) {

		    $uniqCol = []; // 'id' here means we don't intend to `getContents` any rows here but don't want other table calls to complain when they can't find 'name'

			return ['primaryColumns' => $uniqCol];
		}


		public static function search ($conn, $toSearch) {

			$toSearch = self::nameCleanUp($toSearch);

			$like = "%$toSearch%";

			$conn->setAttribute( PDO::ATTR_EMULATE_PREPARES, false); // to retain int data type
			
			// this table has been deprecated. use getContents instead
			$searchRes = $conn->prepare('SELECT name, variables FROM contents WHERE `type`= ? AND `name` LIKE ? LIMIT ?');

			$searchRes->execute(['table_name', $like, 10]);


			$searchRes = $searchRes->fetchAll(PDO::FETCH_ASSOC);

			if (empty($searchRes)) $searchRes = ['no result found'];

			return json_encode($searchRes);
		}
	 }

?>
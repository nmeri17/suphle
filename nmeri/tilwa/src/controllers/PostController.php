<?php

/*Rewrite methods on this class with FeatureClue calls*/

class PostController {

	public static function createComment (PDO $conn, array $dbVars) {

		$commentBody = strip_tags($dbVars['content']);

		$obj = self::trimDataToInsert($query, $dbVars);

		$conn->prepare($query)->execute($obj);
	}

	protected static function fileUpload ($files, $uploadPaths) {}

	public static function updateViews (PDO $conn, string $name) {}
}

?>
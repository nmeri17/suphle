<?php

namespace Post;

use \PDO;

use \Get\TilwaGet;
/**
 * 
 */
class TilwaPost extends PostController {

	private static $accessLevel = [
		'page'=> ['admin', 'juniorAdmin']
	];
	
	
	// manipulate received files as desired before uploading
	public static function fileUpload ($x=null,$y=null ) {

		$userId = $_SESSION['id'];

		// change file names to reflect current user
		$dirs = [];

		$newFiles = array_map(function ($fileObj) use ($userId, &$dirs) {

			$picContext = array_search($fileObj, $_FILES);

			$dirs[] = "../assets/imgs/$picContext/";

			$fileObj['name'] = "$userId.png";

			return $fileObj;
		}, $_FILES);

		parent::fileUpload($newFiles, $dirs);
	}

	public static function create (PDO $conn, array $rawData) {

		session_start();

		$userId = $_SESSION['id'];

		$mode = $_GET['action']; // url slug

		$lock = @self::$accessLevel[$mode];

		$userData = json_decode(TilwaGet::getContents($conn, $userId), true);


		if (isset($userId) && (empty($lock) || in_array($userData['role'], $lock))) {

			if (array_key_exists('dashboard', $rawData)) $rawData = json_decode($rawData['dashboard'], true);

			$queriesMap = [
			];

			$dataToInsert = [
			];


			if (isset($queriesMap[$mode])) return $conn
				->prepare($queriesMap[$mode])

				->execute($dataToInsert[$mode] ?? $rawData);

			elseif (
				($createInstance = new \CreateRequests($conn, $rawData))

				&& method_exists($createInstance, $mode)
			) {

				return $createInstance->$mode( $userData);
			}

			return 0;
		}

		return 0;
	}

	
	public static function modify (PDO $conn, array $rawData) {

		session_start();

		$userId = $_SESSION['id'];

		if (isset($userId)) {

			$modifyInstance = new \ModifyRequests($conn);

			$mode = $_GET['action']; // url slug

			$userData = json_decode(TilwaGet::getContents($conn, $userId), true);
			

			if (array_key_exists('dashboard', $rawData)) $rawData = json_decode($rawData['dashboard'], true);


			elseif (array_key_exists('content', $rawData)) $rawData['content'] =  htmlentities($rawData['content']);

			$dataToInsert = [
			];

			$role = $userData['role'];

			$lock = @self::$accessLevel[$mode];


			// if it's not set, this path is free for all. if it's set, confirm sufficient permission
			if (empty($lock) || in_array($role, $lock)) {

				if ($mode == 'approve-trans') if (!$modifyInstance->approveTrans($rawData)) return 0;

				$conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, true); // using this because pdo complains about queries reusing named parameters


				if (($query = @$modifyInstance->queriesMap[$mode]) && !is_null($query)) return $conn->prepare($query)->execute($dataToInsert[$mode] ?? $rawData);

				return 1;
			}

			return 0;
		}
		return 0;
	}
}
?>
<?php

	namespace Post;

	use \Monolog\Logger;

	use \Monolog\Handler\StreamHandler;

	use \Get\GetController;

	/*In a nutshell, CRUD contracts*/

class PostController {


	public static function createComment (PDO $conn, array $dbVars) {

		// add default vars

		$dbVars['time'] = date("H:i d F Y");

		$commentBody = strip_tags($dbVars['content']);

		$dbVars['content'] = $commentBody;

		try {
			$query = 'INSERT INTO comment (parent, content, `time`) VALUES (:parent, :content, :time)';

			$obj = self::trimDataToInsert($query, $dbVars);

			$conn->prepare($query)->execute($obj);
		} catch (Exception $e) {			
		
			$log = new Logger('comment-create-error');

			$log->pushHandler(new StreamHandler(__DIR__.'/404-error.log', Logger::ALERT ));
			
			$log->addAlert(json_encode(array_merge((array) $e, $dbVars)));
		}
	}


	protected static function fileUpload ($files, $uploadPaths) {

		$ncu = "\Get\GetController::nameCleanUp";

		$fileNames = array_map($ncu, array_column($files, 'name'));

		$tmps = array_map($ncu, array_column($files, 'tmp_name'));
		
		foreach ($uploadPaths as $key => $path)

			move_uploaded_file($tmps[$key], $path . $fileNames[$key]);

		return 1;
	}

	public static function updateViews (PDO $conn, string $name) {
		
		$currViews = json_decode(GetController::getContents($conn, date('Y_m_d')), true)['nav_indicator'];

		$conn->prepare('UPDATE page SET nav_indicator = ? WHERE name = ?')->execute([intval($currViews)+1, $name]);
	}


	// remove nodes in the data to insert absent from the query
	private static function trimDataToInsert(string $query, $dataSet) {

		$obj = array_filter(array_keys($dataSet), function ($key) use ($query) {

			return strpos($query, $key) !== false;
		});

		// reconstruct repaired ds
		$obj = array_map(function ($key) use ($dataSet) {

			return [$key => $dataSet[$key]]; // should've used `reduce` to avoid this map
		}, $obj);

		// reduce to 1d array
		return array_reduce($obj, function ($a,$b) {
			return array_merge($a,$b);
		},[]);
	}
}

?>
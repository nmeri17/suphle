<?php

	require_once '../auth/conn.php';

	require_once 'autoload.php';

	use \Get\TilwaGet;

	use \Post\TilwaPost;
	

	$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

	
	$value = $_GET['url'];

//var_dump($value); die();
	if ($_SERVER['REQUEST_METHOD'] == 'GET') {

		$key = @$_GET['action']; $handler = TilwaGet::nameDirty($value, 'camel-case');


		if (!empty($key)) {

			if (method_exists('\Get\TilwaGet', $handler)) {

				// get computable values i.e. for non-existent directories
				echo TilwaGet::$handler($conn, $key); // other keys sent along in this request arent discarded
			}
			else {

				// requests here look like directory/xyz. `directory` will be auto detected inside the method. should there be need for more "directories", set their handler as `action` in GET so they land in the previous if block

				echo TilwaGet::pairVarToFields($conn, $key);
			}
		}

		else {
/*$method = new \Electrum\Request\Method\Version();

try {
    $response = $method->execute();
} catch(\Exception $exception) {
    die($exception->getMessage());
}

$response->getVersion();*/
			// get single pages
			$query = $_GET;

			unset($query['url']);

			$_GET = ['url' =>$value, 'query' => http_build_query($query)];

			echo TilwaGet::pairVarToFields($conn, $value);
		}
	}

	else {

		$method = TilwaGet::nameDirty($_GET['url'], 'camel-case');

		if (method_exists('\Post\TilwaPost', $method)) echo TilwaPost::$method($conn, $_POST);

		else {
			http_response_code(404);

			echo header('Location: '.$_SERVER['REQUEST_URI']);
		}

		if (!empty($_FILES)) TilwaPost::fileUpload( ); // only upload if post action is complete
	}
?>
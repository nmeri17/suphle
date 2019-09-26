<?php

	namespace Controllers;

	// require_once 'autoload.php';

	require_once 'bootstrap.php';

	require_once '../auth/conn.php';	

	
	class FrontController {

		private $getCtrlInstc;

		private $value;

		private $handler;

		public $response;
		
		function __construct() {

			$this->getCtrlInstc = new GetController($conn);

			$this->value = $_GET['url'];

			$this->handlerMethod = $this->getCtrlInstc->nameDirty($this->value, 'camel-case');


			if ($_SERVER['REQUEST_METHOD'] == 'GET') $this->response = $this->getHandler();

			else $this->response = $this->postHandler();
		}

		function getHandler () {

			$key = @$_GET['action'];


			if (!empty($key)) {

				if (method_exists( 'GetController', $this->handlerMethod )) {

					// get computable values i.e. for non-existent directories
					return $this->getCtrlInstc->{$this->handlerMethod}( $key); // other keys sent along in this request arent discarded
				}
				else {

					// requests here look like directory/xyz. `directory` will be auto detected inside the method. should there be need for more "directories", set their handler as `action` in GET so they land in the previous if block

					return $this->getCtrlInstc->pairVarToFields( $key);
				}
			}

			else {

				// get single pages
				$query = $_GET;

				unset($query['url']);

				$_GET = ['url' =>$this->value, 'query' => http_build_query($query)];

				return $this->getCtrlInstc->pairVarToFields( $this->value);
			}
		}

		// change the class names here
		function postHandler () {

			if (method_exists('TilwaPost', $this->handlerMethod)) return TilwaPost::{$this->handlerMethod}( $_POST);

			else {
				http_response_code(404);

				echo header('Location: '.$_SERVER['REQUEST_URI']);
			}

			if (!empty($_FILES)) TilwaPost::fileUpload( ); // only upload if post action is complete
		}
	}

	$req = new FrontController;

	echo $req->response;

?>
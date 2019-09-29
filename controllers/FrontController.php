<?php

	namespace Controllers;

	// require_once 'autoload.php';

	
	class FrontController {

		private $getCtrl;

		private $value;

		private $handler;

		public $response;

		public $routeRegister;
		
		function __construct() {

			$bts = new Bootstrap;

			$app = $bts->appVariables();


			$this->getCtrl = new GetController( $app );

			$this->value = $_GET['url'];

			$this->handlerMethod = $this->getCtrl->nameDirty($this->value, 'camel-case');

			$bts->assignUser( $this->getCtrl );


			if ($_SERVER['REQUEST_METHOD'] == 'GET') $this->response = $this->getHandler(); // CHECK ROUTE REGISTER FOR THE PRESENCE OF REQUEST PATH AND IF MIDDLEWARED, HANDLE IT AND PASS APP VARS

			else $this->response = $this->postHandler();
		}

		private function getHandler () {

			$query = $_GET;

			unset($query['url']);

			$_GET = ['url' =>$this->value, 'query' => http_build_query($query)];

			return $this->getCtrl->pairVarToFields( $this->value);
		}

		// change the class names here
		private function postHandler () {

			if (method_exists('TilwaPost', $this->handlerMethod)) return TilwaPost::{$this->handlerMethod}( $_POST);

			else {
				http_response_code(404);

				echo header('Location: '.$_SERVER['REQUEST_URI']);
			}

			if (!empty($_FILES)) TilwaPost::fileUpload( ); // only upload if post action is complete
		}

		public function setRouteRegister () {}
	}

	$req = new FrontController;

	echo $req->response;

?>
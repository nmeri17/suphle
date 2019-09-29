<?php	
	
	use Dotenv\Dotenv;
	
	class Bootstrap {

		private $container;

		function __construct ( ) {

			$this->setStaticVars();
			
			$this->setConnection();
		}

		public function appVariables ( ) {
			
			return $container;
		}

		public function setConnection ( ) {

			$dotenv = Dotenv::create( $this->container['rootPath'] );

			$dotenv->load();

			try {

				$conn = new PDO("mysql:host=localhost;dbname=". getenv('DBNAME') . ";charset=utf8", getenv('DBUSER'), getenv('DBPASS'), array(PDO::ATTR_PERSISTENT => true));

				$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

				$conn->setAttribute( PDO::ATTR_EMULATE_PREPARES, false); // to retain int data type

				$this->container['connection'] = $conn; 
			}
			catch (PDOException $e) {

				var_dump("unable to connect to mysql server", $e->getMessage());
			}
		}

		public function setStaticVars ( ) {

			$this->container = [

				'rootPath' => dirname(__DIR__, 1) . DIRECTORY_SEPARATOR, // up one folder

				'sourceCache' => [] // instead of finding every time?
			];
		}

		// this runs on every request
		public function assignUser (GetController $ctrl ) {

			session_start();

			$sess = $_SESSION;

			if (empty($sess)) $user = null;

			else {

				$uColumn = $ctrl->getContentOptions()['primaryColumns']['user'];

				$user = $ctrl->getContents($sess[$uColumn], 'user');
			}

			$this->container['user'] = $user;
		}
	}

?>
<?php

	namespace Tilwa\App;
	
	use Dotenv\Dotenv;

	class ModuleToRoute {

		private $modules;

		private $eventConsumers;

		public function __construct(array $modules, array $eventConsumers) {

			$this->modules = $modules;

			$this->eventConsumers = $eventConsumers;
		}
		
		public function findContext():ModuleInitializer {

			$requestQuery = $_GET['tilwa_request'];
			
			foreach($this->modules as $module) {

				$routeMatcher = (new ModuleInitializer($module, $requestQuery))->assignRoute();
				
				if ($routeMatcher->foundRoute) return $routeMatcher;
			}
		}

		public function environmentDefaults():self {

			return $this->loadEnv()->initSession();

			// ->configMail() // we only wanna run this if it's not set already and if dev wanna send mails. so, a mail adapter?
		}

		protected function loadEnv () {		

			$dotenv = Dotenv::createImmutable( $this->getRootPath() );

			$dotenv->load();

			return $this;
		}

		protected function configMail ():self {

			ini_set("SMTP", getenv('MAIL_SMTP'));

			ini_set("smtp_port", getenv('MAIL_PORT'));

			ini_set('sendmail_from', getenv('MAIL_SENDER'));

			return $this;
		}

		private function initSession ():self {

			if (session_status() == PHP_SESSION_NONE /*&& !headers_sent()*/)

				session_start(); //session_destroy(); $_SESSION = [];

			return $this;
		}

		// note: we don't wanna listen to events from only the active module since one of its listeners can equally fire another event
		public function watchEvents() {
			// filter modules matching contents of $eventConsumers + active module
			foreach($this->modules as $module) {

				foreach($this->modules as $module) { // ensure currently evaluated doesn't match active module
					// call everybody's registerListeners then pull their externals
				}
			}
		}
	}
?>
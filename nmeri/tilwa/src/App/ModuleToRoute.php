<?php

	namespace Tilwa\App;
	
	use Dotenv\Dotenv;

	class ModuleToRoute {
		
		function findContext(array $modules):FrontController {
			
			$context = null;

			foreach($modules as $module) {

				$routeMatcher = (new FrontController($module))->assignRoute();
				
				if ($routeMatcher->foundRoute) {

					$context = $routeMatcher;

					break;
				}
			}
			return $context;
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
	}
?>
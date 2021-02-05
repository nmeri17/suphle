<?php

	namespace Tilwa\App;
	
	use Dotenv\Dotenv;

	use Models\User;

	use Tilwa\Http\Request\RouteGuards;

	abstract class ParentModule {

		protected $container;

		public function activate () {

			$this->loadEnv()->initSession() // these should be done once (in the front controller?)

			->provideSelf()->entityBindings()->bindEvents();

			// ->configMail() // we only wanna run this if it's not set already and if dev wanna send mails. so, a mail adapter?
		}

		// should be listed in descending order of the versions
		public function apiStack ():array;

		public function browserEntryRoute ():string;

		public function getRootPath ():string;

		// will supply the active module to every client requesting this base type
		public function provideSelf ():self;

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

		public function setDependsOn(array $bindings):self {
			
			# check if key interface matches the `exports` of incoming type before pairing
		}

		// @return interfaces[] from `Interactions` namespace
		public function getDependsOn():array {

			return [];
		}

		public function exports():string {

			return; // an interface from Interactions namespace for `setDependsOn` on sister modules to consume
		}

		public function getUserModel():string {

			return User::class;
		}

		public function apiPrefix():string {

			return "api";
		}

		public function getViewPath ():string {

			return $this->getRootPath() . 'views'. DIRECTORY_SEPARATOR;
		}

		# class containing route guard rules
		public function routePermissions():string {
			
			return RouteGuards::class;
		}

		// provision your classes here
		public function entityBindings ():self {

			//
		}

		// attach event listeners here
		public function bindEvents ():self {

			// may need to work with an event manager
		}

		protected function on () {

			//
		}
	}

?>
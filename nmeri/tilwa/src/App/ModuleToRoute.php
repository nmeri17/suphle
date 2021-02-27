<?php

	namespace Tilwa\App;
	
	use Dotenv\Dotenv;

	use Tilwa\Events\EventManager;

	class ModuleToRoute {

		private $modules;

		private $eventConsumers;

		private $activeInitializer;

		public function __construct(array $modules, array $eventConsumers = []) {

			$this->modules = $modules;

			$this->eventConsumers = $eventConsumers;
		}
		
		public function findContext():ModuleInitializer {

			$requestQuery = $_GET['tilwa_request'];
			
			foreach($this->modules as $module) {

				$routeMatcher = (new ModuleInitializer($module, $requestQuery))->assignRoute();
				
				if ($routeMatcher->foundRoute) {

					$this->activeInitializer = $routeMatcher;

					return $routeMatcher;
				}
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

		// setup watchers for the active module and any related modules
		public function watchEvents() {
			
			$activeModule = $this->activeInitializer->getModule();
			
			$subscribers = $this->getModuleSubscribers($activeModule);

			$eventManager = $this->getEventManager($activeModule, $subscribers);
		}

		public function getEventManager(ParentModule $module, array $subscriptions) {

			$eventManager = new EventManager($module, $subscriptions);

			$eventManager->registerListeners();

			$module->container->whenTypeAny()->needsAny([

				EventManager::class => $eventManager
			]);
			return $eventManager;
		}

		public function getModuleSubscribers(ParentModule $emitableModule, ParentModule $circularDebounce) { //A
			
			$subscriptions = [];

			foreach($this->getCanSubscribe([$emitableModule, $circularDebounce]) as $module) { // gets [B]
				
				$subscribers = $this->getModuleSubscribers($module, $emitableModule); // skips A's listeners if A is listening to events on B

				$eventManager = $this->getEventManager($module, $subscribers);

				$hasListeners = $eventManager->getExternalHandlers($emitableModule->exportsImplements()); // pass the dependency interface, not the module itself

				if ($hasListeners)

				 	$subscriptions[] = $hasListeners;
			}
			return $subscriptions;
		}

		// by default, only listeners within the active module (module with the route hit) will be triggered. Or those in `eventConsumers`. This method ensures that dynamic list (cuz of the recursion) is possible
		public function getCanSubscribe(array $reject):array {
			
			return array_filter($this->modules, function ($module) use ($reject) {

				return !in_array($listener, $reject, true) &&
					
				in_array($module, $this->eventConsumers, true);
			});
		}
	}
?>
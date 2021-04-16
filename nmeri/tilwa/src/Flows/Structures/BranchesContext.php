<?php

	namespace Tilwa\Flows\Structures;

	use Tilwa\Http\Response\Format\AbstractRenderer;

	use Tilwa\Routing\RouteManager;

	class BranchesContext {

		private $modules;

		private $user;

		private $renderer;

		private $router;

		/**
		* @param {user} whether a sub-flow or transition from organic flow, all flow queueing is triggered by a user request. This argument is that user
		* @param {router} set during organic requests (without flows), when access to the modules has been lost
		*/
		function __construct( array $modules, object $user, AbstractRenderer $renderer, RouteManager $router) {

			$this->modules = $modules;

			$this->renderer = $renderer;

			$this->user = $user;

			$this->router = $router;
		}

		public function getModules():array {
			
			return $this->modules;
		}

		public function getRenderer():AbstractRenderer {
			
			return $this->renderer;
		}

		public function getUserId() {
			
			return $this->user ? strval($this->user->id) ? "*";;
		}

		public function getRouter():RouteManager {
			
			return $this->router;
		}
	}
?>
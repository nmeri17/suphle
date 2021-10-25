<?php

	namespace Tilwa\Flows\Structures;

	use Tilwa\Response\{Format\AbstractRenderer, ResponseManager};

	use Tilwa\Contracts\Auth\User;

	class BranchesContext {

		private $modules, $user, $renderer, $responseManager;

		/**
		* @param {user} whether a sub-flow or transition from organic flow, all flow queueing is triggered by a user request. This argument is that user
		* @param {responseManager} set during organic requests (without flows), when access to the modules has been lost
		*/
		function __construct( array $modules, User $user, AbstractRenderer $renderer, ResponseManager $responseManager) {

			$this->modules = $modules;

			$this->renderer = $renderer;

			$this->user = $user;

			$this->responseManager = $responseManager;
		}

		public function getModules():array {
			
			return $this->modules;
		}

		public function getRenderer():AbstractRenderer {
			
			return $this->renderer;
		}

		public function getUserId() {
			
			return $this->user ? strval($this->user->getId()) : "*";
		}

		public function getResponseManager():ResponseManager {
			
			return $this->responseManager;
		}
	}
?>
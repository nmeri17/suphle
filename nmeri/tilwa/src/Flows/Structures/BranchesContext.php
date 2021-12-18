<?php
	namespace Tilwa\Flows\Structures;

	use Tilwa\Response\{Format\AbstractRenderer, ResponseManager};

	use Tilwa\Contracts\Auth\User;

	class BranchesContext {

		private $modules, $user, $renderer, $responseManager;

		/**
		* @param {user} whether a sub-flow or transition from organic flow, all flow queueing is triggered by a user request. This argument is that user
		* 
		* @param {responseManager} set during organic requests (i.e. current request is an organic that push a flow), because for those, access to the modules has been lost
		* 
		* @param {modules} set during flow-to-flow requests i.e. current request is a flow rebound by an earlier handled flow, and an earlier organic before it. This means we can't have both this parameter and [responseManager] set at the same time
		*/
		function __construct(AbstractRenderer $renderer, ?User $user, ?array $modules, ResponseManager $responseManager = null) {

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

		public function getUserId():string {
			
			return $this->user ? strval($this->user->getId()) : "*";
		}

		public function getResponseManager():ResponseManager {
			
			return $this->responseManager;
		}
	}
?>
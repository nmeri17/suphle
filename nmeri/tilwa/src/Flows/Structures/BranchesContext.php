<?php
	namespace Tilwa\Flows\Structures;

	use Tilwa\Response\RoutedRendererManager;

	use Tilwa\Contracts\{Auth\UserContract, Presentation\BaseRenderer};

	class BranchesContext {

		private $modules, $user, $renderer, $rendererManager;

		/**
		* @param {user} whether a sub-flow or transition from organic flow, all flow queueing is triggered by a user request. This argument is that user
		* 
		* @param {rendererManager} set during organic requests (i.e. current request is an organic that push a flow), because for those, access to the modules has been lost
		* 
		* @param {modules} set during flow-to-flow requests i.e. current request is a flow rebound by an earlier handled flow, and an earlier organic before it. This means we can't have both this parameter and [rendererManager] set at the same time
		*/
		function __construct(BaseRenderer $renderer, ?UserContract $user, ?array $modules, RoutedRendererManager $rendererManager = null) {

			$this->modules = $modules;

			$this->renderer = $renderer;

			$this->user = $user;

			$this->rendererManager = $rendererManager;
		}

		public function getModules():?array {
			
			return $this->modules;
		}

		public function getRenderer():BaseRenderer {
			
			return $this->renderer;
		}

		public function getUserId():string {
			
			return $this->user ? strval($this->user->getId()) : "*";
		}

		public function getRoutedRendererManager():?RoutedRendererManager {
			
			return $this->rendererManager;
		}
	}
?>
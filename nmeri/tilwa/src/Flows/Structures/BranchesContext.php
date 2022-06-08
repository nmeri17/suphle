<?php
	namespace Tilwa\Flows\Structures;

	use Tilwa\Contracts\{Auth\UserContract, Presentation\BaseRenderer};

	class BranchesContext {

		private $modules, $user, $renderer, $rendererManager;

		/**
		* @param {user} whether a sub-flow or transition from organic flow, all flow queueing is triggered by a user request. This argument is that user
		*/
		public function __construct(BaseRenderer $renderer, ?UserContract $user = null) {

			$this->renderer = $renderer;

			$this->user = $user;
		}

		public function getRenderer ():BaseRenderer {
			
			return $this->renderer;
		}

		public function getUserId ():string {
			
			return $this->user ? strval($this->user->getId()) : "*";
		}
	}
?>
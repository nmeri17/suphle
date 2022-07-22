<?php
	namespace Suphle\Flows\Structures;

	use Suphle\Contracts\{Auth\UserContract, Presentation\BaseRenderer};

	/**
	 * This is the what is received from the currently handled request. It is stored and during handling later, specifics of the flow are extracted and handled
	*/
	class PendingFlowDetails {

		private $user, $renderer;

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
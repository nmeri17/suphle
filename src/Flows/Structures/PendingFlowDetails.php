<?php
	namespace Suphle\Flows\Structures;

	use Suphle\Contracts\{Auth\AuthStorage, Presentation\BaseRenderer};

	use Suphle\Flows\OuterFlowWrapper;

	/**
	 * This is what is received from the currently handled request. It is stored and during handling later, specifics of the flow are extracted and handled
	*/
	class PendingFlowDetails {

		private $authStorage, $renderer;

		public function __construct(BaseRenderer $renderer, AuthStorage $authStorage) {

			$this->renderer = $renderer;

			$this->authStorage = $authStorage;
		}

		public function getRenderer ():BaseRenderer {
			
			return $this->renderer;
		}

		/**
		* Whether a sub-flow or transition from organic flow, all flow queueing is triggered by a user request. This argument is that user
		*/
		public function getUserId ():string {

			$user = $this->authStorage->getUser();
			
			return !is_null($user) ? strval($user->getId()) :

			OuterFlowWrapper::ALL_USERS;
		}

		public function getAuthStorage ():string {

			return get_class($this->authStorage);
		}
	}
?>
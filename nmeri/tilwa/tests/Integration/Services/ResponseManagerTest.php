<?php
	namespace Tilwa\Tests\Integration\Controllers;

	use Tilwa\Testing\IsolatedComponentTest;

	/* I think these should be unit tests sha. Call the high level methods here */
	class ResponseManagerTest extends IsolatedComponentTest {

		// the next 3 methods all trigger [bootCoodinatorManager]
		// one of these guys has to make a POST request to confirm underlying controller request validator is called and behaves correctly. [ValidatorController] is already created
		public function test_validateController () {

			// confirm it throws those errors when unsatisfactory
		}

		public function test_handleValidRequest () {

			// confirm action method's contents are returned
		}

		public function test_isValidRequest () {

			// confirm can find its way to its validator and returns true and false where applicable
		}

		public function test_failed_request_validation_reverts_renderer () {
			
			// 
		}

		public function afterRender():void {

			if ($this->renderer->hasBranches())// the very first request won't be caught in a flow. so, delegate queueing branches

				$this->flowQueuer->insert($this->renderer, $this);
		}

		public function bootCoodinatorManager ():self {

			$this->controllerManager->setController(

				$this->container->getClass($this->renderer->getController())
			);

			$this->controllerManager->bootController($this->renderer->getHandler());

			return $this;
		}

		public function handleValidRequest(RequestDetails $requestDetails):AbstractRenderer {

			$renderer = $this->renderer;

			if (!$requestDetails->isApiRoute())

				$this->router->setPreviousRenderer($renderer);

			if ($renderer instanceof Markup && $this->payloadStorage->acceptsJson())

				$renderer->setWantsJson();

			return $renderer->invokeActionHandler($this->controllerManager->getHandlerParameters());
		}

		public function isValidRequest ():bool {

			return $this->controllerManager->isValidatedRequest();
		}

		public function mayBeInvalid ():void {

			if (!$this->isValidRequest())

				throw new ValidationFailure($this->controllerManager);
		}

		public function requestAuthenticationStatus (AuthStorage $storage):bool {

			$storage->resumeSession();

			return !is_null($storage->getUser()); // confirms there's an active session and that its owner exists on the underlying database
		}
	}
?>
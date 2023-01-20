<?php
	namespace Suphle\Contracts\Requests;

	use Suphle\Services\ServiceCoordinator;

	use Suphle\Exception\Explosives\Generic\NoCompatibleValidator;

	interface CoodinatorManager extends ValidationEvaluator {

		public function setDependencies (ServiceCoordinator $controller, string $actionMethod):self;

		public function getHandlerParameters ():array;

		public function hasValidatorErrors ():bool;

		/**
		 * @throws NoCompatibleValidator
		*/
		public function bootController ():void;
	}
?>
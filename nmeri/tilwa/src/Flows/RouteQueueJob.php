<?php

	namespace Tilwa\Flows;

	class RouteQueueJob implements Job {

		private $contentOwner;

		private $previousResponse;

		private $rendererType;

		private $flow;
		
		/**
		 * Delete the job if its models no longer exist.
		 *
		 * @var bool
		 */
		public $deleteWhenMissingModels = true;

		function __construct(string $contentOwner, $previousResponse, string $rendererType, ControllerFlows $flow) { // i think these are passed in on ->push/dispatch
			
			$this->contentOwner = $contentOwner;

			$this->previousResponse = $previousResponse;

			$this->rendererType = $rendererType;

			$this->flow = $flow;
		}

		// what container is used to hydrate these? laravel
		public function handle(FlowHydrator $ypeHint) {
			
			# when this eventually runs, read the flow object, call the appropriate triggers depending on action specified on it
			// continue here. need to swap out this queue stuff for one that doesn't rely on their container
			// cache lookup when updates come: tag by model type
			//  ==> check [user id] umbrella. no match? check route patterns under [all] umbrella for incoming url pattern and fail if no match
			// 
		}
	}
?>
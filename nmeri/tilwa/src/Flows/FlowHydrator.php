<?php namespace Tilwa\Flows;

	class FlowHydrator {

		private $context;

		private $computedNodes;

		public function setContext(FlowContext $context):self {

			$this->context = $context;
		}

		public function store():self {
			# store them according to the format being read by the outer flow wrapper?
		}

		public function runNodes():self {

			//call the appropriate triggers depending on action specified on it
			// cache lookup when updates come: tag by model type
			//  ==> check [user id] umbrella. no match? check route patterns under [all] umbrella for incoming url pattern and fail if no match
			// use something like $branches = flow->branches->each(select action handler)

			# store them according to the format being read by the outer flow wrapper?
		}
	}
?>
<?php
	namespace Suphle\Flows;

	use Suphle\Flows\Previous\{ResponseBuilderProxy, SingleNode, CollectionNode, UnitNode};

	class ControllerFlows {

		private array $branches = [];

		/**
		 * The pattern given here can just be a generic url like "sub-path/id". When the task runs, we will attempt to find the collection pattern that tells us what part is a placeholder
		*/
		public function linksTo(string $pattern, UnitNode $responseStructure):self {

			$this->branches[$pattern] = $responseStructure;

			return $this;
		}

		public function previousResponse():ResponseBuilderProxy {

			return new ResponseBuilderProxy();
		}

		public function eachBranch(callable $callback) {
			
			foreach ($this->branches as $path => $branch)
				
				$callback($path, $branch);
		}
	}
?>
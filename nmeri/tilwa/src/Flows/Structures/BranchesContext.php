<?php

	namespace Tilwa\Flows\Structures;

	class BranchesContext {

		private $outgoingPath;

		private $modules;

		function __construct(string $outgoingPath, array $modules) {
			
			$this->outgoingPath = $outgoingPath;

			$this->modules = $modules;
		}

		public function getOutgoingPath():string {
			
			return $this->outgoingPath;
		}

		public function getModules():array {
			
			return $this->modules;
		}
	}
?>
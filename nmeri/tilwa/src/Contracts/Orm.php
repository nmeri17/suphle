<?php

	namespace Tilwa\Contracts;

	interface Orm {

		private function setConnection():self;

		private function getConnection ();

		// @param {callback} action to perform once the query being sent is intercepted A. Should be passed to the underlying parameter catcher who will supply the prepared parameters being sent B. Accepts another closure C that receives B as argument in order to execute A
		public function setTrap(callable $callback);

		// @return "next_page_url"
		// this should go into its config
		public function getPaginationPath():string;

		public function runTransaction(callable $queries):void;

		public function registerObservers();
	}
?>
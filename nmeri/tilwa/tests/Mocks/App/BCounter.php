<?php
	namespace Tilwa\Tests\Mocks\App;

	class BCounter {

		private $count = 0;

		public function getCount ():int {

			return $this->count;
		}

		public function setCount (int $newCount):void {

			$this->count = $newCount;
		} 
	}
?>
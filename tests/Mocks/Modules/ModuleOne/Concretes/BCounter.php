<?php
	namespace Suphle\Tests\Mocks\Modules\ModuleOne\Concretes;

	class BCounter {

		protected $count = 0;

		public function getCount ():int {

			return $this->count;
		}

		public function setCount (int $newCount):void {

			$this->count = $newCount;
		} 
	}
?>
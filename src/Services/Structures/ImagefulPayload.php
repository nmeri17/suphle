<?php
	namespace Suphle\Services\Structures;

	use Suphle\Contracts\Requests\FileInputReader;

	use Suphle\IO\Image\OptimizersManager;

	abstract class ImagefulPayload extends ModellessPayload {

		protected $imageOptimizer, $allFiles;

		public function __construct (OptimizersManager $imageOptimizer) {

			$this->imageOptimizer = $imageOptimizer; // default optimizer. can be replaced
		}

		public function dependencyMethods ():array {

			return [

				"setInputReader", "setPayloadStorage" // defined on parent
			];
		}

		public function setInputReader (FileInputReader $inputReader):void {

			$this->allFiles = $inputReader->getFileObjects();
		}
	}
?>
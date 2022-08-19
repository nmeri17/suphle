<?php
	namespace Suphle\Flows\Structures;

	use Suphle\Contracts\Presentation\BaseRenderer;

	class GeneratedUrlExecution {

		private $requestPath, $renderer;

		public function __construct (string $requestPath, BaseRenderer $renderer) {

			$this->requestPath = $requestPath;

			$this->renderer = $renderer;
		}

		public function changeUrl (string $newPath):void {

			$this->requestPath = $newPath;
		}

		public function getRenderer ():BaseRenderer {

			return $this->renderer;
		}

		public function getRequestPath ():string {

			return $this->requestPath;
		}
	}
?>
<?php
	namespace Suphle\Flows\Structures;

	use Suphle\Contracts\Presentation\BaseRenderer;

	class GeneratedUrlExecution {

		public function __construct(private string $requestPath, private readonly BaseRenderer $renderer)
  {
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
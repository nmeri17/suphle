<?php
	namespace Tilwa\Controllers\Proxies;

	use Tilwa\Controllers\Decorators\ServiceErrorCatcher;

	class ErrorCloakBuilder {

		private $originalTarget, $targetName, $serviceCallProxy;

		public function __construct ( UpdatelessCallProxy $serviceCallProxy) {

			$this->serviceCallProxy = $serviceCallProxy;
		}

		public function setTarget (ServiceErrorCatcher $originalTarget):void {

			$this->originalTarget = $originalTarget;

			$this->targetName = get_class($originalTarget);

			$this->serviceCallProxy->setConcrete($originalTarget);
		}

		public function buildClass () {

			$dynamicName = $this->targetName . "Proxy";

			$types = [
				"NewName" => $dynamicName,

				"OldName" => $this->targetName,

				"Methods" => $this->wrapClassMethods(),

				"CatcherType" => BaseCallProxy::class
			];

			$genericContents = file_get_contents("../Templates/ErrorTemplate.php");

		    foreach ($types as $placeholder => $type)

		        $genericContents = str_replace("<$placeholder>", $type, $genericContents);

			eval($genericContents);

			return new $dynamicName($this->serviceCallProxy, $this->originalTarget);
		}

		private function wrapClassMethods ():string {

			$methods = "";

			foreach (get_class_methods($this->targetName) as $method)

				if ($method != "__construct")

					$methods .= "public function $method () {" . '

						return $this->errorCatcher->artificial__call(__FUNCTION__, func_get_args());
					}';

			return $methods;
		}
	}
?>
<?php
	namespace Tilwa\Services\Proxies;

	abstract class BaseCloakBuilder {

		private $originalTarget, $targetName;

		protected $serviceCallProxy, // instance of BaseCallProxy to be injected via constructor

		$templatePath = "../Templates/CallInterceptorTemplate.php";

		/**
		 * @param {originalTarget} the concrete decorated to require this cloak
		*/
		public function setTarget ( $originalTarget):void {

			$this->originalTarget = $originalTarget;

			$this->targetName = get_class($originalTarget);

			$this->serviceCallProxy->setConcrete($originalTarget);
		}

		public function buildClass () {

			$dynamicName = $this->targetName . get_called_class(); // to avoid clashes with other cloaks for decorated entity

			$genericContents = file_get_contents($this->templatePath);

		    foreach ($this->templateTypes() as $placeholder => $type)

		        $genericContents = str_replace("<$placeholder>", $type, $genericContents);

			eval($genericContents);

			return new $dynamicName($this->serviceCallProxy, $this->originalTarget);
		}

		protected function wrapClassMethods ():string {

			$methods = "";

			foreach (get_class_methods($this->targetName) as $method)

				if ($method != "__construct") // since we've overwritten the original constructor

					$methods .= "public function $method () {" . '

						return $this->catcherType->artificial__call(__FUNCTION__, func_get_args());
					}';

			return $methods;
		}

		protected function templateTypes ():array {

			return [
				"NewName" => $dynamicName,

				"OldName" => $this->targetName,

				"Methods" => $this->wrapClassMethods(),

				"CatcherType" => get_class($this->serviceCallProxy)
			];
		}
	}
?>
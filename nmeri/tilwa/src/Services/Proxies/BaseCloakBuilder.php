<?php
	namespace Tilwa\Services\Proxies;

	use Tilwa\Hydration\Container;

	abstract class BaseCloakBuilder {

		private $originalTarget, $targetName;

		protected $serviceCallProxy, // instance of BaseCallProxy to be injected via constructor

		$systemReader, $objectMeta;

		/**
		 * @param {originalTarget} the concrete decorated to require this cloak
		*/
		public function setTarget ( $originalTarget):void {

			$this->originalTarget = $originalTarget;

			$this->targetName = get_class($originalTarget);

			$this->serviceCallProxy->setConcrete($originalTarget);
		}

		public function buildClass () {

			$dynamicName = $this->getNewClassName();

			$genericContents = file_get_contents($this->getTemplatePath());

		    foreach ($this->templateTypes() as $placeholder => $type)

		        $genericContents = str_replace("<$placeholder>", $type, $genericContents);
var_dump($genericContents);
			eval("?>" . $genericContents);

			return new $dynamicName($this->serviceCallProxy, $this->originalTarget);
		}

		protected function getNewClassName ():string {

			return str_replace(
				"\\", "",

				str_shuffle($this->targetName . get_called_class()) // to avoid clashes with other cloaks for decorated entity
			);
		}

		protected function getTemplatePath ():string {

			return $this->systemReader->pathFromLevels(__DIR__,

				"Templates/CallInterceptorTemplate.php", 2
			);
		}

		protected function wrapClassMethods ():string {

			$methods = "";

			foreach (get_class_methods($this->targetName) as $method)

				if ($method == Container::CLASS_CONSTRUCTOR)

					continue; // since we've overwritten the original constructor

				$returnType = $this->objectMeta->methodReturnType($this->targetName, $method);

				if (!is_null($returnType))

					$returnType = ":$returnType";

				else $returnType = "";

				$methods .= "public function $method ()". $returnType .

				" {" . '

					return $this->catcherType->artificial__call(__FUNCTION__, func_get_args());
				}';

			return $methods;
		}

		protected function templateTypes ():array {

			return [
				"NewName" => $this->getNewClassName(),

				"OldName" => $this->targetName,

				"Methods" => $this->wrapClassMethods(),

				"CatcherType" => get_class($this->serviceCallProxy)
			];
		}
	}
?>
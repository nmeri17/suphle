<?php
	namespace Tilwa\Testing\Proxies\Extensions;

	use Tilwa\Middleware\{MiddlewareRegistry, PatternMiddleware};

	class MiddlewareManipulator extends MiddlewareRegistry {

		private $stackAlwaysEmpty = false, $preExclude = [],

		$preInclude = [];

		/**
		 * Whenever router decides on the active pattern, it'll ultimately include middlewares applied here
		 * 
		 * We're using this instead of updating the default middleware list, since the eventual module may have custom config we are unwilling to override with whatever mock we'll set as default
		 * 
		 * @param {middlewares} Middleware class names
		*/
		public function addToActiveStack (array $middlewares):void {

			$this->preInclude = $middlewares;
		}

		public function disableAll ():void {

			$this->stackAlwaysEmpty = true;
		}

		/** 
		 * @param {middlewares} Middleware::class[]
		*/
		public function disable (array $middlewares):void {

			$this->preExclude = $middlewares;
		}

		/**
		 * {@inheritdoc}
		*/
		public function getActiveStack ():array {

			if ($this->stackAlwaysEmpty) return [];

			$realStack = parent::activeStack();

			if (!empty($this->preInclude))

				$realStack[] = $this->includeCollection();

			foreach ($realStack as $holder)

				$this->extractFromHolders($holder, $this->preExclude);

			return $realStack;
		}

		private function includeCollection ():PatternMiddleware {

			$collection = new PatternMiddleware;

			$collection->setList($this->preInclude);

			return $collection;
		}
	}
?>
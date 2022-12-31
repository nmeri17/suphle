<?php
	namespace Suphle\Testing\Proxies\Extensions;

	use Suphle\Middleware\{MiddlewareRegistry, PatternMiddleware};

	class MiddlewareManipulator extends MiddlewareRegistry {

		protected bool $stackAlwaysEmpty = false;

		protected array $preExclude = [], $preInclude = [];

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

			$stack = [];

			$parentStack = parent::getActiveStack();

			if (!empty($this->preInclude))

				$stack[] = $this->includeCollection();

			foreach ($parentStack as $holder)

				$holder->omitWherePresent($this->preExclude);

			return [...$stack, ...$parentStack];
		}

		private function includeCollection ():PatternMiddleware {

			$collection = new PatternMiddleware;

			$collection->setList($this->preInclude);

			return $collection;
		}
	}
?>
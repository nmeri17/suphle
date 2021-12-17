<?php
	namespace Tilwa\Testing\Proxies\Extensions;

	use Tilwa\Middleware\{MiddlewareRegistry, PatternMiddleware};

	class MiddlewareManipulator extends MiddlewareRegistry {

		private $stackAlwaysEmpty = false, $excludeMiddleware = [];

		/**
		 * Whenever router decides on the active pattern, it'll ultimately include middlewares applied here
		 * 
		 * @param {middlewares} Middleware[], not class names
		*/
		public function addToActiveStack (array $middlewares):void {

			$stackHolder = new PatternMiddleware; // we don't care whether another holder already contains any of these middleware since they'll eventually get filtered

			foreach ($middlewares as $instance)

				$stackHolder->addMiddleware($instance);

			$this->activeStack[] = $stackHolder;
		}

		public function disableAll ():void {

			$this->stackAlwaysEmpty = true;
		}

		/** 
		 * @param {middlewares} Middleware[], not class names
		*/
		public function disable (array $middlewares):void {

			$this->excludeMiddleware = $middlewares;
		}

		/**
		 * {@inheritdoc}
		*/
		public function getActiveStack ():array {

			if ($this->stackAlwaysEmpty) return [];

			$realStack = parent::activeStack();

			$this->extractFromHolders($realStack);

			return $realStack;
		}

		/**
		 * @param {holders} PatternMiddleware[]
		*/
		private function extractFromHolders (array $holders):void {

			foreach ($this->excludeMiddleware as $middleware)

				foreach ($holders as $holder)

					$holder->omitWherePresent($middleware);
		}
	}
?>
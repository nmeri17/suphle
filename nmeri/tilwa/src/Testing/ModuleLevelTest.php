<?php

	namespace Tilwa\Testing;

	use Tilwa\App\ModulesBooter;

	use Tilwa\Events\ExecutionUnit;

	use PHPUnit\Framework\TestCase;

	abstract class ModuleLevelTest extends TestCase {

		private $eventManager;

		protected function setUp ():void {

			$booter = new ModulesBooter($this->getModules());

			$this->eventManager = $booter->getEventManager();

			$booter->boot();
		}
		
		abstract protected function getModules():array;

		protected function getModuleFor (string $interface):object {

			foreach ($this->getModules() as $descriptor)

				if ($interface == $descriptor->exportsImplements())

					return $descriptor->exports();
		}

		protected function assertFiredEvent ($emitter, string $eventName):void {

			$subscription = $this->findInBlanks($emitter);

			if (is_null($subscription))

				throw new Exception("Event not fired");
			
			assert(!empty($subscription->getMatchingUnits($eventName)));
		}

		private function findInBlanks ($sender):?EventSubscription {

			foreach ($this->eventManager->getBlanks() as $subscription)

				if ($subscription->matchesHandler($sender))

					return $subscription;
		}

		public function catchEmittingEvents ():void {

			$this->eventManager->makeFireSoft();
		}
	}
?>
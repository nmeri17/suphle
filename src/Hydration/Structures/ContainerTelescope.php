<?php
	namespace Suphle\Hydration\Structures;

	class ContainerTelescope {

		private $consumerList = [], // [class/interface => [consumers]]

		$readArguments = [], // [class => [[parameterName => value]]]

		$readConcretes = [], // same as [readArguments]

		$refreshedEntities = [],

		$consumerParents = [], // [dependent/class => [interface whose concrete was hydrated for its constructor]]

		$writtenArguments = [],

		$writtenConcretes = [],

		$missingConcretes = [],

		$missingArguments = [],

		$missingContexts = [], // [class1, class2, class3]

		$storedConcretes = [],

		$noiseFilter // receives $this and is expected to return true when filter should be removed

		;

		public function setNoiseFilter (callable $callback):self {

			$this->noiseFilter = $callback;

			return $this;
		}

		private function notFilteringNoise ():bool {

			return is_null($this->noiseFilter) ||

			call_user_func_array($this->noiseFilter, [$this]);
		}

		public function safePopulate (array &$context, string $key, $value):void {

			if (!$this->notFilteringNoise()) return;

			if (!array_key_exists($key, $context))

				$context[$key] = [];

			$context[$key][] = $value;
		}

		public function didPopulate (array $context, string $key, $value):bool {

			return array_key_exists($key, $context) &&

			in_array($value, $context[$key]);
		}

		/**
		 * Find all in $context matching $value
		*/
		public function allMatchingValue (array $context, $value):array {

			$matchingKeys = [];

			foreach ($context as $key => $valueList)

				if (in_array( $value, $valueList))
			
					$matchingKeys[] = $key;

			return $matchingKeys;
		}

		public function setConsumerList (array $consumerList):void {

			$this->consumerList = $consumerList;
		}

		public function getConsumerList ():array {

			return $this->consumerList;
		}

		public function hasConsumers (string $dependency):bool {

			return array_key_exists($dependency, $this->consumerList);
		}

		public function getConsumersFor (string $dependency):array {

			if (!$this->hasConsumers($dependency)) return [];

			return $this->consumerList[$dependency];
		}

		public function addReadArguments (string $contentOwner, string $parameterName, $mixedValue ):void {

			$this->safePopulate(
				$this->readArguments, $contentOwner,

				[$parameterName => $mixedValue]
			);
		}

		public function readArgumentWithName (string $contentOwner, string $argumentName):bool {

			return in_array($contentOwner,

				$this->allReadArgument($argumentName)
			);
		}

		/**
		 * @param {nameValue} [parameterName, not the type => $instance]
		*/
		public function readArgumentFor (string $contentOwner, array $nameValue):bool {

			return $this->didPopulate(

				$this->readArguments, $contentOwner, $nameValue
			);
		}

		/**
		 * @return a list of all concretes that while hydrating, read [argumentName] from a provision
		*/
		public function allReadArgument ( $argumentName):array {

			$matchingOwners = [];

			foreach ($this->readArguments as $owner => $valuePairList)

				foreach ($valuePairList as $pair)

					if ( current(array_keys($pair)) == $argumentName) // remember they're stored using argumentName => $value, so numeric indexes won't suffice
				
						$matchingOwners[] = $owner;

			return $matchingOwners;
		}

		public function addReadConcretes (string $contentOwner, string $className ):void {

			$this->safePopulate($this->readConcretes, $contentOwner, $className);
		}

		public function getReadConcretes ():array {

			return $this->readConcretes;
		}

		public function readConcreteFor (string $contentOwner, $className):bool {

			return $this->didPopulate(

				$this->writtenConcretes, $contentOwner, $className
			);
		}

		public function addRefreshedEntities (string $entity):void {

			if (!$this->notFilteringNoise()) return;

			$this->refreshedEntities[] = $entity;
		}

		public function getRefreshedEntities ():array {

			return $this->refreshedEntities;
		}

		public function didRefreshEntity (string $entityName):bool {

			return in_array($entityName, $this->refreshedEntities);
		}

		public function addConsumerParent (string $dependent, string $dependency):void {

			$this->safePopulate($this->consumerParents, $dependent, $dependency);
		}

		public function getConsumerParents ():array {

			return $this->consumerParents;
		}

		public function hasConsumerParent (string $dependent, $dependency):bool {

			return $this->didPopulate(

				$this->consumerParents, $dependent, $dependency
			);
		}

		public function addWrittenArguments ( string $contentOwner, array $argumentList):void {

			foreach ($argumentList as $argumentType)

				$this->safePopulate(
					$this->writtenArguments, $contentOwner,

					$argumentType
				);
		}

		public function getWrittenArguments ():array {

			return $this->writtenArguments;
		}

		public function wroteArgumentFor (string $contentOwner, $argumentName):bool {

			return $this->didPopulate(

				$this->writtenArguments, $contentOwner, $argumentName
			);
		}

		public function addWrittenConcretes ( string $contentOwner, array $dependencyList):void {

			foreach ($dependencyList as $dependency)

				$this->safePopulate($this->writtenConcretes, $contentOwner, $dependency);
		}

		public function getWrittenConcretes ():array {

			return $this->writtenConcretes;
		}

		public function addMissingArgument (string $contentOwner, $argumentName):void {

			$this->safePopulate($this->missingArguments, $contentOwner, $argumentName);
		}

		public function getMissingArguments ():array {

			return $this->missingArguments;
		}

		public function missedArgumentFor (string $contentOwner, $argumentName):bool {

			return $this->didPopulate(

				$this->missingArguments, $contentOwner, $argumentName
			);
		}

		public function allMissedArgument ( $argumentName):array {

			return $this->allMatchingValue(

				$this->missingArguments, $argumentName
			);
		}

		public function addMissingConcrete (string $contentOwner, $argumentName):void {

			$this->safePopulate($this->missingConcretes, $contentOwner, $argumentName);
		}

		public function getMissingConcretes ():array {

			return $this->missingConcretes;
		}

		public function missedConcreteFor (string $contentOwner, $argumentName):bool {

			return $this->didPopulate(

				$this->missingConcretes, $contentOwner, $argumentName
			);
		}

		public function allMissedConcrete ( $argumentName):array {

			return $this->allMatchingValue(

				$this->missingConcretes, $argumentName
			);
		}

		public function addMissingContext (string $contentOwner):void {

			if (!$this->notFilteringNoise()) return;

			$this->missingContexts[] = $contentOwner;
		}

		public function getMissingContexts ():array {

			return $this->missingContexts;
		}

		public function addStoredConcrete(string $contentOwner, string $className ):void {

			$this->safePopulate($this->storedConcretes, $contentOwner, $className);
		}

		public function getStoredConcretes ():array {

			return $this->storedConcretes;
		}

		public function storedConcreteFor (string $contentOwner, $className):bool {

			return $this->didPopulate(

				$this->storedConcretes, $contentOwner, $className
			);
		}
	}
?>
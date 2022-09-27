<?php
	namespace Suphle\Tests\Mocks\Modules\ModuleOne\Coordinators;

	use Suphle\Services\ServiceCoordinator;

	use Suphle\Tests\Mocks\Modules\ModuleOne\{PayloadReaders\ImageServiceConsumer, Concretes\ARequiresBCounter};

	class BaseCoordinator extends ServiceCoordinator {

		public function indexHandler () {

			return ["message" => "Hello World!"];
		}

		public function plainSegment () {

			return ["message" => "plain Segment"];
		}

		public function simplePair () {

			//
		}

		public function hyphenatedSegments () {

			//
		}

		public function underscoredSegments () {

			//
		}

		public function optionalPlaceholder () {

			//
		}

		public function incorrectActionInjection (ImageServiceConsumer $payload, ARequiresBCounter $aRequires):array {

			return [];
		}
	}
?>
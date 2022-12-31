<?php
	namespace Suphle\Flows;

	use Suphle\Flows\Structures\{RouteUserNode, RouteUmbrella, PendingFlowDetails};

	use Suphle\Contracts\{IO\CacheManager, Presentation\BaseRenderer, Config\Flows};

	use Suphle\Hydration\Structures\ObjectDetails;

	class UmbrellaSaver {

		final const FLOW_PREFIX = "_suphle_flow";

		public function __construct (
			protected readonly Flows $flowConfig,

			protected readonly CacheManager $cacheManager,

			protected readonly ObjectDetails $objectMeta
		) {}

		public function getPatternLocation (string $urlPattern):string {

			return self::FLOW_PREFIX . "/" . trim($urlPattern, "/");
		}

		public function saveNewUmbrella (string $urlPattern, RouteUserNode $nodeContent, PendingFlowDetails $originatingFlowDetails):void {

			$location = $this->getPatternLocation($urlPattern);
			
			$existing = $this->getExistingUmbrella($location);

			if (is_null($existing)) {

				$existing = new RouteUmbrella($location, $this->objectMeta);

				$existing->setAuthMechanism(

					$originatingFlowDetails->getAuthStorage()
				);
			}

			$existing->addUser(

				$originatingFlowDetails->getStoredUserId(), $nodeContent
			);

			$saved = $this->cacheManager->saveItem($location, $existing);

			$contentType = $this->getContentType($nodeContent->getRenderer());

			if ($contentType)

				$this->cacheManager->tagItem($contentType, $existing);

			// or, $location can subscribe to a topic(instead of using tags?). update listener publishes to that topic (so we never have outdated content)
		}

		/**
		 * @return model type, where present
		*/
		private function getContentType (BaseRenderer $renderer):?string {

			$contentTypes = $this->flowConfig->contentTypeIdentifier();

			$payload = $renderer->getRawResponse();

			$payloadType = $this->objectMeta->getValueType($payload);

			if (array_key_exists($payloadType, $contentTypes))

				return call_user_func([$payload, $contentTypes[$payloadType]]);

			return null;
		}

		public function getExistingUmbrella (string $urlPattern):?RouteUmbrella {

			return $this->cacheManager->getItem($urlPattern); // or combine [tag] with the [get]
		}

		public function updateUmbrella (string $originalPattern, RouteUmbrella $existing):void {

			$prefixed = $this->getPatternLocation($originalPattern);

			$this->cacheManager->saveItem($prefixed, $existing); // override whatever was there
		}
	}
?>
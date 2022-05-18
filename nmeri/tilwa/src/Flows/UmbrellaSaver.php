<?php
	namespace Tilwa\Flows;

	use Tilwa\Flows\Structures\{RouteUserNode, RouteUmbrella};

	use Tilwa\Contracts\{IO\CacheManager, Presentation\BaseRenderer, Config\Flows};

	class UmbrellaSaver {

		const FLOW_PREFIX = "tilwa_flow";

		private $cacheManager, $flowConfig;

		public function __construct (Flows $flowConfig, CacheManager $cacheManager) {

			$this->flowConfig = $flowConfig;

			$this->cacheManager = $cacheManager;
		}

		public function getPatternLocation (string $urlPattern) {

			return self::FLOW_PREFIX . "/" . trim($urlPattern, "/");
		}

		public function saveNewUmbrella (string $urlPattern, RouteUserNode $nodeContent, string $userId):void {

			$location = $this->getPatternLocation($urlPattern);
			
			$existing = $this->getExistingUmbrella($location);

			if (!$existing) $existing = new RouteUmbrella($location);

			$existing->addUser($userId, $nodeContent);

			$saved = $this->cacheManager->saveItem($location, $existing);

			$contentType = $this->getContentType($nodeContent->getRenderer());

			if ($contentType)

				$cacheManager->tagItem($contentType, $existing);

			// or, $location can subscribe to a topic(instead of using tags?). update listener publishes to that topic (so we never have outdated content)
		}

		/**
		 * @return model type, where present
		*/
		private function getContentType (BaseRenderer $renderer):?string {

			$contentTypes = $this->flowConfig->contentTypeIdentifier();

			$payload = $renderer->getRawResponse();

			$payloadType = gettype($payload);

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
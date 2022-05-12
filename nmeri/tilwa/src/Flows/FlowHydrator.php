<?php
	namespace Tilwa\Flows;

	use Tilwa\Flows\Structures\{RouteUserNode, RouteUmbrella, RangeContext, ServiceContext, GeneratedUrlExecution};

	use Tilwa\Flows\Previous\{ SingleNode, CollectionNode, UnitNode};

	use Tilwa\Contracts\{IO\CacheManager, Presentation\BaseRenderer, Config\Flows};

	use Tilwa\Response\RoutedRendererManager;

	use Tilwa\Hydration\Container;

	use Tilwa\Request\PayloadStorage;

	use Tilwa\Routing\PathPlaceholders;

	use Illuminate\Support\Arr;

	use Exception;

	class FlowHydrator {

		private $previousResponse, $cacheManager, $payloadStorage,

		$rendererManager, $container, $placeholderStorage,

		$flowConfig, $baseUrlPattern,

		$parentHandlers = [
			SingleNode::class => "handleSingleNodes",

			CollectionNode::class => "handleCollectionNodes"
		],
			
		$collectionSubHandlers = [

			CollectionNode::PIPE_TO => "handlePipe",

			CollectionNode::IN_RANGE => "handleRange",

			CollectionNode::DATE_RANGE => "handleDateRange",

			CollectionNode::ONE_OF => "handleOneOf",

			CollectionNode::FROM_SERVICE =>	"handleServiceSource"
		],
			
		$singleSubHandlers = [

			SingleNode::ALTERS_QUERY_SEGMENT => "handleQuerySegmentAlter"
		],

		$configHandlers = [
			
			UnitNode::TTL => "setExpiresAtHydrator",

			UnitNode::MAX_HITS => "setMaxHitsHydrator"
		];

		public function __construct (

			CacheManager $cacheManager, Container $randomContainer,

			PathPlaceholders $placeholderStorage, PayloadStorage $payloadStorage, 

			Flows $flowConfig
		) {

			$this->cacheManager = $cacheManager;

			$this->container = $randomContainer;

			$this->placeholderStorage = $placeholderStorage;

			$this->payloadStorage = $payloadStorage;

			$this->flowConfig = $flowConfig;
		}

		# @param {contentType} model type, where present
		private function storeContext(string $urlPattern, RouteUserNode $nodeContent, string $userId, ?string $contentType):void {

			$cacheManager = $this->cacheManager;

			$prefixed = OuterFlowWrapper::FLOW_PREFIX . "/" . trim($urlPattern, "/");
			
			$umbrella = $cacheManager->getItem($prefixed);

			if (!$umbrella)

				$umbrella = new RouteUmbrella($prefixed);

			$umbrella->addUser($userId, $nodeContent);

			$saved = $cacheManager->saveItem($prefixed, $umbrella);

			if ($contentType)

				$cacheManager->tagItem($contentType, $umbrella);

			// better still, this guy can subscribe to a topic(instead of using tags?). update listener publishes to that topic (so we never have outdated content)
		}

		/**
		 * These dependencies are obtained after reading flow structure so they can't be injected in our constructor
		 * 
		 * @param {rendererManager} the manager designated to handle this request
		*/
		public function setDependencies(RoutedRendererManager $rendererManager, $previousResponse, string $urlPattern):self {

			$this->previousResponse = $previousResponse;

			$this->rendererManager = $rendererManager;

			$this->baseUrlPattern = $urlPattern;

			return $this;
		}

		/**
		*	Pipes a controlled list of variables to a path's controller action
		*
		*	@param {flowStructure} $flow->previousResponse()->handler()
		*/
		public function runNodes(UnitNode $flowStructure, string $userId):void {

			$parentHandler = $this->parentHandlers[get_class($flowStructure)];

			$this->rendererToStorable(
				$this->$parentHandler($flowStructure),

				$flowStructure, $userId
			);
		}

		/**
		 * @param {generatedRenderers} GeneratedUrlExecution[]
		 * @param {flowStructure} the original one given
		*/
		public function rendererToStorable (array $generatedRenderers, UnitNode $flowStructure, string $userId):void {

			foreach ($generatedRenderers as $generationUnit) {

				$renderer = $generationUnit->getRenderer();
				
				$unitPayload = new RouteUserNode($renderer);

				$this->runNodeConfigs($unitPayload, $flowStructure);

				$this->storeContext(
					
					$generationUnit->getRequestPath(), $unitPayload, $userId,

					$this->getContentType($renderer)
				);
			}
		}

		private function getContentType(BaseRenderer $renderer):?string {

			$contentTypes = $this->flowConfig->contentTypeIdentifier();

			$payload = $renderer->getRawResponse();

			$payloadType = gettype($payload);

			if (array_key_exists($payloadType, $contentTypes))

				return call_user_func([$payload, $contentTypes[$payloadType]]);

			return null;
		}

		// @return BaseRenderer[]
		private function handleSingleNodes(SingleNode $builtNode):array {

			$carryRenderer = null;
			
			foreach ($this->builtNodeActions($builtNode) as $attribute => $value) {

				$handler = $this->singleSubHandlers[$attribute];

				$previousContent = $this->getNodeFromPrevious($builtNode);

				$carryRenderer = call_user_func_array(
					[$this, $handler],

					[$previousContent, $value/*, $carryRenderer*/]
				);

				$this->handledValidAction($carryRenderer);
					
			}

			return [$carryRenderer];
		}

		/**
		*	@return BaseRenderer[]
		*/
		private function handleCollectionNodes(CollectionNode $builtNode):array {

			if ($builtNode->deferExtraction())

				$carryRenderer = null;

			else $carryRenderer = $this->extractCollectionData($builtNode);

			foreach ($this->builtNodeActions($builtNode) as $attribute => $value) {

				$handler = $this->collectionSubHandlers[$attribute];

				$carryRenderer = call_user_func_array(
					[$this, $handler],
					
					[$carryRenderer, $value, $builtNode]
				);

				$this->handledValidAction($carryRenderer);
			}

			return $carryRenderer;
		}

		private function handledValidAction ($carryRenderer):void {

			if (is_null($carryRenderer))

				throw new Exception("Overwriting operation result with invalid values");
		}

		private function builtNodeActions (UnitNode $builtNode):array {

			$nodeActions = $builtNode->getActions();

			if (empty($nodeActions))

				throw new Exception("No action specified for given node");

			return $nodeActions;
		}

		private function extractCollectionData (CollectionNode $rawNode):array {

			$dataIndex = $rawNode->getLeafName();

			$mapped = [];

			foreach ($this->getNodeFromPrevious($rawNode) as $key => $valueObject)

				$mapped[$key] = Arr::get($valueObject, $dataIndex);

			return $mapped;
		}

		public function getNodeFromPrevious(UnitNode $rawNode):iterable {

			return Arr::get($this->previousResponse, $rawNode->getNodeName());
		}

		public function handleQuerySegmentAlter (array $nodeContent, string $newQueryHolder):?GeneratedUrlExecution {

			$valuePath = $nodeContent[$newQueryHolder];

			if (is_null($valuePath)) return null;

			$queryPart = parse_url($valuePath, PHP_URL_QUERY);

			parse_str($queryPart, $queryArray); // we don't bother passing the path part since it is expected that it = $this->baseUrlPattern

			$generated = $this->updatePayloadStorage($queryArray)->executeGeneratedUrl();

			$generated->changeUrl($valuePath);

			return $generated;
		}

		public function canProcessPath():bool {

			return $this->rendererManager->bootCoodinatorManager()

			->isValidRequest();
		}

		protected function updatePlaceholders (array $updates):self {

			$this->placeholderStorage->overwriteValues($updates);

			return $this;
		}

		protected function updatePayloadStorage (array $updates):self {

			$this->payloadStorage->mergePayload($updates);

			return $this;
		}

		/**
		 * This runs the validation sequence for each single item in this stream just in case any of the ids in the list is invalid
		 * @param {indexes} Array of ids
		 * 
		 * @return GeneratedUrlExecution[]
		*/
		public function handlePipe (array $indexes, int $dummyValue, CollectionNode $rawNode):array {

			return array_map (function($value) use ($rawNode) {

				return $this->updatePlaceholders([

					$rawNode->getLeafName() => $value
				])
				->executeGeneratedUrl();
			}, $indexes );
		}

		public function executeGeneratedUrl ():?GeneratedUrlExecution {

			if (!$this->canProcessPath()) return null;

			$renderer = $this->rendererManager->handleValidRequest($this->payloadStorage);

			$requestPath = $this->placeholderStorage->getPathFromStack($this->baseUrlPattern);

			return new GeneratedUrlExecution($requestPath, $renderer);
		}

		public function handleOneOf (array $indexes, string $requestProperty):?GeneratedUrlExecution {

			return $this->updatePlaceholders([

				$requestProperty => implode(",", $indexes)
			])
			->executeGeneratedUrl();
		}

		public function handleRange (iterable $indexes, RangeContext $context):?GeneratedUrlExecution {

			return $this->updatePlaceholders([

				$context->getParameterMax() => max($indexes),

				$context->getParameterMin() => min($indexes)
			])
			->executeGeneratedUrl();
		}

		public function handleDateRange (array $indexes, RangeContext $context):?GeneratedUrlExecution {

			usort($indexes, function($a, $b) {

				return strtotime($a) - strtotime($b); // asc
			});

			return $this->updatePlaceholders([

				$context->getParameterMin() => $indexes[0], // use `current` here instead?

				$context->getParameterMax() => end($indexes)
			])
			->executeGeneratedUrl();
		}

		public function handleServiceSource($dummyPrevious, ServiceContext $context, CollectionNode $rawNode ):iterable {

			$concrete = $this->container->getClass($context->getServiceName());

			return call_user_func_array(
				[$concrete, $context->getMethod()],

				[$this->getNodeFromPrevious($rawNode)]
			);
		}

		public function runNodeConfigs(RouteUserNode $savedNode, UnitNode $rawNode):void {
			
			foreach ($rawNode->getConfig() as $config => $value) {

				$handler = $this->configHandlers[$config];
				
				call_user_func_array([$savedNode, $handler], [$value]);
			}
		}
	}
?>
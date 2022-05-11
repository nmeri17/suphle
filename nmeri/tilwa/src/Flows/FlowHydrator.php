<?php
	namespace Tilwa\Flows;

	use Tilwa\Flows\Structures\{RouteUserNode, RouteUmbrella, RangeContext, ServiceContext};

	use Tilwa\Flows\Previous\{ SingleNode, CollectionNode, UnitNode};

	use Tilwa\Contracts\{CacheManager, Presentation\BaseRenderer, Config\Flows};

	use Tilwa\Response\RoutedRendererManager;

	use Tilwa\Hydration\Container;

	use Tilwa\Request\PayloadStorage;

	use Tilwa\Routing\PathPlaceholders;

	use Illuminate\Support\Arr;

	use Exception;

	class FlowHydrator {

		private $previousResponse, $cacheManager, $payloadStorage,

		$rendererManager, $container, $placeholderStorage,

		$flowConfig,

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

		function __construct(CacheManager $cacheManager, Container $randomContainer, PathPlaceholders $placeholderStorage, PayloadStorage $payloadStorage, Flows $flowConfig) {

			$this->cacheManager = $cacheManager;

			$this->container = $randomContainer;

			$this->placeholderStorage = $placeholderStorage;

			$this->payloadStorage = $payloadStorage;

			$this->flowConfig = $flowConfig;
		}

		# @param {contentType} model type, where present
		private function storeContext(string $urlPattern, RouteUserNode $nodeContent, string $userId, ?string $contentType):void {

			$manager = $this->cacheManager;
			
			$umbrella = $manager->get($urlPattern);

			if (!$umbrella)

				$umbrella = new RouteUmbrella($urlPattern);

			$umbrella->addUser($userId, $nodeContent);

			$saved = $manager->save($urlPattern, $umbrella);

			if ($contentType)

				$saved->tag($contentType);

			// better still, this guy can subscribe to a topic(instead of using tags?). update listener publishes to that topic (so we hopefully have no loop)
		}

		/**
		*	@param {rendererManager} the manager designated to handle this request if it entered app organically
		*/
		public function setDependencies(RoutedRendererManager $rendererManager, $previousResponse):self {

			$this->previousResponse = $previousResponse;

			$this->rendererManager = $rendererManager;

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

		public function rendererToStorable (array $generatedRenderers, UnitNode $flowStructure, string $userId):void {

			foreach ($generatedRenderers as $renderer) {
				
				$unitPayload = new RouteUserNode($renderer);

				$this->runNodeConfigs($unitPayload, $flowStructure);

				$this->storeContext(
					
					$renderer->getPath(), $unitPayload, $userId,

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

		public function handleQuerySegmentAlter(array $nodeContent, string $newQueryHolder):?BaseRenderer {

			$valuePath = $nodeContent[$newQueryHolder];

			if (is_null($valuePath)) return null;

			$queryPart = parse_url($valuePath, PHP_URL_QUERY);

			parse_str($queryPart, $queryArray); // we don't bother passing the path part since it is expected that that is the flow anchor url

			return $this->updateRequest($queryArray)->executeRequest();
		}

		public function canProcessPath():bool {

			return $this->rendererManager->bootCoodinatorManager()

			->isValidRequest();
		}

		protected function updateRequest(array $updates):self {

			$this->placeholderStorage->overwriteValues($updates);

			return $this;
		}

		/**
		 * This runs the validation sequence for each single item in this stream just in case any of the ids in the list is invalid
		 * @param {indexes} Array of ids
		 * 
		 * @return BaseRenderer[]
		*/
		public function handlePipe(array $indexes, int $dummyValue, CollectionNode $rawNode):array {

			return array_map (function($value) use ($rawNode) {

				return $this->updateRequest([

					$rawNode->getLeafName() => $value
				])
				->executeRequest();
			}, $indexes );
		}

		// @return executes underlying renderer and returns it
		public function executeRequest():?BaseRenderer {

			if ($this->canProcessPath())

				return $this->rendererManager->handleValidRequest($this->payloadStorage);
		}

		public function handleOneOf (array $indexes, string $requestProperty):?BaseRenderer {

			return $this->updateRequest([

				$requestProperty => implode(",", $indexes)
			])
			->executeRequest();
		}

		public function handleRange (iterable $indexes, RangeContext $context):?BaseRenderer {

			return $this->updateRequest([

				$context->getParameterMax() => max($indexes),

				$context->getParameterMin() => min($indexes)
			])
			->executeRequest();
		}

		public function handleDateRange (array $indexes, RangeContext $context):?BaseRenderer {

			usort($indexes, function($a, $b) {

				return strtotime($a) - strtotime($b); // asc
			});

			return $this->updateRequest([

				$context->getParameterMin() => $indexes[0], // use `current` here instead?

				$context->getParameterMax() => end($indexes)
			])
			->executeRequest();
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
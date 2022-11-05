<?php
	namespace Suphle\Flows;

	use Suphle\Flows\Structures\{RouteUserNode, RangeContext, ServiceContext, GeneratedUrlExecution, PendingFlowDetails};

	use Suphle\Flows\Previous\{ SingleNode, CollectionNode, UnitNode};

	use Suphle\Contracts\{Presentation\BaseRenderer, Auth\AuthStorage};

	use Suphle\Response\RoutedRendererManager;

	use Suphle\Hydration\Container;

	use Suphle\Request\PayloadStorage;

	use Suphle\Routing\PathPlaceholders;

	use Illuminate\Support\Arr;

	use Exception;

	/**
	 * Dependencies defined in dependencyMethods are obtained after reading flow structure, so they can't be injected in our constructor. For the same reason, we're deliberately not implementing VariableDependencies.

	 placeholderStorage and payloadStorage shouldn't be random instances but the one modified by routeManager for this module
	*/
	class FlowHydrator {

		private $previousResponse, $flowSaver, $payloadStorage,

		$rendererManager, $container, $placeholderStorage,

		$baseUrlPattern,

		$parentHandlers = [
			SingleNode::class => "handleSingleNodes",

			CollectionNode::class => "handleCollectionNodes"
		],
			
		$collectionSubHandlers = [

			CollectionNode::PIPE_TO => "handlePipe",

			CollectionNode::IN_RANGE => "handleRange",

			CollectionNode::DATE_RANGE => "handleDateRange",

			CollectionNode::AS_ONE => "handleAsOne",

			CollectionNode::FROM_SERVICE =>	"handleServiceSource"
		],
			
		$singleSubHandlers = [

			SingleNode::ALTERS_QUERY_SEGMENT => "handleQuerySegmentAlter"
		],

		$configHandlers = [
			
			UnitNode::TTL => "setExpiresAtHydrator",

			UnitNode::MAX_HITS => "setMaxHitsHydrator"
		];

		public function __construct (UmbrellaSaver $flowSaver) {

			$this->flowSaver = $flowSaver;
		}

		public function dependencyMethods ():array {

			return [

				"setContainer", "setPlaceholderStorage",

				"setPayloadStorage", "setRendererManager"
			];
		}

		public function setContainer (Container $container):void {

			$this->container = $container;
		}

		public function setPayloadStorage (PayloadStorage $payloadStorage):void {

			$this->payloadStorage = $payloadStorage;
		}

		public function setPlaceholderStorage (PathPlaceholders $placeholderStorage):void {

			$this->placeholderStorage = $placeholderStorage;
		}

		// manager designated to handle this request
		public function setRendererManager (RoutedRendererManager $rendererManager):void {

			$this->rendererManager = $rendererManager;
		}

		public function setRequestDetails ($previousResponse, string $urlPattern ):void {

			$this->previousResponse = $previousResponse;

			$this->baseUrlPattern = $urlPattern;
		}

		/**
		*	Pipes a controlled list of variables to a path's controller action
		*
		*	@param {flowStructure} $flow->previousResponse()->handler()
		*/
		public function runNodes(UnitNode $flowStructure, PendingFlowDetails $originatingFlowDetails):void {

			$parentHandler = $this->parentHandlers[$flowStructure::class];

			$this->bindObjectsForUser($originatingFlowDetails);

			$this->rendererToStorable(
				$this->$parentHandler($flowStructure),

				$flowStructure, $originatingFlowDetails
			);
		}

		protected function bindObjectsForUser (PendingFlowDetails $originatingFlowDetails):void {

			$storageInstance = $this->container->getClass(

				$originatingFlowDetails->getAuthStorage()
			);

			$storedId = $originatingFlowDetails->getStoredUserId();

			if ($storedId != OuterFlowWrapper::ALL_USERS)

				$storageInstance->imitate($storedId);

			$this->container->whenTypeAny()->needsAny([

				AuthStorage::class => $storageInstance
			]);
		}

		/**
		 * @param {generatedRenderers} GeneratedUrlExecution[]
		 * @param {flowStructure} the original one given
		*/
		public function rendererToStorable (

			array $generatedRenderers, UnitNode $flowStructure,

			PendingFlowDetails $originatingFlowDetails
		):void {

			foreach ($generatedRenderers as $generationUnit) {

				$unitPayload = new RouteUserNode(

					$generationUnit->getRenderer()
				);

				$this->runNodeConfigs($unitPayload, $flowStructure);

				$this->flowSaver->saveNewUmbrella(
					$generationUnit->getRequestPath(),

					$unitPayload, $originatingFlowDetails
				);
			}
		}

		/**
		*	@return GeneratedUrlExecution[]
		*/
		private function handleSingleNodes(SingleNode $builtNode):array {

			$carryRenderer = null;
			
			foreach ($this->builtNodeActions($builtNode) as $attribute => $dummyValue) {

				$handler = $this->singleSubHandlers[$attribute];

				$previousContent = $this->getNodeFromPrevious($builtNode);

				$carryRenderer = call_user_func_array( // dispatch calls to the internal handlers e.g handleQuerySegmentAlter
					[$this, $handler],

					[$previousContent/*, $value, $carryRenderer*/]
				);

				$this->handledValidAction($carryRenderer);		
			}

			return [$carryRenderer];
		}

		/**
		*	@return GeneratedUrlExecution[]
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

			$previousNode = $this->getNodeFromPrevious($rawNode);

			if (is_null($previousNode)) return [];

			foreach ($previousNode as $key => $valueObject)

				$mapped[$key] = Arr::get($valueObject, $dataIndex);

			return $mapped;
		}

		/**
		 * @param {rawNode}:mixed, where payload e.g. = ["data" => mixed]. Null where key doesn't exist
		 */
		public function getNodeFromPrevious (UnitNode $rawNode) {

			if (Arr::has(

				$this->previousResponse, $rawNode->getNodeName()
			))
				return Arr::get(

					$this->previousResponse, $rawNode->getNodeName()
				);

			return null;
		}

		/**
		 * @return GeneratedUrlExecution. All SingleNode based handlers are expected to return this type, to maintain uniformity
		*/
		public function handleQuerySegmentAlter (string $valuePath):GeneratedUrlExecution {

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
		 * @return GeneratedUrlExecution[]. All collectionNode based handlers are expected to return this type, to maintain uniformity
		*/
		public function handlePipe (array $indexes, int $dummyValue, CollectionNode $rawNode):array {

			$allUnits = array_map (function($value) use ($rawNode) {

				$generationUnit = $this->updatePlaceholders([

					$rawNode->getLeafName() => $value
				])
				->executeGeneratedUrl();

				return $generationUnit;
			}, $indexes );

			return $allUnits;
		}

		public function executeGeneratedUrl ():?GeneratedUrlExecution {

			if (!$this->canProcessPath()) return null;

			$originalRenderer = $this->rendererManager->handleValidRequest($this->payloadStorage);

			$clonedRenderer = clone $originalRenderer; // since we're working with just one renderer for all those calls, without cloning, updates to one (by virtue of updating placeholderStorage) cascades to them all. By cloning, we're able to store the temporary state in memory as its own unique object

			$requestPath = $this->placeholderStorage->getPathFromStack($this->baseUrlPattern);

			return new GeneratedUrlExecution($requestPath, $clonedRenderer);
		}

		/**
		 * @return GeneratedUrlExecution[]
		*/
		public function handleAsOne (array $indexes, string $requestProperty):array {

			$generatedContent = $this->updatePlaceholders([

				$requestProperty => implode(",", $indexes)
			])
			->executeGeneratedUrl();

			return [$generatedContent];
		}

		/**
		 * @return GeneratedUrlExecution[]
		*/
		public function handleRange (iterable $indexes, RangeContext $context):array {

			$generatedContent = $this->updatePlaceholders([

				$context->getParameterMax() => max($indexes),

				$context->getParameterMin() => min($indexes)
			])
			->executeGeneratedUrl();

			return [$generatedContent];
		}

		/**
		 * @return GeneratedUrlExecution[]
		*/
		public function handleDateRange (array $indexes, RangeContext $context):array {

			usort($indexes, function($a, $b) {

				return strtotime($a) - strtotime($b); // asc
			});

			$generatedContent = $this->updatePlaceholders([

				$context->getParameterMin() => $indexes[0], // use `current` here instead?

				$context->getParameterMax() => end($indexes)
			])
			->executeGeneratedUrl();

			return [$generatedContent];
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
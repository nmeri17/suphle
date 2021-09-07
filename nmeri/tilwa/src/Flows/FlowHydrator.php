<?php
	namespace Tilwa\Flows;

	use Tilwa\Contracts\{CacheManager, Orm};

	use Tilwa\Flows\Structures\{RouteUserNode, RouteUmbrella, RangeContext, ServiceContext};

	use Tilwa\Flows\Previous\{ SingleNode, CollectionNode, UnitNode};

	use Tilwa\Response\ResponseManager;

	use Tilwa\App\Container;

	use Illuminate\Support\{Collection, Arr};

	use Tilwa\Routing\{PathPlaceholders, RequestDetails};

	class FlowHydrator {

		private $previousResponse, $cacheManager, $requestDetails,

		$responseManager, $container, $placeholderStorage,

		$branchHandlers = [
			SingleNode::class => "handleSingleNodes",

			CollectionNode::class => "handleCollectionNodes"
		],
			
		$collectionSubHandlers = [

			CollectionNode::EACH_ATTRIBUTE => "extractCollectionData",

			CollectionNode::PIPE_TO => "handlePipe",

			CollectionNode::IN_RANGE => "handleRange",

			CollectionNode::DATE_RANGE => "handleDateRange",

			CollectionNode::ONE_OF => "handleOneOf",

			CollectionNode::FROM_SERVICE =>	"handleServiceSource"
		],
			
		$singleSubHandlers = [

			SingleNode::INCLUDES_PAGINATION => "handlePaginate"
		],

		$configHandlers = [
			
			UnitNode::TTL => "setExpiresAtHydrator",

			UnitNode::MAX_HITS => "setMaxHitsHydrator"
		];

		// if we can't hydrate this with our container, replace interfaces with hard-coded concretes
		function __construct(CacheManager $cacheManager, Orm $orm, Container $randomContainer, PathPlaceholders $placeholderStorage, RequestDetails $requestDetails) {

			$this->cacheManager = $cacheManager;

			$this->orm = $orm;

			$this->container = $randomContainer;

			$this->placeholderStorage = $placeholderStorage;

			$this->requestDetails = $requestDetails;
		}

		# @param {contentType} model type, where present
		private function storeContext(string $urlPattern, RouteUserNode $nodeContent, string $userId, string $contentType):void {

			$manager = $this->cacheManager;
			
			$umbrella = $manager->get($urlPattern);

			if (!$umbrella) $umbrella = new RouteUmbrella($urlPattern);

			$umbrella->addUser($userId, $nodeContent);

			$saved = $manager->save($urlPattern, $umbrella);

			if ($contentType) $saved->tag($contentType);

			// better still, this guy can subscribe to a topic(instead of using tags?). update listener publishes to that topic (so we hopefully have no loop)
		}

		/**
		*	@param {responseManager} the manager designated to handle this request if it entered app organically
		*/
		public function setDependencies(ResponseManager $responseManager, $previousResponse):self {

			$this->previousResponse = $previousResponse;

			$this->responseManager = $responseManager;

			return $this;
		}

		/**
		*	Pipes a controlled list of variables to a path's controller action
		*
		*	@param {flowStructure} $flow->previousResponse()->actionX()
		*/
		public function runNodes(UnitNode $flowStructure, string $userId):void {

			$handler = $this->branchHandlers[$flowStructure::class];

			$evaluatedRenderers = call_user_func_array([$this, $handler], [$flowStructure, $renderer]);

			foreach ($evaluatedRenderers as $renderer) { // SingleNodes should only return array of length 1 here

				$contentType = $this->getContentType($renderer);

				$urlPattern = $renderer->getPath();
				
				$unitPayload = new RouteUserNode($renderer);

				$this->configureRenderer($unitPayload, $flowStructure);

				$this->storeContext($urlPattern, $unitPayload, $userId, $contentType);
			}
		}

		private function getContentType($evaluatedRenderers):string {

			$contentTypes = [
				Collection::class => "getQueueableClass"
			];

			$value = current($evaluatedRenderers);

			$typeSpotter = @$contentTypes[get_class($value)];

			if ($typeSpotter)

				return $value->$typeSpotter();
		}

		// @return AbstractRenderer[]
		private function handleSingleNodes(SingleNode $rawNode):array {

			$carryRenderer = null;

			foreach($rawNode->getActions() as $attribute) {

				$handler = $this->singleSubHandlers[$attribute];

				$previousContent = $this->getNodeFromPrevious($rawNode);

				$carryRenderer = call_user_func_array(
					[$this, $handler],

					[$previousContent/*, $carryRenderer*/]
				);
			}

			return [$carryRenderer];
		}

		/**
		*	Will return the result of the last operation
		*	@return AbstractRenderer[]
		*/
		private function handleCollectionNodes(CollectionNode $rawNode):array {

			$carryRenderer = null;

			foreach ($rawNode->getActions() as $attribute => $value) {

				$handler = $this->collectionSubHandlers[$attribute];

				$carryRenderer = call_user_func_array(
					[$this, $handler],
					
					[$carryRenderer, $value, $rawNode]
				);
			}

			return $carryRenderer;
		}

		/**
		*	@param {dataIndex} previous response = [ourNode => [[dataIndex => 1], [dataIndex => 2]], otherNode => value]
		*/
		private function extractCollectionData($currentSource, string $dataIndex, CollectionNode $rawNode):array {

			if (is_null($currentSource))

				$currentSource = $this->getNodeFromPrevious($rawNode);

			$indexes = [];

			foreach ($currentSource as $valueObject)
				
				$indexes[] = Arr::get($valueObject, $dataIndex);

			return $indexes;
		}

		private function getNodeFromPrevious(UnitNode $rawNode) {

			$keyName = $rawNode->getNodeName();

			return Arr::get($this->previousResponse, $keyName);
		}

		private function handlePaginate($nodeContent):AbstractRenderer {

			$valuePath = $nodeContent[$this->orm->getPaginationPath()];

			$queryPart = parse_url($valuePath, PHP_URL_QUERY);

			return $this->updateRequest(parse_str($queryPart))
			
			->executeRequest();
		}

		private function canProcessPath():bool {

			return $this->responseManager->bootControllerManager()

			->isValidRequest();
		}

		private function updateRequest(array $updates):self {

			$this->placeholderStorage->overwriteValues($updates);

			return $this;
		}

		// @return AbstractRenderer[]
		private function handlePipe(array $indexes):array {

			$results = [];

			foreach ($indexes as $payload)

				$results[] = $this->updateRequest($payload)

				->executeRequest(); // note: runs validation for each single item in this stream. validate and boot manager only once instead?

			return $results;
		}

		// @return executes underlying renderer and returns it
		private function executeRequest() {

			if ($this->canProcessPath())

				return $this->responseManager->handleValidRequest($this->requestDetails);
		}

		private function handleOneOf(array $indexes, string $requestProperty):AbstractRenderer {

			return $this->updateRequest([

				$requestProperty => implode(",", $indexes)
			])
			->executeRequest();
		}

		private function handleRange(array $indexes, RangeContext $context):AbstractRenderer {

			return $this->updateRequest([

				$context->getParameterMax() => max($indexes),

				$context->getParameterMin() => min($indexes)
			])
			->executeRequest();
		}

		private function handleDateRange(array $indexes, RangeContext $context):AbstractRenderer {

			usort($indexes, function($a, $b) {

				return strtotime($a) - strtotime($b); // asc
			});

			return $this->updateRequest([

				$context->getParameterMin() => $indexes[0], // use `current` here instead?

				$context->getParameterMax() => end($indexes)
			])
			->executeRequest();
		}

		private function handleServiceSource($currentSource, ServiceContext $context, CollectionNode $rawNode, ):iterable {

			$concrete = $this->container->getClass($context->getServiceName());

			return call_user_func_array(
				[$concrete, $context->getMethod()],

				[$this->getNodeFromPrevious($rawNode)]
			);
		}

		private function configureRenderer(RouteUserNode $newNode, UnitNode $rawNode):void {
			
			foreach ($rawNode->getConfig() as $config => $value) {

				$handler = $this->configHandlers[$config];
				
				call_user_func_array([$newNode, $handler], [$value]);
			}
		}
	}
?>
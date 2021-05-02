<?php
	namespace Tilwa\Flows;

	use Tilwa\Contracts\{CacheManager, Orm};

	use Tilwa\Flows\Structures\{RouteUserNode, RouteUmbrella};

	use Tilwa\Flows\Previous\{ SingleNode, CollectionNode, UnitNode};

	use Tilwa\Http\Request\BaseRequest;

	use Tilwa\Http\Response\ResponseManager;

	class FlowHydrator {

		private $previousResponse, $cacheManager,

		$responseManager,

		$branchHandlers = [
			SingleNode::class => "handleSingleNodes",

			CollectionNode::class => "handleCollectionNodes"
		];

		function __construct(CacheManager $cacheManager, Orm $orm) {

			$this->cacheManager = $cacheManager;

			$this->orm = $orm;
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

		public function setDependencies(ResponseManager $responseManager, $previousResponse):self {

			$this->previousResponse = $previousResponse;

			$this->responseManager = $responseManager;

			return $this;
		}

		/**
		* Description: Pipes a controlled list of variables to a path's controller action
		*
		* @param {flowSignature} $flow->previousResponse()->actionX()
		* @param {responseManager} the manager designated to handle this request if it entered app organically
		*/
		public function runNodes(UnitNode $flowSignature, string $userId):void {

			$handler = $this->branchHandlers[$flowStructure::class];

			$builtNodes = call_user_func_array([$this, $handler], [$flowStructure, $renderer]);
			// then remember to handle base actions on the UnitNode

			foreach ($builtNodes as $builtNode) { // SingleNodes should only return array of length 1 here

				$contentType = $this->getContentType($builtNode); // find a way to fit this in

				$urlPattern = $builtNode->getPath();
				
				$unitPayload = new RouteUserNode( $builtNode);

				$this->storeContext($urlPattern, $unitPayload, $userId, $contentType);
			}
		}

		# infer from the dominant type of the first value found
		private function getContentType($builtNodes):string {
			
		}

		private function handleSingleNodes(SingleNode $rawNode):array {
			
			$singleMap = [

				SingleNode::INCLUDES_PAGINATION => "handlePaginate"
			];

			$carryRenderer = null;

			foreach($rawNode->getActions() as $attribute)

				$carryRenderer = call_user_func_array([$this, $singleMap[$attribute]], [$rawNode, $carryRenderer]);

			return [$carryRenderer];
		}

		// these guys basically mock a request object and run against the underlying controller for this request
		private function handleCollectionNodes(CollectionNode $rawNode) {

			$rawNode->getActions();
		}

		private function getNodeFromPrevious(UnitNode $rawNode) {

			$keyName = $rawNode->getNodeName();

			if (is_object($this->previousResponse))

				return $this->previousResponse->$keyName;
			
			return $this->previousResponse[$keyName];
		}

		private function handlePaginate(SingleNode $rawNode):AbstractRenderer {

			$ourNode = $this->getNodeFromPrevious($rawNode);

			$valuePath = $ourNode[$this->orm->getPaginationPath()];

			$queryPart = parse_url($valuePath)["query"];

			$this->updateRequest($queryPart);

			if ($this->canProcessPath())

				return $this->responseManager->handleValidRequest();
		}

		private function canProcessPath():bool {

			$manager = $this->responseManager;

			$manager->bootControllerManager()

			->assignValidRenderer();

			return !$manager->rendererValidationFailed();
		}

		private function updateRequest(string $query):void {

			$this->responseManager->getControllerManager()->getRequest()

			->setPlaceholders(parse_str($query));
		}
	}
?>
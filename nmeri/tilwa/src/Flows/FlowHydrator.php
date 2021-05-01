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

		/**
		* Description: Pipes a controlled list of variables to a path's controller action
		*
		* @param {flowSignature} $flow->previousResponse()->actionX()
		* @param {responseManager} the manager designated to handle this request if it entered app organically
		*/
		public function runNodes(ResponseManager $responseManager, UnitNode $flowSignature, string $userId, $previousResponse):void {

			$this->previousResponse = $previousResponse;

			$this->responseManager = $responseManager;

			$handler = $this->branchHandlers[$flowStructure::class];

			$builtNodes = call_user_func_array([$this, $handler], [$flowStructure, $renderer]); // do these guys need renderers or managers?

			foreach ($builtNodes as $builtNode) { // SingleNodes should only return array of length 1 here

				$contentType = $this->getContentType($builtNode); // find a way to fit this in

				$urlPattern = $this->getPathFromIdentifier($builtNode, $renderer);
				
				$unitPayload = new RouteUserNode($renderer, $builtNode);

				$this->storeContext($urlPattern, $unitPayload, $userId, $contentType);
			}
			// work with the controller flow expiry time and co
		}

		private function getContentType($builtNodes):string {
			# code...
		}

		private function handleSingleNodes(SingleNode $rawNode) {
			
			$singleMap = [

				SingleNode::INCLUDES_PAGINATION => "handlePaginate"
			];

			foreach($rawNode->getActions() as $attribute)

				call_user_func_array([$this, $singleMap[$attribute]], [$rawNode]);
		}

		// these guys basically mock a request object and run against the underlying controller for this request
		private function handleCollectionNodes(CollectionNode $rawNode) {

			$rawNode->getActions();
		}

		private function getPathFromIdentifier($builtNodes, AbstractRenderer $renderer):string {
			# code...
		}

		private function getNodeFromPrevious(UnitNode $rawNode) {

			$keyName = $rawNode->getNodeName();

			if (is_object($this->previousResponse))

				return $this->previousResponse->$keyName;
			
			return $this->previousResponse[$keyName];
		}

		// get node name, pull from previous response, get value path
		private function handlePaginate(SingleNode $rawNode) {

			$ourNode = $this->getNodeFromPrevious($rawNode);

			$valuePath = $ourNode[$this->orm->getPaginationPath()];

			// we want responseManager->getResponse

			// so how do i inject incoming value or request query? maybe pull and update renderer's request before setting the above in motion
		}
	}
?>
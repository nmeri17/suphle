<?php
	namespace Tilwa\Flows;

	use Tilwa\Contracts\{CacheManager, Orm};

	use Tilwa\Flows\Structures\{RouteUserNode, RouteUmbrella};

	use Tilwa\Flows\Previous\{ SingleNode, CollectionNode, UnitNode};

	use Tilwa\Http\Request\BaseRequest;

	class FlowHydrator {

		private $previousResponse, $cacheManager, 

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
		*/
		public function runNodes(AbstractRenderer $renderer, UnitNode $flowSignature, string $userId, $previousResponse):void {

			$this->previousResponse = $previousResponse;

			$handler = $this->branchHandlers[$flowStructure::class];

			foreach ($this->$handler($flowStructure, $renderer) as $builtNode) { 

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

			foreach($rawNode->getActions() as $attribute) {

				$subHandler = $singleMap[$attribute];

				$this->$subHandler($rawNode);
			}
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

		// get node name, pull from previous response, get value path. the route manager will likely match our own
		private function handlePaginate(SingleNode $rawNode) {

			$ourNode = $this->getNodeFromPrevious($rawNode);

			$valuePath = $ourNode[$this->orm->getPaginationPath()];

			// find a way to access this renderer's response manager->getResponse
		}

		private function getRequestFromUrl(string $path):BaseRequest {
			# pull the request applicable to this controller
		}
	}
?>
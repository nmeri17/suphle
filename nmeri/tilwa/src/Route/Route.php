<?php

	namespace Nmeri\Tilwa\Route;

	class Route {

		public $reqName; // actual user request name

		public $queryVars;

		public $pattern;


		// setting `viewName` to false skips the trip to parse		
		function __construct(

			string $pathPattern, string $source, $viewName = 'index', string $method = 'get', 

			bool $appendHeader = true, $middleware = []
		) {

			$this->hasQuery();

			$this->validateSource($source);


			$this->middleware = is_string($middleware) ? [$middleware] : $middleware;

			$this->appendHeader = $appendHeader;

			$this->reqName = explode('/', $_SERVER['REQUEST_URI'])[ getenv('ENV') == 'dev' ? 1 : 2 ];

			$this->pattern = $pathPattern;
		}


		// seriously in need of review. Should we alter this to begin with
		private function hasQuery () {
			
			preg_match('/([\w=&,-:]+)$/', @urldecode($_GET['query']), $viewState);

			if (!empty($viewState)) $this->queryVars = $viewState[1];
		}

		private function validateSource ( $src ) {

			if (
				preg_match('/[^\/,]([\\@\w]+)/', $src, $res) &&

				(strlen($res[1]) == strlen($src))
			)

				$this->source = $src;

			else $this->source = 'errorClass@handler';
		}
	}

?>
<?php
	namespace Suphle\Tests\Integration\Modules;

	use Suphle\Contracts\{Auth\ModuleLoginHandler, Config\Router};

	use Suphle\Flows\OuterFlowWrapper;

	use Suphle\Testing\{Condiments\DirectHttpTest, Proxies\WriteOnlyContainer};

	use Suphle\Tests\Integration\Flows\Jobs\RouteBranches\JobFactory;

	use Suphle\Tests\Mocks\Modules\ModuleOne\{Routes\Flows\OriginCollection, Meta\ModuleOneDescriptor, Config\RouterMock};

	class ModuleHandlerIdentifierTest extends JobFactory {

		use DirectHttpTest, DoublesHandlerIdentifier;

		protected function setUp ():void {

			$this->setDummyRenderer();

			parent::setUp();
		}

		protected function getModules():array {

			return [

				$this->replicateModule(ModuleOneDescriptor::class, function (WriteOnlyContainer $container) {

					$container->replaceWithMock(Router::class, RouterMock::class, [

						"browserEntryRoute" => OriginCollection::class
					]);
				})
			];
		}
		
		public function test_can_handle_login () {

			$this->setHttpParams("/login", "post", []); // given

			$this->getHandlerIdentifier([

				"getLoginHandler" => $this->mockLoginHandler() // then	
			])
			->respondFromHandler(); // when
		}

		private function mockLoginHandler ():ModuleLoginHandler {

			return $this->positiveDouble(ModuleLoginHandler::class,

				[

					"isValidRequest" => true,

					"handlingRenderer" => $this->dummyRenderer,

					"setResponseRenderer" => $this->returnSelf()
				], [

					"processLoginRequest" => [

						$this->atLeastOnce(), []
					]
				]
			);
		}

		public function test_saved_flow_triggers_flow_handler () {

			$this->handleDefaultPendingFlowDetails(); // given

			//$this->assertHandledByFlow($this->userUrl);
			
			$this->setHttpParams($this->userUrl); // when

			$this->getHandlerIdentifier([], [

				"flowRequestHandler" => [$this->atLeastOnce(), [ // then

					$this->callback(function($argument) {

						return is_a($argument, OuterFlowWrapper::class);
					})
				]]
			])
			->respondFromHandler();
		}
	}
?>
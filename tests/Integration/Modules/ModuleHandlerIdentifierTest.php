<?php
	namespace Suphle\Tests\Integration\Modules;

	use Suphle\Modules\ModuleHandlerIdentifier;

	use Suphle\Contracts\{Auth\ModuleLoginHandler, Presentation\BaseRenderer, Config\Router};

	use Suphle\Flows\OuterFlowWrapper;

	use Suphle\Testing\{Condiments\DirectHttpTest, Proxies\WriteOnlyContainer};

	use Suphle\Tests\Integration\Flows\Jobs\RouteBranches\JobFactory;

	use Suphle\Tests\Mocks\Modules\ModuleOne\{Routes\Flows\OriginCollection, Meta\ModuleOneDescriptor, Config\RouterMock};

	class ModuleHandlerIdentifierTest extends JobFactory {

		use DirectHttpTest;

		private $dummyRenderer;

		protected function setUp ():void {

			$this->dummyRenderer = $this->positiveDouble(BaseRenderer::class);

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

			$this->getIdentifier([

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

			$this->getIdentifier([], [

				"flowRequestHandler" => [$this->atLeastOnce(), [ // then

					$this->callback(function($argument) {

						return is_a($argument, OuterFlowWrapper::class);
					})
				]]
			])
			->respondFromHandler();
		}

		private function getIdentifier (array $stubMethods, array $mockMethods = []):ModuleHandlerIdentifier {

			$identifier = $this->replaceConstructorArguments(

				ModuleHandlerIdentifier::class, [],

				array_merge([

					"getModules" => $this->modules,

					"handleGenericRequest" => $this->dummyRenderer
				], $stubMethods),

				$mockMethods,

				true, true, true, true
			);

			$identifier->extractFromContainer();

			return $identifier;
		}
	}
?>
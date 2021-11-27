<?php
	namespace Tilwa\Tests\Integration\Flows\Jobs;

	use Tilwa\Testing\TestTypes\ModuleLevelTest;

	class RouteBranchesMultiModuleTest extends ModuleLevelTest {
		
		public function test_handle_flows_in_other_modules ($renderer, $user, $modules, $responseManager) {

			// for this test to hold water, you'd have to trigger the job without mocks so it actually picks the associated controller and runs it 
		}
	}
?>
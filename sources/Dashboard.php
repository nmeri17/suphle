<?php

	namespace Sources;

	use Tilwa\Sources\BaseSource;

	class Dashboard extends BaseSource {

		public function profile ( array $reqData, array $reqPlaceholders, array $validationErrors) {

			$reqData['userData'] = $this->app->fresh('user');

			//$conn = $this->app->connection;

			var_dump($reqData ); die();

			if ($userData['role'] == 'user') $this->dataBlocks = $this->user( $reqData);

			$this->dataBlocks = $this->admin( $reqData);

			return $this->dataBlocks;
		}

		private function admin ( array $reqData) {

			$queriesMap = []; // array of tables to fetch from

			$paginationNames = [$reqData['transPN'], $reqData['testPN'], $reqData['userPN'] ]; // passed from GET

			$jsScripts = [['admin_script' => '<script src="/assets/admin-profile.js"></script>']];

			$finalRes = [
				[['left_menu' => file_get_contents($this->container->viewPath . 'admin-menu.tmpl')]]
			];


			foreach ($queriesMap as $key => $value) {
				
				$pdo = $conn->prepare("SELECT * FROM `$value` WHERE id > ? LIMIT ?"); // ORDER BY `date` DESC

				$pdo->execute([ $paginationNames[$key], $reqData['limit']]);

				$pdo = $pdo->fetchAll(PDO::FETCH_ASSOC);

				$finalRes[] = $this->formatForEngine( $queriesMap[$key]);
			}

			$finalRes[1] = [
				//[0=>[['current_balance'=> $reqData['userData']['balance']]]],

				[/*1=>*/$finalRes[1]['fullTable']['allRows']]
			];


			array_splice($finalRes, 2, 0, [[],[]]); // user testimonial & transactions will be blank in admin

			array_splice($finalRes, 6, 0, [[]]); // ordinary user

			$finalRes[] = $jsScripts;
			
			return $finalRes;
		}

		private function user ( array $reqData) {

			$leftMenu = ['left_menu' => '
				<a>Menu Item 1</a>
				<a>Menu Item 2</a>
				<a>Menu Item 3</a>
			'];

			$userTransactions = $this->formatForEngine( $reqData);

			
			// account section
			$veriConfir = file_get_contents($this->container->viewPath . 'user-verify.tmpl');

			$myAccount = [
				[ 0=> [$reqData['userData'] ]
			], [1=> [['verify_user' => $veriConfir]] ]
			];


			return [[$leftMenu], [], $userTransactions, [], [], [], $myAccount, [], [], [], [],[]];
		}

		protected function semantics (array $data):array {}
	}

?>
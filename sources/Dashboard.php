<?php

	use Nmeri\Tilwa\Sources\Source;

	class Dashboard extends Source {

		public function dashboard ( string $urlSlug, array $opts) {

			$userData = json_decode($this->getCtrl->getContents(  ), true);

			$opts['userData'] = $this->container->user;

			if ($userData['role'] == 'user') $this->dataBlocks = $this->userProfile( $opts);

			$this->dataBlocks = $this->adminProfile( $opts);

			return $this;
		}

		private function adminProfile ( array $opts) {

			$queriesMap = [];

			$paginationNames = [$opts['transPN'], $opts['testPN'], $opts['userPN'] ];

			$jsScripts = [['admin_script' => '<script src="/assets/admin-profile.js"></script>']];

			$finalRes = [
				[['left_menu' => file_get_contents('../views/admin-menu.tmpl')]]
			];


			foreach ($queriesMap as $key => $value) {
				
				$pdo = $conn->prepare("SELECT * FROM `$value` WHERE id > ? LIMIT ?"); // ORDER BY `date` DESC

				$pdo->execute([ $paginationNames[$key], $opts['limit']]);

				$pdo = $pdo->fetchAll(PDO::FETCH_ASSOC);

				$finalRes[] = self::formatForEngine($pdo, $queriesMap[$key]);
			}

			$finalRes[1] = [
				//[0=>[['current_balance'=> $opts['userData']['balance']]]],

				[/*1=>*/$finalRes[1]['fullTable']['allRows']]
			];


			array_splice($finalRes, 2, 0, [[],[]]); // user testimonial & transactions will be blank in admin

			array_splice($finalRes, 6, 0, [[]]); // ordinary user

			$finalRes[] = $jsScripts;
			
			return $finalRes;
		}

		private static function userProfile (PDO $conn, array $opts) {

			$leftMenu = ['left_menu' => '<a>Transactions</a>
				<a>Testimonials</a>
				<a>Account Management</a>'];

			$userTransactions = self::formatForEngine($userTransactions, 'civili-trans');

			
			// account section
			$veriConfir = file_get_contents('../views/user-verify.tmpl');

			$userVeriCrit = [];

			$veriStat = array_filter($userVeriCrit, function ($loca) use ($opts) {
				return true;
			});

			if (count($veriStat) == count($userVeriCrit)) $veriConfir = "<p>Account Verified</p>";

			$myAccount = [
				[ 0=> [$opts['userData'] ]
			], [1=> [['verify_user' => $veriConfir]] ]
			];


			return [[$leftMenu], [], $userTransactions, [], [], [], $myAccount, [], [], [], [],[]];
		}

		protected function semantics (array $data):array {}
	}

?>
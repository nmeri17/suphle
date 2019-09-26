<?php


 class Dashboard {

	public static function dashboard (PDO $conn, string $urlSlug, array $opts) {

		session_start();
		
		$_SESSION['id'] = 'user5c8fa27da2340'; // user5c8fa2581b4f9

		$userData = json_decode(TilwaGet::getContents($conn, $_SESSION['id']), true);

		$limit = 25;

		// set default page numbers
		foreach (['testPN', 'userPN', 'currPN', 'couPN', 'dataPN', 'transPN'] as $value) {
			
			if (!isset($opts[$value])) $opts[$value] = 0;

			else $opts[$value] *= $limit;
		}

		$opts['limit'] = $limit;

		$opts['userData'] = $userData;

		if ($userData['role'] == 'user') $userVals = self::userProfile($conn, $opts);

		else $userVals = self::adminProfile($conn, $opts);

		return $userVals;
	}

	private static function adminProfile (PDO $conn, array $opts) {

		$queriesMap = [];

		$paginationNames = [$opts['transPN'], $opts['testPN'], $opts['userPN'], $opts['currPN'], $opts['couPN'], $opts['dataPN'], 0 ];

		$jsScripts = [['admin_script' => '<script src="/assets/admin-profile.js"></script>']];

		$finalRes = [
			[['left_menu' => file_get_contents('../views/admin-menu.tmpl')]]
		];


		foreach ($queriesMap as $key => $value) {

			$conn->setAttribute( PDO::ATTR_EMULATE_PREPARES, false); // to retain int data type
			
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

?>
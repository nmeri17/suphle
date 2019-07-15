<?php

	use \Get\TilwaGet;

/**
*	@description: Methods on this class are required to fetch live data from relevant sources and return them in a presentable format to be piped into their respective views. Except in the case of internally used methods, method names on this class correspond to the name of the view (directory/parent content type) they're gathering data for.
*
*	In cases where a non-existent invalid resource or malformed parameters are requested, throwing `new TypeError`, will trigger a 404 error page
 *
 *	@TO-DO: Because multiple people can't work on one model file, we can either
 *	a) have a master model `using` the traits of user model classes, via folder/namespace scan
 *	b) instead of calling a model method, pass a name to a factory method on the model, who will in turn pick the class with that method and call it
 *	c) abstract classes
 */
 class Model {
 	
 	public static function index (PDO $conn, $name, $opts) {

	    /*$cache = TilwaGet::cacheManager();*/
 		
 		// referrals
 		if ($ref = array_intersect(['betalist', 'producthunt'], $opts) && !empty($ref)) PostController::updateViews($conn, $ref[0]);

 		return [/*$variety*/]; // ordering here MUST correspond with the foreach ordering in the view
 	}

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


	/** 
	* @description: takes care of formatting multi-nested dataSet for templating
	* @return: the transformed `dataSet`
	*/
	private static function formatForEngine (array $dataSet, string $templName=null):array {

		// preselect current user role
		$hlightRoles = function ($arr) {
			
			$opts = ['user' => 'user', 'admin' => 'admin', 'juniorAdmin' => 'junior'];

			$arr[$opts[$arr['role']].'_role'] = 'selected'; # change keys to views' placeholder

			return $arr;
		};

		// preselect settings
		$hlightSettings = function ($arr) {		

	 		$opts = [ 'none' => 'none', 'photoIds' => 'photo', 'utilityBills' => 'utility', 'admin' => 'admin', 'juniorAdmin'  => 'junior'
	 		];

	 		return array_map(function ($currVal) use ($opts, &$arr) {

	 			$oVals = explode(',', $currVal); $retArr = ['name' => key($arr)]; next($arr);

		 		foreach ($oVals as $val) $retArr[$opts[$val].'_set'] = 'selected';

	 			return $retArr;
	 		}, $arr);
		};

		// preselect vtu network
		$hlightVtu = function ($arr) {
			
			$opts = ['glo' => 'glo', 'mtn' => 'mtn', '9mobile' => 'nmobile', 'airtel' => 'airtel'];

			$arr['vtu_' . $opts[$arr['network']]] = 'selected';

			return $arr;
		};

		$ntwToInt = function ($arr) {

			$opts = ['glo' => 3, 'mtn' => 2, '9mobile' => 4, 'airtel' => 1];
			
			$arr['network'] = $opts[$arr['network']];

			return $arr;
		};
 		
 		// front-end semantics
 		$anyAdditionalKeys = array_map(function (&$arr) use ($templName, &$anyAdditionalKeys, $hlightRoles, $hlightSettings,$hlightVtu, $ntwToInt) {

			switch ($templName) {
				case 'users':

					$pil = 'assets/imgs/photoIds/' . $arr['userId'] . '.png';

					$ubl = 'assets/imgs/utilityBills/' . $arr['userId'] . '.png';

					
					$arr['photoId'] = file_exists('../' . $pil) ? "<a href=$pil> yes </a>" : 'no';

					$arr['utilityBill'] = file_exists('../' . $ubl) ? "<a href=$ubl> yes </a>" : 'no';

					$arr = $hlightRoles($arr);
			 	break;
			 	
			 	case 'testimonials':
			 		$arr['approveOpp'] = $arr['approved'] == '1' ? 0 : 1;

			 		$arr['approved'] = $arr['approved'] == '1' ? 'checked' : '';
			 	break;

			 	case 'transactions':
			 		
			 		$arr['approved'] = $arr['approved'] == '1' ? 'checked disabled' : '';
			 	break;

			 	case 'ecurrencies':
			 		
			 		$arr['visible'] = $arr['visible'] == '1' ? 'checked' : '';

			 		if ($arr['approvalMode'] == 'manual') $arr['curr_manual'] = 'selected';

			 		else $arr['curr_auto'] = 'selected';
			 	break;

			 	case 'vtu-data-catalog':
			 		
			 		$arr['visible'] = $arr['visible'] == '1' ? 'checked' : '';

			 		$arr = $hlightVtu($arr);
			 	break;

			 	case 'civili-trans':
			 		
			 		$arr['approved'] = $arr['approved'] == '1' ? 'approved' : 'pending';
			 	break;

			 	case 'buy-data':

			 		$arr = $ntwToInt($arr);
			 	break;

			 	// fabricate new dataSet since only one row is returned from the db
			 	case 'settings':

			 		unset($arr['id']);

			 		$arr = [$hlightSettings($arr) ];

			 	break;
			}

			return [$arr];
		}, $dataSet);

		if ($templName == 'settings') $anyAdditionalKeys = $anyAdditionalKeys[0];

 		return [			

			'fullTable'=> [
				'allRows' => $anyAdditionalKeys
			],
		];
	}

	// restricted area
 	public static function metaDetails (PDO $conn, $name) {

 		$vars = json_decode(TilwaGet::getContents($conn, $name), true);

 		$breadcrumbs = [];

	 	$breadcrumbs['og_url'] = $postUrl = urlencode('https://boardman.com.ng/checkout?id='. $name);

	 	$desc = 'Will you like to bet against me on this cart? I think ';
	 		
	 	// share buttons
 		$breadcrumbs['share_twitter'] = 'https://twitter.com/intent/tweet/?text=' . urlencode($desc. ' #BoardmanCarts'). '&url=' . $postUrl . '&via=heyboardman';

 		$breadcrumbs['share_fb'] = 'https://www.facebook.com/sharer/sharer.php?u=' . $postUrl;

 		$breadcrumbs['description'] = $desc;

 		return $breadcrumbs;
 	}

 	/**
 	 * @description: check if post schedule is due.
 	 *
 	 * @param: {date}:String. Post date to check
 	 *
 	 * @return Boolean. true if post date is still in the future
 	 **/
 	
 	private static function afterToday ($date) {

 		return new DateTime("now") < new DateTime($date);
 	}


 	// gets the last ID in a given table and increments it by 1
	public static function dbLastItem ($conn, $table, $columnName='id') {

		$cache = TilwaGet::cacheManager();

	    $cachedData = $cache->getItem(__FUNCTION__.'|'.$table);

	    $itemId = $cachedData->get();


	    if (is_null($itemId )) {

	 		$total = $conn->prepare('SELECT `'. $columnName. "` FROM $table ORDER BY `". $columnName."` DESC LIMIT 1");

	 		$total->execute();

	 		$itemId = intval($total->fetch(PDO::FETCH_ASSOC)[$columnName])+1;

	 		$cachedData->set($itemId)->expiresAfter(600);
	 	}

	 	return $itemId;
	}
 }

?>
<?php

namespace Post;

use \PDO;

use \Get\TilwaGet;
/**
 * 
 */
class TilwaPost extends PostController {

	private static $accessLevel = [
		'page'=> ['admin', 'juniorAdmin'],

		'testimonial'=> ['admin', 'juniorAdmin'],

		'currency' => ['admin'],

		'account-man' => ['admin', 'juniorAdmin'],

		'broadcast' => ['admin'],

		'approve-trans' => ['admin', 'juniorAdmin'],

		'vtu-data' => ['admin', 'juniorAdmin'],

		'coupon' => ['admin'],

		'approve-test' => ['admin', 'juniorAdmin']
	];
	
	
	// manipulate received files as desired before uploading
	public static function fileUpload ($x=null,$y=null ) {

		$userId = $_SESSION['id'];

		// change file names to reflect current user
		$dirs = [];

		$newFiles = array_map(function ($fileObj) use ($userId, &$dirs) {

			$picContext = array_search($fileObj, $_FILES);

			$dirs[] = "../assets/imgs/$picContext/";

			$fileObj['name'] = "$userId.png";

			return $fileObj;
		}, $_FILES);

		parent::fileUpload($newFiles, $dirs);
	}

	public static function create (PDO $conn, array $rawData) {

		session_start();

		$userId = $_SESSION['id'];

		$mode = $_GET['action']; // url slug

		$lock = @self::$accessLevel[$mode];

		$userData = json_decode(TilwaGet::getContents($conn, $userId), true);


		if (isset($userId) && (empty($lock) || in_array($userData['role'], $lock))) {

			if (array_key_exists('dashboard', $rawData)) $rawData = json_decode($rawData['dashboard'], true);

			$queriesMap = [
				'civili-testimonial'=> 'INSERT INTO testimonials (`fromId`,`content`,`date`) VALUES (?,?,?)',

				'vtu-item' => 'INSERT INTO `vtu-data-catalog` (packageValue,network,price,validity,visible) VALUES (:packageValue,:network,:price,:validity,:visible)',

				'currency' => 'INSERT INTO currencies (currencyFrom, currencyTo, minimumTransactionCost, maximumTransactionCost, approvalMode, priceToday, visible) VALUES (:currencyFrom, :currencyTo, :minimumTransactionCost, :maximumTransactionCost, :approvalMode, :priceToday, :visible)'
			];

			$dataToInsert = [
				'civili-testimonial'=> [$userId, @$rawData['content'], date('Y-m-d H:i:s')],

				'vtu-item' => array_filter($rawData, function ($k) {return $k !== 'id';}, ARRAY_FILTER_USE_KEY)
			];


			if (isset($queriesMap[$mode])) return $conn
				->prepare($queriesMap[$mode])

				->execute($dataToInsert[$mode] ?? $rawData);

			elseif (
				($createInstance = new \CreateRequests($conn, $rawData))

				&& method_exists($createInstance, $mode)
			) {

				return $createInstance->$mode( $userData);
			}

			return 0;
		}

		return 0;
	}

	
	public static function modify (PDO $conn, array $rawData) {

		session_start();

		$userId = $_SESSION['id'];

		if (isset($userId)) {

			$modifyInstance = new \ModifyRequests($conn);

			$mode = $_GET['action']; // url slug

			$userData = json_decode(TilwaGet::getContents($conn, $userId), true);
			

			if (array_key_exists('dashboard', $rawData)) $rawData = json_decode($rawData['dashboard'], true);


			elseif (array_key_exists('content', $rawData)) $rawData['content'] =  htmlentities($rawData['content']);

			$dataToInsert = [

				'civili-user' => array_merge($rawData, ['userId' => $userId]),

				'approve-trans' => array_merge($rawData, ['approvedBy' => $userId, 'approved' => 1])
			];

			$role = $userData['role'];

			$lock = @self::$accessLevel[$mode];


			// if it's not set, this path is free for all. if it's set, confirm sufficient permission
			if (empty($lock) || in_array($role, $lock)) {

				if ($mode == 'approve-trans') if (!$modifyInstance->approveTrans($rawData)) return 0;

				$conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, true); // using this because pdo complains about queries reusing named parameters


				if (($query = @$modifyInstance->queriesMap[$mode]) && !is_null($query)) return $conn->prepare($query)->execute($dataToInsert[$mode] ?? $rawData);

				return 1;
			}

			return 0;
		}
		return 0;
	}
	
	public static function buyData (PDO $conn, $rawData) {

		session_start();

		$rawData = $rawData['data']; $mode = $_GET['action']; $network = $rawData['ntw'];

		
		$price = $packageValue = $rawData['pkv'];


		if ($mode == 'data') {

			$price = $conn->prepare('SELECT price FROM `vtu-data-catalog` WHERE packageValue=? AND network=?');

			$price->execute([$packageValue, $network]);

			$price = $price->fetch()['price'];
		}

		$userData = json_decode(TilwaGet::getContents($conn, $_SESSION['id']), true);

		$phone = $userData['phone'];

		$formatRaw = [
			'msisdn'=> $phone, 'networkid'=> $network,

			'amount'=> $price, 'type'=> $this->rawData['type']
		];

		$airvendInstance = new AirvendAccessor($formatRaw, $mode);

		$responseArr = $airvendInstance->airtimeData();

		if ($responseArr['message']['ResponseMessage'] == 'SUCCESS') {

			$dataToInsert = [
				'initiator' => $_SESSION['id'], 'approved' => 1,

				'currencyFrom' => $packageValue, 'currencyTo' => $network,

				'mode' => $mode, 'amount' => $price,

				'vendorResp' => $responseArr['message']
			];

			$createInstance = new CreateRequests($conn, $dataToInsert);

			return $createInstance->transaction( $userData);
		}

		else return 0;
	}
	
	/**
	 * @description: sets $_SESSION['trade_notification'] for the GET postback to output
	 *
	 * @return on success: 1
	 airvend request failure: 0
	 invalid request: -1
	 **/
	public static function utilities (PDO $conn, $rawData) {

		session_start();

		$airvendMode = $rawData['multi_mode'];

		$action = $_GET['action'];

		$handler = TilwaGet::nameDirty($action, 'camel-case');

		$airvendInstance = new AirvendAccessor($rawData, $airvendMode);


		if (method_exists($airvendInstance, $handler)) {

			$responseArr = $airvendInstance->$handler();

			if ($responseArr['message']['ResponseMessage'] == 'SUCCESS') {

				$dataToInsert = [
					'initiator' => $_SESSION['id'], 'approved' => 1,

					'currencyFrom' => $airvendMode,

					'currencyTo' => '', 'mode' => 'buy',

					'amount' => $responseArr['amount'],

					'vendorResp' => $responseArr['message']
				];

				$createInstance = new CreateRequests($conn, $dataToInsert);
				
				$createInstance->transaction( $userData, '?cat=mcf&mode='. $airvendMode);
			}

			return 0;
		}

		return -1;
	}

	/**
	 * @description: sets the balance of admin, initiator and their referrer (if any) based on $opts[mode]
	 *
	 * @param {opts}: must contain the folowing keys --> mode, userData, transactionData
	 *
	 * @return {Boolean}
	 **/
	public static function setBalances (PDO $conn, array $opts) {

		require '../auth/masterId.php';

		$referrer = $opts['userData']['referredBy']; $amount = +$opts['transactionData']['amount'];

		
		$enoughBalance = true; $userNewBalance = $opts['userData']['balance'];

		
		$referrersBalance = null;

		
		$adminAcc = json_decode(TilwaGet::getContents($conn, $masterCred), true);

		$adminBalance = $adminAcc['balance'];


		if (!is_null($referrer)) {

			if (($cxtAccount = TilwaGet::getContents($conn, $referrer)) && !is_null($cxtAccount)) {

				$cxtAccount = json_decode($cxtAccount, true);

				$referrersBalance = $cxtAccount['balance'];
			}
		}


		if ($opts['mode'] == 'buy' ) {

			if (!is_null($referrersBalance)) {
				$contend = $amount * .25;

				$referrersBalance += $contend;

				$amount -= $contend;
			}

			$userNewBalance -= $amount ;

			$adminBalance += $amount;

			$enoughBalance = $userNewBalance > 0;
		}

		elseif ($opts['mode'] == 'sell') {

			if (!is_null($referrersBalance)) {
				$contend = $amount * .15;

				$referrersBalance += $contend;

				$amount -= $contend;
			}

			$userNewBalance += $amount ;

			$adminBalance -= $amount;

			$enoughBalance = $adminBalance > $amount;
		}

		if (!$enoughBalance ) return 0;

		$dataToUpdate = [
			[$userNewBalance, $opts['userData']['email']],

			[$adminBalance, $adminAcc['email']]
		];

		if (!is_null($referrersBalance)) $dataToUpdate[] = [$referrersBalance, $referrer];


		$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		try {

			$conn->beginTransaction();

			$linkedTrans = $conn->prepare('UPDATE users SET balance =? WHERE email=?');
			
			foreach ($dataToUpdate as $arr) $linkedTrans->execute($arr);

			return $conn->commit();
		}

		catch(Exception $e) {

			$conn->rollback();

			return 0;
		}
	}

	private static function updateTransInDb(string $vendorString) {}
}
?>
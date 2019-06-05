<?php

use \Get\TilwaGet;

use \Post\TilwaPost;

use \Monolog\Logger;

use \Monolog\Handler\NativeMailerHandler;

use \Monolog\Formatter\HtmlFormatter;

/**
 * helper class containing methods called from the `create` method on the post controller
 */
class CreateRequests {
	
	private $conn;

	private $payload;

	function __construct(PDO $conn, array $payload ) {

		$this->conn = $conn;

		$this->payload = $payload;
	}

	public function broadcast () {

		$allUsers = $this->conn->prepare('SELECT email FROM users');	

		$allUsers->execute();

		$allUsers = $allUsers->fetchAll(PDO::FETCH_ASSOC);

		$receipients = implode(',', array_column($allUsers, 'email'));

		$headers  = 'MIME-Version: 1.0' . "\r\n";
		$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";

		//$headers .= 'To: Mary <mary@example.com>, Kelly <kelly@example.com>' . "\r\n";
		$headers .= 'From: Tilwa Admin <no-reply@tilwa.com>' . "\r\n";

		mail($receipients, $this->payload['subject'], $this->payload['content'], $headers);

	}


	public function coupon () {

		$code = substr(md5(microtime()),rand(0,26),7); // md5 converts the microtime to a string

		$insert = $this->conn->prepare('INSERT INTO coupons (`name`,`mode`,`amount`) VALUES (:name,:mode,:amount)')->execute(array_merge($this->payload, ['name' => $code]));

		if ($insert) return json_encode(['code' => $code, 'time' => date('Y-m-d H:i:s')]);

		return 0;
	}

	
	/**
	 * @description: internally calls setBalance for you and sets session accordingly
	 *
	 * @param {userData} details about current signed in user
	 * @param {dest} get param to tack onto redirect url
	 *
	 * @return {void} will redirect to referer on successful db insertion. no redirect means failure and will indicate as such in session[trade_notification]
	 **/
	public function transaction (array $userData, $dest ='') {

		require '../auth/masterId.php';


		$payload = $this->payload;

		$mode = strtolower($payload['mode']);

		$userTransacLevel = true;

		$failResp = '<p class="trade-failure">Failed to process your transaction!</p>';

		$succResp = '<p class="trade-success">Your '. $mode . ' transaction was successfull.';

		$query = 'INSERT INTO transactions (initiator,currencyFrom,currencyTo,mode,amount,approved)VALUES (:initiator,:currencyFrom,:currencyTo,:mode,:amount,:approved)';


		$appSettings = $this->conn->prepare('SELECT * FROM settings');

		$appSettings->execute();

		$appSettings = $appSettings->fetch(PDO::FETCH_ASSOC);

		if ($appSettings[$mode] != 'none') {

			foreach (explode(',', $appSettings[$mode]) as $value) {
				
				if ($userTransacLevel) {
					switch ($value) {

						case 'photoIds':
							$userTransacLevel = file_exists("../assets/imgs/$value/". $userData['userId'] . ".png");
						break;

						case 'utilityBills':
							$userTransacLevel = file_exists("../assets/imgs/$value/". $userData['userId'] . ".png");
						break;

						case 'admin':// if admin or junior
							$userTransacLevel = preg_match('/admin/i', $userData['role']);
						break;

						case 'juniorAdmin':
							$userTransacLevel = $userData['role'] == 'juniorAdmin';
						break;
					}
				}
			}
		}


		// admin can't sell or buy from himself
		if ($userTransacLevel == true && $userData['userId'] !== $masterCred ) {

			// consume crypto api IF TRANSACTION IS OF THAT TYPE

			// THEN

			// handle coupon codes
			if (isset($payload['coupon_code'])) {

				$couponDetails = TilwaGet::getContents($this->conn, $payload['coupon_code']);

				if (!is_null($couponDetails)) {

					$couponDetails = json_decode($couponDetails, true);
					
					if ($couponDetails['mode'] == 'percentage') $payload['amount'] -= +$couponDetails['amount']/100 * $payload['amount'];

					elseif (in_array($couponDetails['mode'], ['crypto', 'Naira']) ) $payload['amount'] -= $couponDetails['amount'];
				}
			}

			$dataToInsert = array_merge($payload, ['initiator' => $userData['userId'], 'approved' => 1]);

			$approvalMode = json_decode(TilwaGet::getContents($this->conn, $payload['currencyFrom']), true)['approvalMode'];

			$isManual = $approvalMode == 'manual';


			if ($isManual) $dataToInsert['approved'] = 0;

			else $dataToInsert['approved'] = 1;


			$opts = ['mode' => $mode, 'userData' => $userData, 'transactionData' => $payload];


			unset($dataToInsert['coupon']);

			if (
				TilwaPost::setBalances($this->conn, $opts)

				&& $this->conn->prepare($query)->execute($dataToInsert)
			) {
				if ($isManual) $_SESSION['trade_notification'] = '<p class="trade-pending">Your '. $mode . ' transaction will be processed in a few moments.</p>';

				else {

					if (($isPostpaid = @$payload['vendorResp']['vendData']['creditToken']) && !is_null($isPostpaid)) $succResp .= ' Your token is ' . $isPostpaid;

					$_SESSION['trade_notification'] = $succResp . '</p>';
				}
			}

			else $_SESSION['trade_notification'] = $failResp;
		}

		else $_SESSION['trade_notification'] = $failResp;

		header('Location: ' . $_SERVER['HTTP_REFERER'] . $dest);
	}
}

?>
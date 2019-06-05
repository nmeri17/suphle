<?php

/**
 * @description: receives a form supplied via POST, verifies the validity of the data and if correct, dispenses service to the user's supplied account
 *
 * @return Array or -1, depending on last operation's status. the front facing/verification methods all attempt to return a consistent array with keys -> message (full vendor response), amount OR -1 ON INVALID USER CREDENTIALS
 */
class AirvendAccessor {

	private $payload;

	private $mode;

	function __construct(array $payload =[], string $mode='') {

		$this->payload = $payload;

		$this->mode = $mode;
	}

	public function purchaseElectric () {

		$typeMap = [
			'ikeja' => ['prepaid'=> 11,'postpaid' => 10],
			'ibadan' => ['prepaid'=> 12,'postpaid' => 23],
			'phc' => ['prepaid'=> 16,'postpaid' => 15],
			'eko' => ['prepaid'=> 14,'postpaid' => 13],
			'enugu' => ['prepaid'=> 21,'postpaid' => 22],
		];

		$vNameMap = [
			'ikeja' => 'name', 'ibadan' => 'firstName', 'phc' => 'customerName', 'eko' => 'firstName', 'enugu' => 'name'
		]; // they all return different fields for name

		$payload = $this->payload;

		$location = $payload['location'];

		$postQuery = 'type='. $this->payload['type']. '&account='. $payload['account'];

		$verifyElectric = $this->airvendRequest($postQuery, 'vas/electricity/verify/');


		$respArr = $this->xmlToJson($verifyElectric)['verify']['details'];

		$name = $respArr[$vNameMap[$location]];

		if (!empty($respArr['canVend'])) {

			$postQuery .= '&amount='. $payload['amount'] . '&customerphone='. $payload['phone'];

			
			// location specific fields
			$toIncl = [
				'ikeja'=>['customerdtnumber' => 'customerDtNumber'],

				'phc'=> ['uniqueref' => 'uniqueReference']
			];

			if (in_array($location, array_keys($toIncl))) {

				$postQuery .= '&customeraddress='. $respArr['customerAddress'] ?? $respArr['address'];
				
				foreach ($toIncl[$location] as $param => $val) $postQuery .= "&$param=$val";
			}

			$verifiedElectric = $this->airvendRequest($postQuery, 'electricity/');
			

			return [
				'message'=> $this->xmlToJson($verifiedElectric)['VendResponse'],

				'amount' => $payload['amount']
			];
		}

		return -1;
	}

	public function purchaseMulti () {

		$payload = $this->payload;

		$mode = $this->mode;

		if ($mode == 'dstv') $postQuery = 'customer='. $payload['customer_id'];

		elseif (in_array($mode, ['startimes', 'gotv']) ) $postQuery = 'smartcard='. $payload['customer_id'];


		$verifyMultiResponse = $this->airvendRequest($postQuery, $mode. '/verify/'); 

		$verifyMultiResponse = json_decode($verifyMultiResponse, true);

		$verifyData = $verifyMultiResponse["details"];


		if ($verifyData["customerNumber"] == $payload['customer_id']) {

			if ($mode == 'dstv') {

				// get price from aggregate method
				$additionalOpts = ['code' => $payload['product_code'], 'mode' => $mode];

				$respVars = $this->nestedAggregate( $additionalOpts);

				$respVars = json_decode($respVars, true)['details']['items'];
			}

			$amount = array_filter($respVars, function ($arr) use ($payload) {

				return $payload['product_code'] == $arr['code'];
			})[0]['price'];


			// actual Payment Request
			$postQuery = http_build_query([
				'customerNumber'=> $payload['customer_id'],

				'invoicePeriod' => $verifyData['invoicePeriod'],

				'amount' => $amount,

				'customerName' => $verifyData['firstName'].$verifyData['lastName']
			]);
			
			
			$verifiedMulti = $this->airvendRequest($postQuery, $mode);
			
			return [
				'message'=> $this->xmlToJson($verifiedMulti)['VendResponse'],

				'amount' => $amount
			];
		}

		return -1;
	}

	public function purchaseSmile () {

		$verifySmileResponse = $this->airvendRequest('account='. $this->payload['customer_id'], 'smile/verify/'); 

		$verifySmileResponse = json_decode($verifySmileResponse, true);


		if (!empty($verifySmileResponse["details"]["firstName"])) {

			// get price
			$respVars = $this->airvendRequest('&account='.$this->payload['customer_id'], $this->mode.'/product');

			$respVars = json_decode($respVars, true)['details']['bundles'];

			$payload = $this->payload;

			$amount = array_filter($respVars, function ($arr) use ($payload) {

				return $payload['product_code'] == $arr['typeCode'];
			})['price'];


			// actual Payment Request
			$postQuery = http_build_query([
				'account'=> $this->payload['customer_id'],

				'bundleCode' => $this->payload['product_code'],

				'amount' => $amount,

				'quantity' => 1
			]);
			
			
			$verifiedSmile = $this->airvendRequest($postQuery, $this->mode);
			
			return [
				'message' =>$this->xmlToJson($verifiedSmile)['VendResponse'],

				'amount' => $amount
			];
		}
	}

	public function waecCards () {

		$amount = $this->airvendRequest('', "waec/product/")['details']['pinValues'][0]['amount'];
		
		$postQuery = http_build_query([
			'pins'=> $this->payload['amount'],

			'pinValue' => +$amount,

			'amount' => +$amount * +$this->payload['amount'],
		]);

		$waecResp = $this->airvendRequest($postQuery, 'waec');		
		
		return [
			'message' => $this->xmlToJson($waecResp)['VendResponse'],

			'amount' => $amount
		];
	}

	public function airtimeData () {

		$adResp = $this->airvendRequest(http_build_query($this->payload), 'vtu');
		
		return [
			'message' => $this->xmlToJson($adResp)['VendResponse'],

			'amount' => $this->payload['amount']
		];
	}

	// returns xml or json depending on the endpoints response
	public function airvendRequest ( string $query, string $endpoint):string {
		
		$baseQuery = http_build_query([ 'username' => 'halavas', 'password' => 'test666']);

		$opts = array('http' =>
		   ['method'  => 'POST',
		        'header'  => 'Content-type: application/x-www-form-urlencoded',
		        'content' => rtrim($baseQuery . '&'.$query, '&')
		    ]
		);

		$context  = stream_context_create($opts);

		return file_get_contents('https://api.airvendng.net/'. $endpoint, false, $context);
	}

	/**
	* @description: make requests to APIs for data that wasnt fetched in one scoop
	* @param: {dataArgs} -> relevant keys (code, mode) to aid our query for additional data
	*
	* @return json string housing further details about a resource
	*
	*/
	public function nestedAggregate (array $dataArgs) {

		return $this->airvendRequest('&productcode='. $dataArgs['code'], $dataArgs['mode'].'/product/');
	}

	private function xmlToJson($xmlString) {

		$xml = simplexml_load_string($xmlString); // turn the string into an array

		$json = json_encode($xml);

		return json_decode($json,TRUE);
	}
}
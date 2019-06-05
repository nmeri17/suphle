<?php

	use \Get\TilwaGet;

	use \Post\TilwaPost;

/**
 * helper class for post controller invoked from the modify method
 */
class ModifyRequests {

	public $queriesMap = [
		'page'=> 'UPDATE page SET name=:name,content=:content,description=:description,title=:title,custom_scripts=:custom_scripts,keywords=:keywords WHERE name=:name',

		'testimonial'=> 'UPDATE testimonials SET approved =:approved WHERE fromId=:fromId',

		'currency' => 'UPDATE ecurrencies SET minimumTransactionCost=:minimumTransactionCost,maximumTransactionCost=:maximumTransactionCost,priceToday=:priceToday,visible=:visible,approvalMode=:approvalMode WHERE currencyFrom=:currencyFrom',

		'civili-user' => 'UPDATE users SET bankName=:bankName,accountNumber=:accountNumber WHERE userId=:userId',

		'account-man' => 'UPDATE users SET role=:role WHERE userId=:userId',

		'approve-trans' => 'UPDATE transactions SET approved=:approved,approvedBy=:approvedBy WHERE id=:id',

		'settings' => 'UPDATE settings SET buy=:buy,sell=:sell,data=:data,airtime=:airtime',

		'vtu-item' => 'UPDATE `vtu-data-catalog` SET packageValue=:packageValue,network=:network,price=:price,validity=:validity,visible=:visible WHERE id=:id'
	];

	private $conn;

	function __construct(PDO $conn)
	{
		$this->conn = $conn;
	}

	// return result of last db op
	public function approveTrans (array $rawData) {

		$initiatorObj = $this->conn->prepare('SELECT initiator,amount,mode FROM transactions WHERE id=?');

		$initiatorObj->execute([$rawData['id']]);

		$initiatorObj = $initiatorObj->fetch(PDO::FETCH_ASSOC);


		$userData = json_decode(TilwaGet::getContents($this->conn, $initiatorObj['initiator']), true);

		$opts = ['mode' => $initiatorObj['mode'], 'userData' => $userData, 'transactionData' => $initiatorObj];

		return TilwaPost::setBalances($this->conn, $opts);
	}
}
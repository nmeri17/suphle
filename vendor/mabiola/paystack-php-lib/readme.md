# PHP Library For [Paystack.co](http://paystack.co "Paystack.co")  (Unofficial) #
A PHP library for Paystack.

###Latest Version###
	1.0.0
[![Build Status](https://travis-ci.org/MalikAbiola/paystack-php-lib.svg?branch=master)](https://travis-ci.org/MalikAbiola/paystack-php-lib)
[![Coverage Status](https://coveralls.io/repos/github/MalikAbiola/paystack-php-lib/badge.svg?branch=master)](https://coveralls.io/github/MalikAbiola/paystack-php-lib?branch=master)
# Requirements #

 - PHP 5.5+
 - [Composer](https://getcomposer.org/doc/00-intro.md "Composer")

# Installation #

### Via Composer ###
Add the following to your `composer.json` file and run `composer install`

	"mabiola/paystack-php-lib" : "~1.0"

Then use composer's autoload.

	require_once __DIR__ . '/vendor/autoload.php';

**NOTE:** if you are using a PHP Framework for example, [Laravel](https://laravel.com/), you do not need to add the composer autoload to your file(s) as it is already done. (see it in `bootstrap/autoload`; `bootstrap/app.php` for Lumen; ).

### Other Installation Methods ###

**No other installation methods! Use composer!**

**Why?**

- It's doing PHP the right way!
- It's the right thing to do.
- If you still need convincing, [this](http://blog.nelm.io/2011/12/composer-part-1-what-why/) might help.

Seriously, please... use composer. Thank you.

# Configurations #

Add the following keys to your .env file.

    #PAYSTACK LIB MODE [test | live]
    PAYSTACK_MODE = test
    #YOUR PAYSTACK KEYS
    PAYSTACK_LIVE_PUBLIC_KEY = my_paystack_live_public_keys
    PAYSTACK_LIVE_SECRET_KEY = my_paystack_live_secret_key
    PAYSTACK_TEST_PUBLIC_KEY = my_paystack_test_public_key
    PAYSTACK_TEST_SECRET_KEY = my_paystack_test_secret_key

Replace the keys with your actual Paystack keys - you can find this on the `Developer/API` panel of your `settings` page. Use the `PAYSTACK_MODE` to switch between `live` and `test` mode for Paystack.

That's is! You are ready to receive payments!

# Usage #

Using the library is simple, make a Paystack Library object and use this object to perform operations on Paystack.
To create the Paystack Library object, do;

	$paystackLibObject = \MAbiola\Paystack\Paystack::make();

or if you'd rather provide the exact key (if you are not using an env file);

    $paystackLibObject = \MAbiola\Paystack\Paystack::make("my-paystack-private-key");

Now lets walk through some of the operations you can perform with the object you just created.

1. **Initialize a One Time Transaction**

	According to [Paystack's documentation](https://developers.paystack.co/docs/), to charge a customer, you create a one time transaction for which you get an authorization url which you redirect your page to so that your customer can enter their card details and pay for your service(s). To do this with the library, pass the amount to be charged, the customer email, and the optional plan (if this is a transaction to create a subscription. you can either enter the plan code here or the plan object - more on this coming soon).

		$getAuthorization = $paystackLibObject->startOneTimeTransaction('10000', 'me@me.com');

	You will expect an array that contains the authorization url `authorization_url` to redirect to to accept this payment, and the unique auto-generated transaction reference `reference`.

2. **Verify Transactions**

	To verify a transaction, simply call the function like;

		$verifyTransaction = $paystackLibObject->verifyTransaction('unique_transaction_ref');

	if transaction is successful, this function returns an array containing the transaction details else ` $verifyTransaction ` will be ` false `.

3. **Charging Returning Customers**

	Now, when you successfully charge a customer, an authorization key that represents the card of the customer is generated - you can find this in the array you get back when you verify a transaction. Therefore, the next time you want to charge this customer, you can use this authorization code to charge said customer. To do this, just call the function like;

		$chargeReturningCustomer = $paystackLibObject->chargeReturningTransaction('authorization_code', 'me@me.com', '10000');

	if transaction is successful, this function returns an array containing the transaction details.

4. **Customer**

	- **Retrieve Customer Data**

		You can retrieve customer details by passing the customer code to the `getCustomer` to get a customer object.


			$customer = $paystackLibObject->getCustomer('customer_code');

		If the operation is successful, you get a customer object which you can call a `$newCustomer->toArray()` to get the details as an array or you can do a `get` passing an attribute to retrieve, or a list of attributes  as arguments or an array of attributes. e.g. `$newCustomer->get(['first_name', 'customer_code', 'subscriptions', 'authorizations']);` or `$newCustomer->get('subscriptions');`

	- **Create Customer**

		To create a customer, pass the customer first name, last name, email and phone to the `createCustomer` method, like;


			$newCustomer = $paystackLibObject->createCustomer('first_name', 'last_name', 'email', 'phone');

		If the operation is successful, a customer object is returned.

	- **Update Customer Data**

		You can update the customer details by passing the customer code and update data as an array with attributes to update as keys and the update value as the value to the `updateCustomerData` method, like;

			$updatedCustomer = $paystackLibObject->updateCustomerData('customer_code',['last_name' => 'new_last_name']);

		If the operation is successful, the customer object is returned.

	- **Retrieve All Customers**

		To retrieve all your customers, call the `getCustomers` method on the PaystackLibObject. Expect an array of customer objects.

			$myCustomers = $paystackLibObject->getCustomers();

5. **Plans**

	- **Retrieve Plan Details**

		You can retrieve the details of a plan by passing the plan code to the `getPlan` to get a plan object.


			$plan = $paystackLibObject->getPlan('plan_code');

		If the operation is successful, you get a plan object which you can call a `$plan->toArray()` on to get the details as an array or you can do a `get`, passing an attribute to retrieve, or a list of attributes  as arguments or an array of attributes. e.g. `$plan->get(['name', 'plan_code', 'subscriptions', 'hosted_page_url']);` or `$plan->get('subscriptions');`

	- **Create A New Plan**

		To create a plan, pass the plan's name, description, amount (not in kobo apparently) and the currency (NGN | USD) to the `createPlan` method, like;


			$newPlan = $paystackLibObject->createPlan('Random_Plan_1000', 'Random 1000NGN Plan', '1000', 'NGN');

		If the operation is successful, a plan object is returned.

	- **Update Plan Data**

		You can update the plan details by passing the plan code and update data as an array with attributes to update as keys and the update value as the value to the `updatePlan` method, like;

			$updatedPlan = $paystackLibObject->updatePlan('plan_code', ['hosted_page_url' => 'http://somerandomu.rl', 'hosted_page' => true]);

		If the operation is successful, the plan object is returned.

	- **Retrieve All Plans**

		To retrieve all your plans, call the `getPlans` method on the PaystackLibObject. Expect an array of plans objects.

			$myPlans = $paystackLibObject->getPlans();

6. **Other Transactions Operations**

	- **Get Details of A Transaction**
		To get the details of a transaction, pass the transaction id to the `transactionDetails` function. Expect a transaction object on success or a thrown exception. And as usual you can perform the `toArray` and `get` operations on it as you can on the customer and plan objects. Also, you can call `verify()` on this object to verify the transaction.

			$transactionDetails = $paystackLibObject->transactionDetails('transaction_id');

	- **Get All Transactions**
		To retrieve all transactions, call the `allTranactions` function on the paystack library object. An array of transaction objects is returned on success or an exception thrown on error.

			$allMyTransactions = $paystackLibObject->allTransactions();

	- **Transaction Totals**
		To get a cummulative view of your successful transactions, use the `transactionTotals` function. An array with `total_volume`, `total_transactions`, and `pending_transfers` as keys is returned. or ofcourse, an exception when something goes wrong.

			$totals = $paystackLibObject->transactionsTotals();

7. **Exceptions**

	Errors are bound to occur, but not to worry, the library contain descriptive exceptions and methods/functions to get the error details. To get the error message when an exception is thrown, call `getErrors()` on the exception object. e.g.

		try {
			$paystackLibObject->getPlan('plan_code');
		} catch (PaystackNotFoundException $e) {
			print_r($e->getErrors());
		}

	Possible Exceptions;

	- **PaystackInternalServerError**
	- **PaystackInvalidTransactionException:** Thrown when a unique transaction reference could not be generated.
	- **PaystackNotFoundException:** Thrown when the requested object/resource can not be found
	- **PaystackUnauthorizedException:** Thrown when the authorization keys can not be found.
	- **PaystackUnsupportedOperationException:** Thrown when the operation you are trying to perform is not supported by Paystack.
	- **PaystackValidationException:** Thrown when validation errors occur. You can view validation errors by calling `getValidationErrors()` on the exception object. `getValidationErrors()` returns an array with attributes failing validation and the reasons.

# Contributing #

I very much welcome your contributions, fork and send me a pull request. Remember to write tests. Or you can open issues to report bugs.

Also, if you like this library, star the repo.  Or if you have questions or just want to give me a shout, you can reach me on [twitter](https://twitter.com/MalikAbiola_)

# License #

MIT.

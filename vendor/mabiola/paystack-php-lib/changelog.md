# Change Log #

##Version##
    1.0.0

###Changes###
 - Allow paystack keys to be specified when creating Paystack Library Object, so you can do;

    $paystackLibObject = \MAbiola\Paystack\Paystack::make("my-paystack-private-key");

 - Allow CURL SSL verification to be disabled during local test mode

 
##Version##
    1.0.1

###Changes###
 - Fix issue with transaction reference generation by adding dependency that could be causing issue
 - made changes to resource class because of Paystack's API change in plan update response
 - fixed test

___
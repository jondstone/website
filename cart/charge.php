/******* Notes ******/
Here, I set my Discount Code in a SHA1 hash, then pass in the input from the submitted form.
I attempted to create a method to sanitize the input, which failed; so I chose to do each string individually. 
Then I'm checking to make sure the user has not manipulated the JS on the front end, by setting the Discount Code to "true" without actually knowing it. If that is the case, I am notified and the order is cancelled. In addition the user will be redirected to a "nice try" page.
Else, it creates a customer record in Stripe, and then passes the data to their API to charge the card.
The charge method is in an extensive try/catch as I wanted to make sure the user knew why the charge failed, without an generic error message.
If there is an error, it will redirect to error.php, which will then display the unique error message.
Else, it redirects to an order confirmation page and generates an order confirmation email to me and the user.
/******* Notes ******/

<?php

//store and pass the discount code
$discount_code_hash = 'hashfordiscounthere'; //15% off

//set all variables and sanitize inputs
$firstName = $_POST['firstName'];
$lastName = $_POST['lastName'];
$customerEmail = $_POST['email'];
$customerAddress = $_POST['Address'];
$customerCity = $_POST['City'];
$customerState = $_POST['State'];
$customerZip = $_POST['Zip'];
$notes = $_POST['Notes'];
$discountCode = $_POST['discount_code'];
$discountExist = $_POST['discountExist'];
$items = $_POST['cartItems'];
$total = $_POST['totalAmount'];
$token = $_POST['stripeToken'];

$s_firstName = filter_var($firstName, FILTER_SANITIZE_STRING);
$s_lastName = filter_var($lastName, FILTER_SANITIZE_STRING);
$customerName = $s_firstName . ' ' . $s_lastName;
$s_customerEmail = filter_var($customerEmail, FILTER_SANITIZE_STRING);
$s_customerAddress = filter_var($customerAddress, FILTER_SANITIZE_STRING);
$s_customerCity = filter_var($customerCity, FILTER_SANITIZE_STRING);
$s_customerState = filter_var($customerState, FILTER_SANITIZE_STRING);
$s_customerZip = filter_var($customerZip, FILTER_SANITIZE_STRING);
$s_notes = filter_var($notes, FILTER_SANITIZE_STRING);
$s_discountCode = filter_var($discountCode, FILTER_SANITIZE_STRING);
$s_discountExist = filter_var($discountExist, FILTER_SANITIZE_STRING);
$s_items = filter_var($items, FILTER_SANITIZE_STRING);
$s_total = filter_var($total, FILTER_SANITIZE_STRING);

//declare TOTAL price for Stripe where 170000 => 1700.00
$stripeTOTAL = $s_total . '00';
$customerTOTAL = '$' . $s_total;

//verify if Discount Code returns true hash = code, or, Discount code is null or incorrect and Discount Exists returns false
//Else, someone is being shady and manipulating the JS, kick order out and inform them of such
if (((sha1($s_discountCode) === $discount_code_hash) && ($s_discountExist == 'true')) || (($s_discountCode == '') && ($s_discountExist == 'false')) || ((sha1($s_discountCode) != $discount_code_hash) && ($s_discountExist == 'false'))) {
    
    //Generate email to myself before calling Stripe's API
    //Doing this to see potential orders if Stripe fails to charge
    sendInitialEmail($customerName, $s_customerAddress, $s_customerCity, $s_customerState, $s_customerZip, $s_customerEmail, $s_items, $s_notes, $s_discountCode, $s_discountExist, $customerTOTAL);

    /******* Make Initial Calls and Set Keys ******/
    require_once('stripe-php/init.php');
    $stripe = [
      "secret_key"      => "secretKey",
      "publishable_key" => "publishableKey",
    ];
    \Stripe\Stripe::setApiKey($stripe['secret_key']);

    //Create Stripe Customer
    $customer = \Stripe\Customer::create([
        'email' => $customerEmail,
        'source' => $token,
    ]);
    /******* Make Initial Calls and Set Keys ******/

    //Charge Customer
    try {
        $charge = \Stripe\Charge::create([
            'customer' => $customer->id,
            'amount'   => $stripeTOTAL,
            'currency' => 'usd',
        ]);

        //Store Charge ID to pass into sendOrderEmail
        $transID = $charge->id;

        //Send Confirmation email to customer and myself
        sendOrderEmail($customerName, $s_customerAddress, $s_customerCity, $s_customerState, $s_customerZip, $s_customerEmail, $transID, $customerTOTAL);

        //success page
        header('Location: orderconfirmation.php?tid='.$transID);

    } catch(\Stripe\Error\Card $e) {
      // Since it's a decline, \Stripe\Error\Card will be caught
      /*$body = $e->getJsonBody();
      $err  = $body['error'];

      print('Status is:' . $e->getHttpStatus() . "\n");
      print('Type is:' . $err['type'] . "\n");
      print('Code is:' . $err['code'] . "\n");
      // param is '' in this case
      print('Param is:' . $err['param'] . "\n");
      print('Message is:' . $err['message'] . "\n");
        */
      $errorMessage = 'declinedcard';
      header('Location: error.php?id='.$errorMessage);
    } 
    catch (\Stripe\Error\RateLimit $e) {
      // Too many requests made to the API too quickly
      $errorMessage = 'ratelimit';
      header('Location: error.php?id='.$errorMessage);
    } 
    catch (\Stripe\Error\InvalidRequest $e) {
      // Invalid parameters were supplied to Stripe's API
      $errorMessage = 'invalidrequest';  
      header('Location: error.php?id='.$errorMessage);
    } 
    catch (\Stripe\Error\Authentication $e) {
      // Authentication with Stripe's API failed
      // (maybe you changed API keys recently)
      $errorMessage = 'autherror';
      header('Location: error.php?id='.$errorMessage);
    } 
    catch (\Stripe\Error\ApiConnection $e) {
      // Network communication with Stripe failed
      $errorMessage = 'apiconnection';    
      header('Location: error.php?id='.$errorMessage);
    } 
    catch (\Stripe\Error\Base $e) {
      // Display a very generic error to the user, and maybe send
      // yourself an email
      $errorMessage = 'generalerror';
      header('Location: error.php?id='.$errorMessage);
    } 
    catch (Exception $e) {
      // Something else happened, completely unrelated to Stripe
      $errorMessage = 'unknownerror';
      header('Location: error.php?id='.$errorMessage);
    }    
}
else if ((sha1($s_discountCode) != $discount_code_hash) && ($s_discountExist == 'true')) {
    
    //redirect to 'nope' page but still send email to myself
    sendInitialEmail($customerName, $s_customerAddress, $s_customerCity, $s_customerState, $s_customerZip, $s_customerEmail, $s_items, $s_notes, $s_discountCode, $s_discountExist, $customerTOTAL);
    header('Location: discountvalidation.php');
}

//Method to send initial order email to just myself
function sendInitialEmail($customerName, $s_customerAddress, $s_customerCity, $s_customerState, $s_customerZip, $s_customerEmail, $s_items, $s_notes, $s_discountCode, $s_discountExist, $customerTOTAL)
{
    $csz = $s_customerCity . ', ' . $s_customerState . ' ' . $s_customerZip;
    $emailBody = "From: $customerName\n E-Mail: $s_customerEmail\n Address: $s_customerAddress\n $csz\n Items Ordered: $s_items\n Notes: $s_notes\n Discount Exists: $s_discountExist\n Discount Code: $s_discountCode\n Amount Billed: $customerTOTAL";
    $to = 'mail@domain.com';
    $subject = 'Order Submission - PreStripe';
    $headers = 'From: mail@domain.com' . "\r\n" .
            'Reply-To: mail@domain.com' . "\r\n" .
            'X-Mailer: PHP/' . phpversion();

    mail ($to, $subject, $emailBody, $headers);
}

//Method to send Order Confirmation email
function sendOrderEmail($customerName, $s_customerAddress, $s_customerCity, $s_customerState, $s_customerZip, $s_customerEmail, $transID, $customerTOTAL)
{
    $csz = $s_customerCity . ', ' . $s_customerState . ' ' . $s_customerZip;

    $emailBody = "Hi $customerName,\n\nThis email serves as confirmation of the order you placed, for an amount of $customerTOTAL. Your Transaction ID is: $transID. Please save or print this email for your records. In addition, you will receive an invoice in 1-2 business days.\n\nPlease allow 3-4 weeks for completion of your piece (usually longer for Framed Plaques).\nSee <a href =\"https://www.jondstone.com/info/fineart.php#information\">Caring for your Artwork</a> for proper installation and cleaning procedures.\n\nYour order will be shipped to:\n $s_customerAddress\n$csz\n\nIf this address is incorrect, or you did not place this order, please notify me immediately.\n\nThank you,\nJon Stone\n\norders@jondstone.com\n<a href =\"https://www.jondstone.com/\">www.jondstone.com</a>\nOklahoma City, OK"; 
    $to = $s_customerEmail;
    $subject = 'Order Confirmation';
    $headers = 'From: mail@domain.com' . "\r\n" .
            'Reply-To: mail@domain.com' . "\r\n" .
            'Bcc: mail@domain.com' . "\r\n" .
            'X-Mailer: PHP/' . phpversion();

    mail ($to, $subject, $emailBody, $headers);
}
?> 
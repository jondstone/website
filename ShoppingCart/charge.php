<?php
// Vars
$discountCodeHash = 'SHA1HASHCODE'; //discountcode

// Define input fields and their sanitization rules
$firstName = filter_var($_POST['firstName'], FILTER_SANITIZE_STRING);
$lastName = filter_var($_POST['lastName'], FILTER_SANITIZE_STRING);
$customerEmail = filter_var($_POST['email'], FILTER_SANITIZE_STRING);
$customerAddress = filter_var($_POST['Address'], FILTER_SANITIZE_STRING);
$customerCity = filter_var($_POST['City'], FILTER_SANITIZE_STRING);
$customerState = filter_var($_POST['State'], FILTER_SANITIZE_STRING);
$customerZip = filter_var($_POST['Zip'], FILTER_SANITIZE_STRING);
$notes = filter_var($_POST['Notes'], FILTER_SANITIZE_STRING);
$discountCode = filter_var($_POST['discountCode'], FILTER_SANITIZE_STRING);
$discountExist = filter_var($_POST['discountExist'], FILTER_SANITIZE_STRING);
$cartItems = filter_var($_POST['cartItems'], FILTER_SANITIZE_STRING);
$totalAmount = filter_var($_POST['totalAmount'], FILTER_SANITIZE_STRING);
$stripeToken = filter_var($_POST['stripeToken'], FILTER_SANITIZE_STRING);

// Set vars from inputs, to be used throughout 
$customerName = "$firstName $lastName";
$stripeTOTAL = $totalAmount . '00'; // Declare TOTAL price for Stripe where 170000 => 1700.00
$customerTOTAL = '$' . $totalAmount;

// Charge the customer, assuming the following conditions are met
if (
  // Case 1: Discount code matches the hash and discount exists
  ((sha1($discountCode) === $discountCodeHash) && ($discountExist == 'true')) 

  // Case 2: No discount code and discount does not exist
  || (($discountCode == '') && ($discountExist == 'false')) 

  // Case 3: Discount code does not match the hash and discount does not exist
  || ((sha1($discountCode) != $discountCodeHash) && ($discountExist == 'false'))
) {
    // Generate initial email to myself, just in case something fails. This allows me to still reach out to the customer
    sendEmail(
      'initial', 
      $customerName, 
      $customerAddress, 
      $customerCity, 
      $customerState, 
      $customerZip, 
      $customerEmail, 
      $cartItems, 
      $notes,
      $discountCode, 
      $discountExist, 
      $customerTOTAL, 
      null, 
      $message = 'Starting Order Process'
    );

    // Set Keys
    require_once('stripe-php/init.php');

    $stripe = [
      "secret_key"      => "sk_live_livekey",
      "publishable_key" => "pk_live_livekey",
    ];
    \Stripe\Stripe::setApiKey($stripe['secret_key']);

    try {
      // Create Stripe Customer
      $customer = \Stripe\Customer::create([
          'email' => $customerEmail,
          'source' => $stripeToken,
      ]);
   

      // Charge Customer
      $charge = \Stripe\Charge::create([
          'customer' => $customer->id,
          'amount'   => $stripeTOTAL,
          'currency' => 'usd',
      ]);

      //Store Charge ID to pass into sendOrderEmail
      $transID = $charge->id;

      //Send Confirmation email to customer and myself
      sendEmail(
        'order', 
        $customerName, 
        $customerAddress, 
        $customerCity, 
        $customerState, 
        $customerZip, 
        $customerEmail, 
        $cartItems, 
        null, 
        null, 
        null, 
        $customerTOTAL, 
        $transID
      );

      // Send customer to success page and display transId
      header('Location: orderconfirmation.php?tid='.$transID);
      exit();

    } catch (\Stripe\Exception\CardException $e) { //done
        // Card error: The card was declined
        $errorMessage = 'declinedcard';
        header('Location: error.php?id='.$errorMessage);
        exit();
    } catch (\Stripe\Exception\RateLimitException $e) {
        // Too many requests made to the API too quickly
        $errorMessage = 'ratelimit';
        header('Location: error.php?id='.$errorMessage);
        exit();
    } catch (\Stripe\Exception\InvalidRequestException $e) { //done
        // Invalid parameters were supplied to Stripe's API
        $errorMessage = 'invalidrequest';  
        header('Location: error.php?id='.$errorMessage);
        exit();
    } catch (\Stripe\Exception\AuthenticationException $e) {
        // Authentication with Stripe's API failed 
        $errorMessage = 'autherror';
        header('Location: error.php?id='.$errorMessage);
        exit();
    } catch (\Stripe\Exception\ApiConnectionException $e) {
        // Network communication with Stripe failed
        $errorMessage = 'apiconnection';    
        header('Location: error.php?id='.$errorMessage);
        exit();
    } catch (\Stripe\Exception\ApiErrorException $e) {
        // Other API errors
        $errorMessage = 'generalerror';
        header('Location: error.php?id='.$errorMessage);
        exit();
    } catch (Exception $e) {
        // Something else happened, completely unrelated to Stripe
        $errorMessage = 'unknownerror';
        header('Location: error.php?id='.$errorMessage);
        exit();
    }
}
else if ((sha1($discountCode) != $discountCodeHash) && ($discountExist == 'true')) {
  // Redirect to "Haha" page, and send me an email
  sendEmail(
    'initial', 
    $customerName, 
    $customerAddress, 
    $customerCity, 
    $customerState, 
    $customerZip, 
    $customerEmail, 
    $cartItems, 
    $notes,
    $discountCode, 
    $discountExist, 
    $customerTOTAL, 
    null, 
    $message = 'Order Failed because of discount code manipulation'
  );
  header('Location: discountvalidation.php');
}

/**
 * Sends an email based on the specified type.
 * 
 * This function handles sending different types of emails. It can send an initial email to the admin with order details,
 * or send an order confirmation email to the customer. It supports both HTML-formatted and plain text content.
 * 
 * @param string $type The type of email to send ('initial' for admin email or 'order' for customer order confirmation).
 * @param string $customerName The name of the customer.
 * @param string $customerAddress The address of the customer.
 * @param string $customerCity The city of the customer.
 * @param string $customerState The state of the customer.
 * @param string $customerZip The zip code of the customer.
 * @param string $customerEmail The email address of the customer.
 * @param string $cartItems The list of cartItems ordered 
 * @param string|null $notes Any additional notes provided by the customer (optional, used in 'initial' email).
 * @param string|null $discountCode The discount code used (optional, used in 'initial' email).
 * @param string|null $discountExist A flag indicating if a discount exists (optional, used in 'initial' email).
 * @param string $customerTOTAL The total amount billed to the customer.
 * @param string|null $transID The transaction ID (optional, used in 'order' email).
 * @param string|null $message Additional message content (optional, used in 'initial' email).
 * 
 * @return void This function does not return anything. It sends the email directly.
 */
function sendEmail(
  $type,
  $customerName,
  $customerAddress,
  $customerCity,
  $customerState,
  $customerZip,
  $customerEmail,
  $cartItems = null,
  $notes = null,
  $discountCode = null,
  $discountExist = null,
  $customerTOTAL,
  $transID = null,
  $message = null
): void 
{
    // Format CSZ
    $csz = "$customerCity, $customerState $customerZip";

    // Initialize email body
    $emailBody = '';

    // Switch between types of email (initial email to admin or order confirmation to customer)
    if ($type == 'initial') {
        // Initial email to admin
        $emailBody = "
            From: $customerName
            E-Mail: $customerEmail
            Address: $customerAddress
            City, State, Zip: $csz
            Items Ordered: $cartItems
            Customer Notes: $notes
            Discount Exists: $discountExist
            Discount Code: $discountCode
            Amount Billed: $customerTOTAL
            Message: $message
        ";
        $to = 'email@email.com';
        $subject = 'Order Submission - PreStripe';
        $headers = 'From: email@email.com' . "\r\n" . 
                   'Reply-To: email@email.com' . "\r\n" . 
                   'X-Mailer: PHP/' . phpversion();
    } elseif ($type == 'order') {
        // Order confirmation email to customer
        $emailBody = "
        <html>
        <body>
            Hello $customerName!
            This email serves as confirmation of the order you placed, for an amount of <strong>$customerTOTAL</strong>.
            <br><br>
            Your Transaction ID is: <strong>$transID</strong>. Please save or print this email for your records. 
            In addition, you will receive a paid invoice in 1-2 business days.
            <br><br>
            Please allow at least a minimum of 4 weeks for completion of your piece (usually longer for Framed Plaques).<br>
            See <a href=\"https://www.jondstone.com/info/fineart.php#information\">Caring for your Artwork</a> 
            for proper installation and cleaning procedures.
            <br><br>
            Your order will be shipped to:<br>
            $customerAddress<br>$csz
            <br><br>
            If this address is incorrect, or you did not place this order, please notify me immediately.
            <br><br>
            Thank you,<br>
            Jon Stone
            <br><br>
            <a href=\"mailto:email@email.com\">email@email.com</a><br>
            <a href=\"https://www.jondstone.com/\">www.jondstone.com</a>
            
        </body>
        </html>
        ";

        $to = $customerEmail;
        $subject = 'Order Confirmation';
        $headers = 'From: email@email.com' . "\r\n" .
                   'Reply-To: email@email.com' . "\r\n" .
                   'Bcc: email@email.com' . "\r\n" .
                   'X-Mailer: PHP/' . phpversion() . "\r\n" .
                   'MIME-Version: 1.0' . "\r\n" .
                   'Content-Type: text/html; charset=UTF-8' . "\r\n";
    }

    // Send the email
    mail($to, $subject, $emailBody, $headers);
}
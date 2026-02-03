<?php
// charge.php

require_once('stripe-php/init.php');
$discountCodeHash = 'hashcode'; // hashcode
$stripe = [
    "secret_key" => "sk_live_key",
    "publishable_key" => "pk_live_key",
];

\Stripe\Stripe::setApiKey($stripe['secret_key']);
\Stripe\Stripe::setCABundlePath('cacert.pem');

/**
 * Validate Discount Code
 * 
 * Validates a provided discount code by comparing its SHA1 hash against
 * the stored hash. This is used for server-side validation to prevent client side tampering.
 */
function validateDiscountCode($discountCode, $discountCodeHash) {
    $trimmedCode = trim($discountCode);

    // Empty code = no discount, but valid
    if (empty($trimmedCode)) {
        return ['valid' => true, 'discountPercent' => 0];
    }

    // Hash the code the customer provided and compare
    $providedHash = sha1($trimmedCode);
    if ($providedHash === $discountCodeHash) {
        return ['valid' => true, 'discountPercent' => 0.15];
    }

    // Code was provided but doesn't match (assume they fat fingered the field)
    return ['valid' => false, 'discountPercent' => 0];
}

/**
 * AJAX Handler: Validate Discount Code
 * 
 * Validates the discount code and returns its validity status and discount percentage.
 * Used for real-time validation as customers enter the discount code.
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'validate_discount') {
    // Set JSON response header
    header('Content-Type: application/json');

    // Extract discount code from request
    $discountCode = $_POST['discountCode'] ?? '';
    
    // Validate the discount code against stored hash
    $discountValidation = validateDiscountCode($discountCode, $discountCodeHash);

    // Return validation result with discount percentage
    echo json_encode([
        'valid' => $discountValidation['valid'],
        'discountPercent' => $discountValidation['discountPercent']
    ]);
    exit();
}

/**
 * AJAX Handler: Update Stripe PaymentIntent
 * 
 * Updates an existing PaymentIntent with a new amount and discount metadata.
 * Typically used when discount codes are applied/removed after initial creation.
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_payment_intent') {
    // Set JSON response header
    header('Content-Type: application/json');

    // Extract input parameters
    $paymentIntentId = $_POST['paymentIntentId'] ?? '';
    $newAmount = (int) ($_POST['amount'] ?? 0);
    $discountCode = $_POST['discountCode'] ?? '';

    // Validate required parameters
    if (empty($paymentIntentId) || $newAmount <= 0) {
        echo json_encode(['error' => 'Invalid parameters']);
        exit();
    }

    try {
        // Validate the discount code against stored hash
        $discountValidation = validateDiscountCode($discountCode, $discountCodeHash);
        $discountApplied = ($discountValidation['valid'] && $discountValidation['discountPercent'] > 0);

        // Only store discount code in metadata if it was actually applied
        $metadataDiscountCode = $discountApplied ? trim($discountCode) : '';

        // Update the existing PaymentIntent with new amount and metadata
        \Stripe\PaymentIntent::update($paymentIntentId, [
            'amount' => $newAmount,
            'metadata' => [
                'discount_code' => $metadataDiscountCode,
                'discount_exist' => $discountApplied ? 'true' : 'false',
            ]
        ]);

        // Return success confirmation
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        // Handle any Stripe API or validation errors
        error_log('Update payment intent exception: ' . $e->getMessage() . ' | intent=' . $paymentIntentId . ' | amount=' . $newAmount);
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit();
}

/**
 * AJAX Handler: Create Stripe PaymentIntent
 * 
 * Processes payment intent creation requests, validate discount code,
 * and returns the client secret needed to complete the payment in the frontend.
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create_payment_intent') {
    // Set JSON response header
    header('Content-Type: application/json');

    // Extract and sanitize input data
    $amount = (int) ($_POST['amount'] ?? 0);
    $discountCode = $_POST['discountCode'] ?? '';

    // Validate amount is positive
    if ($amount <= 0) {
        echo json_encode(['error' => 'Invalid amount']);
        exit();
    }

    try {
        // Validate the discount code against stored hash
        $discountValidation = validateDiscountCode($discountCode, $discountCodeHash);
        $discountApplied = ($discountValidation['valid'] && $discountValidation['discountPercent'] > 0);

        // Only store discount code in metadata if it was actually applied
        $metadataDiscountCode = $discountApplied ? trim($discountCode) : '';

        // Create the Stripe PaymentIntent
        $paymentIntent = \Stripe\PaymentIntent::create([
            'amount' => $amount,
            'currency' => 'usd',
            'automatic_payment_methods' => ['enabled' => true],
            'metadata' => [
                'discount_code' => $metadataDiscountCode,
                'discount_exist' => $discountApplied ? 'true' : 'false',
            ]
        ]);

        // Return success response with client secret for frontend
        echo json_encode([
            'clientSecret' => $paymentIntent->client_secret,
            'paymentIntentId' => $paymentIntent->id
        ]);
    } catch (Exception $e) {
        // Handle any Stripe API or validation errors
        error_log('Create payment intent exception: ' . $e->getMessage() . ' | amount=' . $amount . ' | discountCode=' . $discountCode);
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit();
}

// Main POST handler - Process completed payment
$firstName = htmlspecialchars(strip_tags($_POST['firstName'] ?? ''), ENT_QUOTES, 'UTF-8');
$lastName = htmlspecialchars(strip_tags($_POST['lastName'] ?? ''), ENT_QUOTES, 'UTF-8');
$customerEmail = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
$customerAddress = htmlspecialchars(strip_tags($_POST['Address'] ?? ''), ENT_QUOTES, 'UTF-8');
$customerCity = htmlspecialchars(strip_tags($_POST['City'] ?? ''), ENT_QUOTES, 'UTF-8');
$customerState = htmlspecialchars(strip_tags($_POST['State'] ?? ''), ENT_QUOTES, 'UTF-8');
$customerZip = htmlspecialchars(strip_tags($_POST['Zip'] ?? ''), ENT_QUOTES, 'UTF-8');
$notes = htmlspecialchars(strip_tags($_POST['Notes'] ?? ''), ENT_QUOTES, 'UTF-8');
$cartItems = htmlspecialchars(strip_tags($_POST['cartItems'] ?? ''), ENT_QUOTES, 'UTF-8');
$totalAmount = htmlspecialchars(strip_tags($_POST['totalAmount'] ?? ''), ENT_QUOTES, 'UTF-8');
$paymentIntentId = htmlspecialchars(strip_tags($_POST['paymentIntentId'] ?? ''), ENT_QUOTES, 'UTF-8');

$customerName = "$firstName $lastName";
$stripeTOTAL = ((int)$totalAmount) * 100;
$customerTOTAL = '$' . $totalAmount;

// Retrieve the PaymentIntent metadata from Stripe
$paymentIntent = \Stripe\PaymentIntent::retrieve($paymentIntentId);

// Discount metadata for email
$discountCode = $paymentIntent->metadata->discount_code ?? '';
$discountExist = $paymentIntent->metadata->discount_exist ?? 'false';

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
    'Starting Order Process'
);

try {
    if ($paymentIntent->status !== 'succeeded') {
        throw new Exception('Payment was not successful');
    }
    
    if ($paymentIntent->amount != $stripeTOTAL) {
        throw new Exception('Payment amount mismatch');
    }

    // Success
    $transID = $paymentIntent->id;
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

    header('Location: orderresult.php?status=success&tid=' . $transID);
    exit();
} catch (\Stripe\Exception\CardException $e) {
    error_log('Card exception: ' . $e->getMessage() . ' | intent=' . $paymentIntentId);
    header('Location: orderresult.php?status=declinedcard');
    exit();
} catch (\Stripe\Exception\RateLimitException $e) {
    error_log('Rate limit exception: ' . $e->getMessage() . ' | intent=' . $paymentIntentId);
    header('Location: orderresult.php?status=ratelimit');
    exit();
} catch (\Stripe\Exception\InvalidRequestException $e) {
    error_log('Invalid request exception: ' . $e->getMessage() . ' | intent=' . $paymentIntentId);
    header('Location: orderresult.php?status=invalidrequest');
    exit();
} catch (\Stripe\Exception\AuthenticationException $e) {
    error_log('Authentication exception: ' . $e->getMessage() . ' | intent=' . $paymentIntentId);
    header('Location: orderresult.php?status=autherror');
    exit();
} catch (\Stripe\Exception\ApiConnectionException $e) {
    error_log('API connection exception: ' . $e->getMessage() . ' | intent=' . $paymentIntentId);
    header('Location: orderresult.php?status=apiconnection');
    exit();
} catch (\Stripe\Exception\ApiErrorException $e) {
    error_log('API error exception: ' . $e->getMessage() . ' | intent=' . $paymentIntentId);
    header('Location: orderresult.php?status=generalerror');
    exit();
} catch (Exception $e) {
    error_log('Order exception: ' . $e->getMessage() . ' | intent=' . $paymentIntentId . ' | expected=' . $stripeTOTAL);
    header('Location: orderresult.php?status=unknown');
    exit();
}

/**
 * Sends an email based on the specified type.
 * 
 * This function handles sending different types of emails. It can send an initial email to the admin with order details,
 * or send an order confirmation email to the customer. It supports both HTML-formatted and plain text content.
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
        $cartItemsExpanded = explode('||', rtrim($cartItems, '|'));
        $cartLines = [];

        foreach ($cartItemsExpanded as $item) {
            if ($item === '') continue;
            [$name, $group, $size, $finish] = explode('|', $item);
            $cartLines[] = "{$name} ({$finish})({$group} {$size})";
        }

        $cartItemsHtml = implode("<br>", $cartLines);

        // Order confirmation email to customer
        $emailBody = <<<HTML
        <html>
        <body style="margin:0;padding:0;font-family:Arial,Helvetica,sans-serif;font-size:14px;line-height:1.6;color:#222;">
        <div style="max-width:600px;margin:0 auto;padding:20px;">

            <p>Hello <strong>$customerName</strong>,</p>
            <p>
                This email serves as confirmation of the order you placed, for an amount of
                <strong>$customerTOTAL</strong>.
            </p>
            <p>
                Your Transaction ID is:
                <strong>$transID</strong>.<br>
                Please save or print this email for your records.
                In addition, you will receive a paid invoice in 1-2 business days.
            </p>
            <p>
                Please allow a minimum of <strong>4 weeks</strong> for completion of your piece
                (usually longer for Framed Plaques).
                See
                <a href="https://www.jondstone.com/info/fineart.php#information"
                    style="color:#7a1f1f;text-decoration:none;">
                    Caring for your Artwork
                </a>
                for proper installation and cleaning procedures.
            </p>

            <hr style="border:none;border-top:1px solid #ddd;margin:24px 0;">

            <p><strong>Your order:</strong></p>
                <p style="margin-left:10px;">
                    $cartItemsHtml
                </p>
            <p>
                <strong>will be shipped to:</strong><br>
                <p style="margin-left:10px;">
                    $customerAddress<br>
                    $csz
                </p>
            </p>

            <hr style="border:none;border-top:1px solid #ddd;margin:24px 0;">

            <p>
                If this address is incorrect, or you did not place this order,
                please notify me immediately.
            </p>
            <p style="margin-top:32px;">
                Thank you,<br>
                <strong>Jon Stone</strong>
            </p>
            <p>
                <a href="mailto:email@email.com" style="color:#7a1f1f;text-decoration:none;">
                    email@email.com
                </a><br>
                <a href="https://www.jondstone.com/" style="color:#7a1f1f;text-decoration:none;">
                    www.jondstone.com
                </a>
            </p>
        </div>
        </body>
        </html>
        HTML;
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
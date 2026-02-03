<?php
$status  = $_GET['status'] ?? 'unknown';
$transID = $_GET['tid'] ?? null;

$messages = [
    'success' => [
        'page_title' => 'Jon Stone Photography | Order Confirmation',
        'h1'         => 'Order Confirmation',
        'body'       => 'Your order has been successfully placed and your payment was successful.<br>You will receive a confirmation email soon, please print this page for your records.',
    ],
    'declinedcard' => [
        'page_title' => 'Jon Stone Photography | Payment Failed',
        'h1'         => 'Payment Failed',
        'body'       => 'Unfortunately your card was declined by Stripe. Please try another payment method.',
    ],
    'ratelimit' => [
        'page_title' => 'Jon Stone Photography | Payment Error',
        'h1'         => 'Payment Error',
        'body'       => 'Too many requests. Please wait a moment and try again.',
    ],
    'invalidrequest' => [
        'page_title' => 'Jon Stone Photography | Payment Error',
        'h1'         => 'Payment Error',
        'body'       => 'Stripe had problem processing your payment. Please try again.',
    ],
    'autherror' => [
        'page_title' => 'Jon Stone Photography | Payment Error',
        'h1'         => 'Payment Error',
        'body'       => 'Stripes payment authentication failed. Please try again.',
    ],
    'apiconnection' => [
        'page_title' => 'Jon Stone Photography | Payment Error',
        'h1'         => 'Payment Error',
        'body'       => 'Could not connect to the Stripe payment processor. Please try again.',
    ],
    'generalerror' => [
        'page_title' => 'Jon Stone Photography | Payment Error',
        'h1'         => 'Payment Error',
        'body'       => 'An unexpected payment error occurred. Please try again.',
    ],
    'unknown' => [
        'page_title' => 'Jon Stone Photography | Payemt Error',
        'h1'         => 'Error',
        'body'       => 'An unknown payment error occurred. Please try again.',
    ],
];

$data = $messages[$status] ?? $messages['unknown'];

if ($status === 'success' && $transID) {
    $data['body'] .= "<br /><br />Transaction ID: <strong>" . htmlspecialchars($transID) . "</strong>";
}
?>
<!DOCTYPE html>
<html lang="en-US">
<head>
	<title><?= htmlspecialchars($data['page_title']) ?> </title>
    <?php include $_SERVER['DOCUMENT_ROOT'] . "/scripts/header.html"; ?>
</head>

<!-- Clear Cart if payment is successful -->
<?php if ($status === 'success'): ?>
<script>
  localStorage.clear();
</script>
<?php endif; ?>

<body>
    <noscript>
        Javascript is currently disabled. This site requires Javascript to be enabled.
    </noscript>
    <?php include $_SERVER['DOCUMENT_ROOT'] . "/scripts/header.txt"; ?>

	<div id="content">
        <br />
        <h1><?= htmlspecialchars($data['h1']) ?></h1>
        <br /><br />
        <?= $data['body'] ?>
        <br /><br /><br /><br />
	</div>

    <?php include $_SERVER['DOCUMENT_ROOT'] . "/scripts/footer.txt"; ?>

</body>
</html>
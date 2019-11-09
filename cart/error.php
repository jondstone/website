<?php
    if(!empty($_GET['id'])){
        $id = $_GET['id'];
    }
    /*else {
        header('Location: index.php');
    }*/
    if ($id == 'declinedcard'){
        $message = 'Stripe returned a result saying the card was declined, please go back and try another card.';
    }
    // Too many requests made to the API too quickly
    if ($id == 'ratelimit'){
        $message = 'Stripe\'s API timed out due to, too many calls. Please go back and try again.';
    }
    // Invalid parameters were supplied to Stripe's API
    if ($id == 'invalidrequest'){
        $message = 'Invalid parameters were supplied to Stripe\'s API, either due to incorrect card information in the checkout or a server issue. I will be in touch with you to complete your order.';
    }
    // Authentication with Stripe's API failed
    if ($id == 'autherror'){
        $message = 'The server was unable to authenticate the API with Stripe\'s servers, I will be in touch with you to complete your order.';
    }
    // Network communication with Stripe failed
    if ($id == 'apiconnection'){
        $message = 'The server failed to connect to Stripe\'s network, please go back and try again.';
    }
    // Display a very generic error to the user
    if ($id == 'generalerror'){
        $message = 'An unknown error has occurred, I will be in touch with you to complete your order.';
    }
    if ($id == 'unknownerror'){
        $message = 'An unknown error has occurred, I will be in touch with you to complete your order.';
    }
?>

<!DOCTYPE html>
<html lang="en-US">
<head>
	
</head>

<body>
   
	<div id="content">
        <br />
        <h1>Order Confirmation | Error</h1>
        <br /><br />
        Stripe was unable to process your payment, and returned the following message: <br />
        <p class="errormessage"><?php echo $message; ?></p><br /><br />
        If you believe you reached this message in error, please go back and try again. Otherwise, you may send an email to:
        <script type="text/javascript">
            document.write(atob("PGEgaHJlZj0ibWFpbHRvOm9yZGVyc0Bqb25kc3RvbmUuY29tIiBzdHlsZT0iY29sb3I6cmdiKDk0LDAsMTQpIj5vcmRlcnNAam9uZHN0b25lLmNvbTwvYT4="));
        </script>
        and I will reply back within 1 - 2 business days.
        <br /><br />
        I apologize about any inconvenience. Thank you!
        <br /><br /><br /><br />
	</div>

</body>
</html>
<?php
    if(!empty($_GET['tid'])){
        $tid = $_GET['tid'];
    }
    else {
        header('Location: index.php');
    }
?>

<!DOCTYPE html>
<html lang="en-US">
<head>
	<title>Jon Stone Photography | Order Confirmation </title>
	<link rel='stylesheet' href='./../css/cart.css' type='text/css' media='all' />
    <?php include "./../scripts/header.html" ?>
</head>

<body>
    <noscript>
        Javascript is currently disabled. This site requires Javascript to be enabled.
    </noscript>
    <?php include "./../scripts/header.txt" ?>	

	<div id="content">
        <br />
        <h1>Order Confirmation</h1>
        <br /><br />
        Thank you for your order. Your transaction ID is <?php echo $tid; ?>. Please print or save this for your records.
        <br /><br />
        A copy of your Order Confirmation will be sent to your email shortly. Kindly check your inbox.
        <br /><br /><br /><br />
	</div>

    <div id="footer">
		<?php include "./../scripts/footer.txt" ?>
	</div>

</body>
</html>
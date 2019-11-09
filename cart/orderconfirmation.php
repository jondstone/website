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
	
</head>

<body>
   
	<div id="content">
        <br />
        <h1>Order Confirmation</h1>
        <br /><br />
        Thank you for your order! Your transaction ID is <?php echo $tid; ?>. Print or save this page for your records. 
        <br /><br />
        Please check your email for a copy of the Order Confirmation.
        <br /><br /><br /><br />
	</div>

</body>
</html>
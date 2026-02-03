<!DOCTYPE html>
<html lang="en-US">
<head>
	<title>Jon Stone Photography | Shopping Cart </title>
    <link rel='stylesheet' href='/css/cart.css' type='text/css' media='all' />
    <?php include $_SERVER['DOCUMENT_ROOT'] . "/scripts/header.html"; ?>
</head>

<body>
    <noscript>
        Javascript is currently disabled. This site requires Javascript to be enabled.
    </noscript>

    <?php include $_SERVER['DOCUMENT_ROOT'] . "/scripts/header.txt"; ?>

	<div id="content">
        <div id="cartContainer">
            <div id="cartColumn">
                <h2>Shopping Cart</h2>
                <hr>
                <div id="cartData" type="visible"></div>
            </div>
            
            <div id="paymentColumn">
                <h2>Checkout</h2>
                <hr>
                
                <form id="checkout" method="post" action="charge.php">
                     <input name="_token" value="" type="hidden">
                    <input id="cartItems" name="cartItems" type="hidden">
                    <input id="totalAmount" name="totalAmount" type="hidden">
                    <input type="text" class="formInputField" id="firstName" value="" name="firstName" placeholder="First Name">
                    <input type="text" class="formInputField" id="lastName" value="" name="lastName" placeholder="Last Name">
                    <input type="text" class="formInputField" id="email" value="" name="email" placeholder="Email">
                    <input type="text" class="formInputField" id="address" value="" name="Address" placeholder="Street Address">
                    <input type="text" class="formInputField" id="city" value="" name="City" placeholder="City">
                    <input type="text" class="formInputField" id="state" value="" name="State" placeholder="State">
                    <input type="text" class="formInputField" id="zip" value="" name="Zip" placeholder="Zip">
                    <input type="text" class="formInputField" id="notes" value="" name="Notes" placeholder="Order Notes">
                    <input type="text" class="formInputField" id="discountCode" value="" name="discountCode" placeholder="Discount Code">
                    <br />
                    <p class="stripeInfo">All payments are securely handled by Stripe for your protection.</p>
                    <br /><br />
                    <div class="form-row">
                        <div id="payment-element"></div>
                        <div id="payment-message" role="alert"></div>
                    </div>
                    <br />
                    <button type="submit" id="payButton" class="btn btnPayOrder">
                        <span id="payButtonText"></span>
                    </button>
                    <br /><br />
                    <p class="privacyPolicy"><a target="_blank" href="/../info/privacypolicy.php">Privacy Policy</a></p>
                </form>
            </div>

            <div id="formValidationModal" class="modal" style="display: none;">
                <div class="formValidationModalContent">
                    <div class="formValidationModalHeader">
                        <button type="button" class="modalXClose" aria-label="Close">&times;</button>
                        <p id="modalHeader"></>
                    </div>
                    <div class="formValidationModalBody">
                        <p id="modalBody"></>
                    </div>
                    <div class="formValidationModalFooter">
                        <button type="button" class="modalCloseButton">Close</button>
                    </div>
                </div>
            </div>
        </div>
	</div>
   
    <script src="/scripts/charge.js"></script>

	<?php include $_SERVER['DOCUMENT_ROOT'] . "/scripts/footer.txt"; ?>

</body>
</html>
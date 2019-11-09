<!-- 
This shows the code associated with the shopping cart checkout page. The full example can be seen here: https://www.jondstone.com/cart/ as it assumes the users will have a basic working knowledge of HTML and CSS. If not, feel free to look at the source above.


In addition to the below JavaScript, there is a reference to jquery.min.js, and bootstrap.min.js, which is stored locally on the server.

I have chosen to store the above mentioned files locally, due to minimizing any impact to code changes, hacks, etc., that could break my code and render the checkout useless. I've done a stress-test on my server and have not had issues with loads by doing this. This will ensure the code stays completely intact and *should* always work.

The shopping cart checkout is split into two parts: the left side contains the items in the cart, and the right side contains the checkout form and payment info.

The list of items is handled by the JavaScript shown below. Removing an item, will remove it in real time and refresh the cart. However, if you open a new tab and add another item to the cart, it will not refresh. This has to be manually done. This should be fixed, but I currently don't anticipate any users having this issue. 

The form and items are driven by CSS that I wrote. Since the files are not large, I did not minimize them for ease of use. You can view the CSS files in this folder. I am using JavaScript validation to verify data as it is entered in realtime on the form. That code can be viewed below as well.

I am utilizing Stripe's API to handle credit cards, as I chose not to store this information on my server. 

Last, when the user clicks "Pay Now", it gathers the data from the form and submits it to an external PHP (shown in this folder).

All code written by hand; searched for guidance on Stack Overflow when stuck.
-->
<html>
    <script type="application/javascript">
        //calls cart on load
        $(document).ready(function () {
            updateCart();
        });

        //updates Cart
        function updateCart() {
            "use strict";
            var cartData = ""; 
            var counter = 0;
            var total = 0.0;
            var discountTotal = 0.0;
            var discountText = "";
            var doesDiscountExist = false;
            cartData += "";

            if(localStorage.length > 0) 
                $('#cartIconText').text(localStorage.length); else
                $('#cartIconText').text('');

            for (var i = 0, len = localStorage.length; i < len; i++) {
                var cartItems;
                var key = localStorage.key(i);
                var value = localStorage[key];
                var removeButton = "<button type=\"button\" class='btn btn-danger' style='margin:10px;' onclick='removeFromCart(\"" + key + "\")' />x</button>";
                var delimited = value.split("_");
                var name = delimited[0];
                var productGroup = delimited[1];
                var size = delimited[3];
                var finish = delimited[2];
                var priceInfo = delimited[4];

                total += parseInt(priceInfo);

                cartData += removeButton + "<p class=\"cart_item\">" + name + " (" + productGroup + " " + size + ")(" + finish + ") $" + priceInfo + "</p><br/>";
                cartItems += name + " (" + productGroup + " " + size + ")(" + finish + ") $" + priceInfo;
                document.getElementById('cartItems').value = cartItems;
                counter++;
            }

            //inserted a random horz line independent of Discount or Total
            cartData += '<hr>';

            var previousTotal;
            if(SHA1($('#discount_code').val().toLowerCase()) == 'SHA1Encryption')
            {
                previousTotal = total;
                total = parseInt(total * 0.85);
                discountTotal = previousTotal - total;
                discountText = "15% Discount";
                doesDiscountExist = true;
            }
            
            //discount code
            document.getElementById('discountExist').value = doesDiscountExist; 
            if(doesDiscountExist)
                cartData += "<strong><p class=\"discount_Header\">Discount:</p> " + "<p class=\"discount_Amount\">-$" + discountTotal + " </p><p class=\"discount_Text\">(" + discountText + ")</p><hr>";

            //total
            cartData += "<strong><p class=\"total\">Total:</p> " + "<p class=\"amount\">$" + total + "</p></strong>";

            if (counter > 0) {
                document.getElementById('cartData').innerHTML = cartData;
                document.getElementById('payment').style.visibility = 'visible';
            }
            else {
                document.getElementById("cartData").innerHTML = "<h1 align=\"center\"><br/>The cart is empty!<br/><br/></h1>";
                document.getElementById('payment').style.visibility = 'visible';
            }
            document.getElementById('payButton').value = "Pay $" + total.toString();
            document.getElementById('totalAmount').value = total;
        }

        //this handles displaying the cart
        $("#cartID").click(function () {
            updateCart();
        })
        
        //real time validation of certain fields
        function checkDiscountCode() {
            var result = (SHA1($('#discount_code').val()) == '347df44912abf6995c279f0687c1cd419e721451');

            if (result == false)
                $('#discount_code').css('border-color', '#F00');
            else
                $('#discount_code').css('border-color', '#0F0');
            return result;
        }
        function checkFirstName() {
            var result = checkAlphaOnly($('#firstName').val());

            if (result == false)
                $('#firstName').css('border-color', '#F00');
            else
                $('#firstName').css('border-color', '#0F0');
            return result;
        }
        function checkLastName() {
            var result = checkAlphaOnly($('#lastName').val());

            if (result == false)
                $('#lastName').css('border-color', '#F00');
            else
                $('#lastName').css('border-color', '#0F0');
            return result;
        }
        function checkAddress() {
            var result = $('#address').val().length > 4;

            if (result == false)
                $('#address').css('border-color', '#F00');
            else
                $('#address').css('border-color', '#0F0');
            return result;
        }
        function checkCity() {
            var result = checkAlphaOnly($('#city').val());

            if (result == false)
                $('#city').css('border-color', '#F00');
            else
                $('#city').css('border-color', '#0F0');
            return result;
        }
        function checkState() {
            var result = (checkAlphaOnly($('#state').val()) && $('#state').val().length > 1);

            if (result == false)
                $('#state').css('border-color', '#F00');
            else
                $('#state').css('border-color', '#0F0');
            return result;
        }
        function checkZip() {
            var result = (checkNumberOnly($('#zip').val()) && $('#zip').val().length > 4);

            if (result == false)
                $('#zip').css('border-color', '#F00');
            else
                $('#zip').css('border-color', '#0F0');
            return result;
        }
        
        //validate email
        $('#email').on('input propertychange paste', function () {
            checkEmail();
        });
        //validate first name
        $('#firstName').on('input propertychange paste', function () {
            checkFirstName();
        });
        //validate last name
        $('#lastName').on('input propertychange paste', function () {
            checkLastName();
        });
        //validate address
        $('#address').on('input propertychange paste', function () {
            checkAddress();;
        });
        //validate city
        $('#city').on('input propertychange paste', function () {
            checkCity();
        });
        //validate state
        $('#state').on('input propertychange paste', function () {
            checkState();
        });
        //validate zip
        $('#zip').on('input propertychange paste', function () {
            checkZip();
        });
        //validate discount code
        $('#discount_code').on('input propertychange', function () {
            checkDiscountCode();
            updateCart();
        });

        function showModal(title,body)
        {
            $('#modal-title').text(title);
            $('#modal-body').text(body);
            $('#universalModal').modal('show');
        }

        //validate fields upon payment click
        function payButtonClicked() {

            var title = 'Error';

            if (checkFirstName() == false) {
                showModal(title, "Please enter a valid first name");
                updateCart();
                return false;
            }
            else if (checkLastName() == false) {
                showModal(title, "Please enter a valid last name");
                updateCart();
                return false;
            }
            else if (checkEmail() == false) {
                showModal(title, "Please enter a valid email address");
                updateCart();
                return false;
            }
            else if (checkAddress() == false) {
                showModal(title, "Please enter a valid address");
                updateCart();
                return false;
            }
            else if (checkCity() == false) {
                showModal(title, "Please enter a valid city");
                updateCart();
                return false;
            }
            else if (checkState() == false) {
                showModal(title, "Please enter a valid state");
                updateCart();
                return false;
            }
            else if (checkZip() == false) {
                showModal(title, "Please enter a valid zip");
                updateCart();
                return false;
            }

            $('#payButton').prop('value', 'Processing...');
            placeCartInPost();

            //if the payment form is hidden, this means it was already stored and the customer wants to use the stored payment info so we need to remove from post
            var paymentFormElement = document.getElementById("payment-form");
            if( $('#payment-form').is(":visible") == false)
            {
                paymentFormElement.parentNode.removeChild(paymentFormElement);
                $('form#checkout').submit();
            }
            return true;
        }

        //first part of additional validation of fields
        function checkAlphaOnly(name) { 
            var alphaCheck = /^[a-zA-Z\s]+$/;
            return alphaCheck.test(name);
        }

        function checkEmail() {
            var emailAddress = $('#email').val();
            var sQtext = '[^\\x0d\\x22\\x5c\\x80-\\xff]';
            var sDtext = '[^\\x0d\\x5b-\\x5d\\x80-\\xff]';
            var sAtom = '[^\\x00-\\x20\\x22\\x28\\x29\\x2c\\x2e\\x3a-\\x3c\\x3e\\x40\\x5b-\\x5d\\x7f-\\xff]+';
            var sQuotedPair = '\\x5c[\\x00-\\x7f]';
            var sDomainLiteral = '\\x5b(' + sDtext + '|' + sQuotedPair + ')*\\x5d';
            var sQuotedString = '\\x22(' + sQtext + '|' + sQuotedPair + ')*\\x22';
            var sDomain_ref = sAtom;
            var sSubDomain = '(' + sDomain_ref + '|' + sDomainLiteral + ')';
            var sWord = '(' + sAtom + '|' + sQuotedString + ')';
            var sDomain = sSubDomain + '(\\x2e' + sSubDomain + ')*';
            var sLocalPart = sWord + '(\\x2e' + sWord + ')*';
            var sAddrSpec = sLocalPart + '\\x40' + sDomain; // complete RFC822 email address spec
            var sValidEmail = '^' + sAddrSpec + '$'; // as whole string

            var reValidEmail = new RegExp(sValidEmail);

            var result = reValidEmail.test(emailAddress);

            if (result == false)
                $('#email').css('border-color', '#F00');
            else
                $('#email').css('border-color', '#0F0');

            return result;
        }

        function checkNumberOnly(zip) {
            var numberCheck = new RegExp("^[0-9]+$");
            return numberCheck.test(zip);
        }

        //remove items from the cart
        function removeFromCart(keyToRemove) {
            localStorage.removeItem(keyToRemove);
            updateCart();
        }
        
        function erasePaymentData()
        {
            var cookies = document.cookie.split(";");
            for (var i = 0; i < cookies.length; i++) {
                var equals = cookies[i].indexOf("=");
                var name = equals > -1 ? cookies[i].substr(0, equals) : cookies[i];
                document.cookie = name + "=;expires=Thu, 01 Jan 1970 00:00:00 GMT";
                window.location = "https://www.jondstone.com";
            }
        }

        //second part of script
        function SHA1(msg) {
            function rotate_left(n,s) {
                var t4 = (n<<s) | (n>>>(32-s));
                return t4;
            }
            function lsb_hex(val) {
                var str="";
                var i;
                var vh;
                var vl;
                for( i=0; i<=6; i+=2 ) {
                    vh = (val>>>(i*4+4))&0x0f;
                    vl = (val>>>(i*4))&0x0f;
                    str += vh.toString(16) + vl.toString(16);
                }
                return str;
            }
            function cvt_hex(val) {
                var str="";
                var i;
                var v;
                for( i=7; i>=0; i-- ) {
                    v = (val>>>(i*4))&0x0f;
                    str += v.toString(16);
                }
                return str;
            }
            function Utf8Encode(string) {
                string = string.replace(/\r\n/g,"\n");
                var utftext = "";
                for (var n = 0; n < string.length; n++) {
                    var c = string.charCodeAt(n);
                    if (c < 128) {
                        utftext += String.fromCharCode(c);
                    }
                    else if((c > 127) && (c < 2048)) {
                        utftext += String.fromCharCode((c >> 6) | 192);
                        utftext += String.fromCharCode((c & 63) | 128);
                    }
                    else {
                        utftext += String.fromCharCode((c >> 12) | 224);
                        utftext += String.fromCharCode(((c >> 6) & 63) | 128);
                        utftext += String.fromCharCode((c & 63) | 128);
                    }
                }
                return utftext;
            }
            var blockstart;
            var i, j;
            var W = new Array(80);
            var H0 = 0x67452301;
            var H1 = 0xEFCDAB89;
            var H2 = 0x98BADCFE;
            var H3 = 0x10325476;
            var H4 = 0xC3D2E1F0;
            var A, B, C, D, E;
            var temp;
            msg = Utf8Encode(msg);
            var msg_len = msg.length;
            var word_array = new Array();
            for( i=0; i<msg_len-3; i+=4 ) {
                j = msg.charCodeAt(i)<<24 | msg.charCodeAt(i+1)<<16 |
                msg.charCodeAt(i+2)<<8 | msg.charCodeAt(i+3);
                word_array.push( j );
            }
            switch( msg_len % 4 ) {
                case 0:
                    i = 0x080000000;
                    break;
                case 1:
                    i = msg.charCodeAt(msg_len-1)<<24 | 0x0800000;
                    break;
                case 2:
                    i = msg.charCodeAt(msg_len-2)<<24 | msg.charCodeAt(msg_len-1)<<16 | 0x08000;
                    break;
                case 3:
                    i = msg.charCodeAt(msg_len-3)<<24 | msg.charCodeAt(msg_len-2)<<16 | msg.charCodeAt(msg_len-1)<<8  | 0x80;
                    break;
            }
            word_array.push( i );
            while( (word_array.length % 16) != 14 ) word_array.push( 0 );
            word_array.push( msg_len>>>29 );
            word_array.push( (msg_len<<3)&0x0ffffffff );
            for ( blockstart=0; blockstart<word_array.length; blockstart+=16 ) {
                for( i=0; i<16; i++ ) W[i] = word_array[blockstart+i];
                for( i=16; i<=79; i++ ) W[i] = rotate_left(W[i-3] ^ W[i-8] ^ W[i-14] ^ W[i-16], 1);
                A = H0;
                B = H1;
                C = H2;
                D = H3;
                E = H4;
                for( i= 0; i<=19; i++ ) {
                    temp = (rotate_left(A,5) + ((B&C) | (~B&D)) + E + W[i] + 0x5A827999) & 0x0ffffffff;
                    E = D;
                    D = C;
                    C = rotate_left(B,30);
                    B = A;
                    A = temp;
                }
                for( i=20; i<=39; i++ ) {
                    temp = (rotate_left(A,5) + (B ^ C ^ D) + E + W[i] + 0x6ED9EBA1) & 0x0ffffffff;
                    E = D;
                    D = C;
                    C = rotate_left(B,30);
                    B = A;
                    A = temp;
                }
                for( i=40; i<=59; i++ ) {
                    temp = (rotate_left(A,5) + ((B&C) | (B&D) | (C&D)) + E + W[i] + 0x8F1BBCDC) & 0x0ffffffff;
                    E = D;
                    D = C;
                    C = rotate_left(B,30);
                    B = A;
                    A = temp;
                }
                for( i=60; i<=79; i++ ) {
                    temp = (rotate_left(A,5) + (B ^ C ^ D) + E + W[i] + 0xCA62C1D6) & 0x0ffffffff;
                    E = D;
                    D = C;
                    C = rotate_left(B,30);
                    B = A;
                    A = temp;
                }
                H0 = (H0 + A) & 0x0ffffffff;
                H1 = (H1 + B) & 0x0ffffffff;
                H2 = (H2 + C) & 0x0ffffffff;
                H3 = (H3 + D) & 0x0ffffffff;
                H4 = (H4 + E) & 0x0ffffffff;
            }
            temp = cvt_hex(H0) + cvt_hex(H1) + cvt_hex(H2) + cvt_hex(H3) + cvt_hex(H4);

            return temp.toLowerCase();
        }
    </script>
    
    <div id="content">
        <div id="cart_Container">
            <div id="cart">
                <h2>Shopping Cart</h2>
                <hr>
                <div id="cartData" type="visible"></div>
            </div>
            
            <div id="payment">
                <h2>Checkout</h2>
                <hr>
                
                <form id="checkout" method="post" action="charge.php">
                    <input name="_token" value="SieNmuKUDLLfmVqdUK90fpLUVVyX2npLnmFKg7Py" type="hidden">
                    <input id="cartItems" name="cartItems" type="hidden">
                    <input id="discountExist" name="discountExist" type="hidden">
                    <input id="totalAmount" name="totalAmount" type="hidden">
                    <input type="text" class="form-control" id="firstName" value="" name="firstName" placeholder="First name">
                    <input type="text" class="form-control" id="lastName" value="" name="lastName" placeholder="Last name">
                    <input type="text" class="form-control" id="email" value="" name="email" placeholder="Email">
                    <input type="text" class="form-control" id="address" value="" name="Address" placeholder="Street Address">
                    <input type="text" class="form-control" id="city" value="" name="City" placeholder="City">
                    <input type="text" class="form-control" id="state" value="" name="State" placeholder="State">
                    <input type="text" class="form-control" id="zip" value="" name="Zip" placeholder="Zip">
                    <input type="text" class="form-control" id="notes" value="" name="Notes" placeholder="Order Notes">
                    <input type="text" class="form-control" id="discount_code" value="" name="discount_code" placeholder="Discount Code">
                    <br />
                    <p class="stripe">Payments are securely handled and stored by Stripe for your protection.</p>
                    <br />
                    <div class="form-row">
                        <div id="card-element">
                          <!-- A Stripe Element will be inserted here. -->
                        </div>

                        <!-- Used to display form errors. -->
                        <div id="card-errors" role="alert"></div>
                    </div>
                    <br />
                    <input id="payButton" class="btn btn-success" type="submit" onclick="return payButtonClicked();" value="">
                    <br /><br />
                    <p class="policy"><a target="_blank" href="">Privacy Policy</a></p>
                </form>
                <script src="insert you charge.js"></script>
            </div>
            <div class="modal fade" id="universalModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                            <h2 class="modal-title" id="modal-title"></h2>
                        </div>
                        <div class="modal-body">
                            <h3 id="modal-body"></h3>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
	</div>

</html>
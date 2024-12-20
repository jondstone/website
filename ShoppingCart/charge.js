// Create a Stripe client.
var stripe = Stripe('pk_live_livekey');

// Create an instance of Elements.
var elements = stripe.elements();

// Custom styling can be passed to options when creating an Element.
// (Note that this demo uses a wider set of styles than the guide below.)
var style = {
  base: {
    color: '#32325d',
    fontFamily: '"Helvetica Neue", Helvetica, sans-serif',
    fontSmoothing: 'antialiased',
    fontSize: '16px',
    '::placeholder': {
      color: '#aab7c4'
    }
  },
  invalid: {
    color: '#fa755a',
    iconColor: '#fa755a'
  }
};

// Create an instance of the card Element.
var card = elements.create('card', {style: style});

// Add an instance of the card element
card.mount('#cardElement');

// Handle real-time validation errors from the card Element.
card.addEventListener('change', function(event) {
  var displayError = document.getElementById('cardErrors');
  if (event.error) {
    displayError.textContent = event.error.message;
  } else {
    displayError.textContent = '';
  }
});

// Handle form submission.
var form = document.getElementById('checkout');
form.addEventListener('submit', function(event) {
  event.preventDefault();

  stripe.createToken(card).then(function(result) {
    if (result.error) {
      // Inform the user if there was an error.
      var errorElement = document.getElementById('cardErrors');
      errorElement.textContent = result.error.message;
    } else {
      // Send the token to your server.
      stripeTokenHandler(result.token);
    }
  });
});

// Submit the form with the token ID.
function stripeTokenHandler(token) {
  // Insert the token ID into the form so it gets submitted to the server
  var form = document.getElementById('checkout');
  var hiddenInput = document.createElement('input');
  hiddenInput.setAttribute('type', 'hidden');
  hiddenInput.setAttribute('name', 'stripeToken');
  hiddenInput.setAttribute('value', token.id);
  form.appendChild(hiddenInput);

  // Submit the form
  form.submit();
}
/* ---------------------------------------- */
// Update the cart as soon as the page loads
// This ensures the cart data is consistent with local storage
document.addEventListener('DOMContentLoaded', function () {
  updateCart();
});

// Update cart items and calculate total
function updateCart() {
  "use strict";
  var cartData = ""; 
  var cartItems = '';
  var counter = 0;
  var total = 0.0;
  var discountTotal = 0.0;
  var discountText = '';
  var doesDiscountExist = false;
  cartData += "";

  document.addEventListener('DOMContentLoaded', function () {
      var cartIconText = document.getElementById('cartIconText');

      if (cartIconText) {
          if (localStorage.length > 0) {
              cartIconText.textContent = localStorage.length;
          } else {
              cartIconText.textContent = '';
          }
      } 
  });

  // Iterate through items in local storage
  for (var i = 0, len = localStorage.length; i < len; i++) {
      var key = localStorage.key(i);
      var value = localStorage.getItem(key);

      var delimited = value.split("_");
      var name = delimited[0];
      var productGroup = delimited[1];
      var finish = delimited[2];
      var size = delimited[3];
      var priceInfo = delimited[4];

      total += parseInt(priceInfo);
      
      var removeButton = "<button type=\"button\" class='btn btnRemoveItem' style='margin:10px;' onclick='removeFromCart(\"" + key + "\")' />x</button>";
      cartData += removeButton + "<p class=\"cartItem\">" + name + " (" + productGroup + " " + size + ")(" + finish + ") $" + priceInfo + "</p><br/>";
      cartItems += name + " (" + productGroup + " " + size + ")(" + finish + ") $" + priceInfo;
      counter++;
  }

  //  Update the hidden input field with cart items
  document.getElementById('cartItems').value = cartItems;

  // Insert horz line between cart items and Total Price
  cartData += '<hr>';

  // Check for valid discount code
  var previousTotal;
  var discountCode = document.getElementById('discountCode').value;
  var hashedCode = SHA1(discountCode); 

  if (hashedCode === 'SHA1HASHCODE') {
      previousTotal = total;
      total = Math.floor(total * 0.85); 
      discountTotal = previousTotal - total;
      discountText = "15% Discount";
      doesDiscountExist = true;
  }

  // Update discount existence input for server-side validation
  document.getElementById('discountExist').value = doesDiscountExist; 
  if (doesDiscountExist) {
      cartData += "<strong><p class=\"discountHeader\">Discount:</p>" + 
                  "<p class=\"discountAmount\"> -$" + discountTotal + " </p>" + 
                  "<p class=\"discountText\">(" + discountText + ")</p><hr>\n";
  }

  // Display Total Price
  cartData += "<strong><p class=\"total\">Total:</p> " + "<p class=\"amount\">$" + total + "</p></strong>";

  // Update the cart display or show empty cart message
  if (counter > 0) {
      document.getElementById('cartData').innerHTML = cartData;
      document.getElementById('paymentColumn').style.visibility = 'visible';
  } else {
      document.getElementById('cartData').innerHTML = "<h1 align=\"center\"><br/>The cart is empty!<br/><br/></h1>";
      document.getElementById('paymentColumn').style.visibility = 'visible';
  }

  // Update payment button and total amount
  document.getElementById('payButton').value = "Pay $" + total.toString();
  document.getElementById('totalAmount').value = total;
}

// Array of field elements and their corresponding validation functions
const fields = [
  { id: 'email', validate: checkEmail },
  { id: 'firstName', validate: checkFirstName },
  { id: 'lastName', validate: checkLastName },
  { id: 'address', validate: checkAddress },
  { id: 'city', validate: checkCity },
  { id: 'state', validate: checkState },
  { id: 'zip', validate: checkZip },
  { id: 'discountCode', validate: checkDiscountCode }
];

// Attach input event listeners to each field, and validate on realtime
fields.forEach(field => {
  const element = document.getElementById(field.id);
  if (element) {
      element.addEventListener('input', () => {
          field.validate();
          if (field.id === 'discountCode') {
              updateCart();  // Update cart when discount code is validated
          }
      });
  }
});

// Remove an item from the cart and update
function removeFromCart(keyToRemove) {
  localStorage.removeItem(keyToRemove);
  updateCart();
}

// Utility function for setting field border color based on validity
function setBorderColor(element, isValid) {
  element.style.borderColor = isValid ? '#0F0' : '#F00';
}

// Field-specific validation functions
function checkDiscountCode() {
  const discountCode = document.getElementById('discountCode').value;
  const isValid = SHA1(discountCode) === 'SHA1HASHCODE';
  setBorderColor(document.getElementById('discountCode'), isValid);
  return isValid;
}

function checkCartItems(){
  const cartItems = document.getElementById('cartItems').value;
  const isValid = cartItems.length > 0;
  return isValid;
}

function checkFirstName() {
  const firstName = document.getElementById('firstName').value;
  const isValid = checkAlphaOnly(firstName);
  setBorderColor(document.getElementById('firstName'), isValid);
  return isValid;
}

function checkLastName() {
  const lastName = document.getElementById('lastName').value;
  const isValid = checkAlphaOnly(lastName);
  setBorderColor(document.getElementById('lastName'), isValid);
  return isValid;
}

function checkAddress() {
  const address = document.getElementById('address').value;
  const isValid = address.length > 4;
  setBorderColor(document.getElementById('address'), isValid);
  return isValid;
}

function checkCity() {
  const city = document.getElementById('city').value;
  const isValid = checkAlphaOnly(city);
  setBorderColor(document.getElementById('city'), isValid);
  return isValid;
}

function checkState() {
  const state = document.getElementById('state').value;
  const isValid = checkAlphaOnly(state) && state.length > 1;
  setBorderColor(document.getElementById('state'), isValid);
  return isValid;
}

function checkZip() {
  const zip = document.getElementById('zip').value;
  const isValid = checkNumberOnly(zip) && zip.length > 4;
  setBorderColor(document.getElementById('zip'), isValid);
  return isValid;
}

function showModal(title, body) {
  // Get modal elements
  const modal = document.getElementById('formValidationModal');
  const modalHeader = document.getElementById('modalHeader');
  const modalBody = document.getElementById('modalBody');

  // Set title and body content
  modalHeader.textContent = title;
  modalBody.textContent = body;

  // Show the modal
  modal.style.display = 'block';
  modal.classList.add('show');

  // Add event listener to close modal when clicking the close button or outside the modal
  const closeButtons = modal.querySelectorAll('.modalCloseButton, .modalXClose');
  closeButtons.forEach(button => {
      button.addEventListener('click', hideModal);
  });

  window.addEventListener('click', (event) => {
      if (event.target === modal) {
          hideModal();
      }
  });
}

function hideModal() {
  const modal = document.getElementById('formValidationModal');
  modal.style.display = 'none';
  modal.classList.remove('show');
}

// Validate Fields when "Pay" button is clicked
function payButtonClicked() {
  var title = 'Error';

  // Validation functions and corresponding error messages
  var checks = [
      { check: checkCartItems, message: "You have no items in your cart!" },
      { check: checkFirstName, message: "Please enter a valid first name" },
      { check: checkLastName, message: "Please enter a valid last name" },
      { check: checkEmail, message: "Please enter a valid email address" },
      { check: checkAddress, message: "Please enter a valid address" },
      { check: checkCity, message: "Please enter a valid city" },
      { check: checkState, message: "Please enter a valid state" },
      { check: checkZip, message: "Please enter a valid zip" }
  ];

  // Loop through validations
  for (var i = 0; i < checks.length; i++) {
      if (!checks[i].check()) {
          showModal(title, checks[i].message); // Show modal with error message
          updateCart(); // Update the cart
          return false; // Stop further processing
      }
  }

  // If checks pass, update the pay button value
  document.getElementById('payButton').value = 'Processing...';

  return true;
}

// Ensure First and Last Name, City, and State contain letters only
function checkAlphaOnly(name) { 
  var alphaCheck = /^[a-zA-Z\s]+$/;
  return alphaCheck.test(name);
}

// Validate Email Field
function checkEmail() {
  var emailInput = document.getElementById('email'); // Get email input element
  var emailAddress = emailInput.value; // Get the value of the input

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
  var sAddrSpec = sLocalPart + '\\x40' + sDomain; // Full RFC 822 email address spec
  var sValidEmail = '^' + sAddrSpec + '$';

  var reValidEmail = new RegExp(sValidEmail);

  // Validate email address
  var result = reValidEmail.test(emailAddress);

  // Provide visual feedback
  emailInput.style.borderColor = result ? '#0F0' : '#F00';

  return result; 
}

// Verify ZipCode field is numbers only
function checkNumberOnly(zip) {
  var numberCheck = new RegExp("^[0-9]+$");
  return numberCheck.test(zip);
}

/**
 * Computes the SHA-1 hash of a string.
 * Converts input to UTF-8 and processes it per SHA-1 specifications.
 * Returns a 40-character hexadecimal hash.
 */
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
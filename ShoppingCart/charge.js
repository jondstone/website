var stripe = Stripe('pk_live_key');
var elements;
var paymentElement;
var currentPaymentIntentId = null;

// Sets the text content of the pay button
function setPayButton(text) {
  const el = document.getElementById('payButtonText');
  if (el) el.textContent = text;
}

// Initializes the Stripe payment element with a new PaymentIntent
async function initializePaymentElement() {
  const totalAmount = document.getElementById('totalAmount').value;
  if (!totalAmount || totalAmount === '0') return;

  const discountCodeEl = document.getElementById('discountCode');
  const discountCode = discountCodeEl ? discountCodeEl.value : '';

  const formData = new FormData();
  formData.append('action', 'create_payment_intent');
  formData.append('amount', totalAmount + '00');
  formData.append('discountCode', discountCode);

  const response = await fetch('charge.php', { method: 'POST', body: formData });
  const data = await response.json();

  if (data.error) {
    elements = null;
    paymentElement = null;
    return;
  }

  currentPaymentIntentId = data.paymentIntentId;

  elements = stripe.elements({
    clientSecret: data.clientSecret,
    appearance: { theme: 'stripe' }
  });

  paymentElement = elements.create('payment', { layout: 'accordion' });
  paymentElement.mount('#payment-element');
}

// Handles form submission and payment confirmation
var form = document.getElementById('checkout');
form.addEventListener('submit', async function (event) {
  event.preventDefault();

  if (!payButtonClicked()) return;

  if (!elements) {
    await initializePaymentElement();
    if (!elements) return;
    setPayButton('Submit Payment');
    return;
  }

  const submitButton = document.getElementById('payButton');
  submitButton.disabled = true;
  setPayButton('Processing...');

  try {
    const { error, paymentIntent } = await stripe.confirmPayment({
      elements,
      confirmParams: {
        return_url:
          window.location.origin +
          window.location.pathname.replace('index.php', 'orderconfirmation.php'),
        receipt_email: document.getElementById('email').value
      },
      redirect: 'if_required'
    });

    if (error) {
      showModal('Payment Failed', error.message);
      submitButton.disabled = false;
      setPayButton('Submit Payment');
      return;
    }

    if (paymentIntent && paymentIntent.status === 'succeeded') {
      var hiddenInput = document.createElement('input');
      hiddenInput.type = 'hidden';
      hiddenInput.name = 'paymentIntentId';
      hiddenInput.value = paymentIntent.id;
      form.appendChild(hiddenInput);
      form.submit();
    }
  } catch (err) {
    showModal('Error', 'An unexpected error occurred.');
    submitButton.disabled = false;
    setPayButton('Submit Payment');
  }
});

// Initializes the cart on page load
document.addEventListener('DOMContentLoaded', function () {
  updateCart();
});

// Updates cart display, validates discount code, and calculates totals
async function updateCart() {
  "use strict";
  var cartData = "";
  var cartItems = '';
  var counter = 0;
  var total = 0.0;
  var discountTotal = 0.0;
  var discountText = '';
  var doesDiscountExist = false;

  var cartIconText = document.getElementById('cartIconText');
  if (cartIconText) {
    cartIconText.textContent = localStorage.length > 0 ? localStorage.length : '';
  }

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
    cartItems += name + "|" + productGroup + "|" + size + "|" + finish + "||";
    counter++;
  }

  document.getElementById('cartItems').value = cartItems;
  cartData += '<hr>';

  var discountCodeEl = document.getElementById('discountCode');
  var discountCode = discountCodeEl ? discountCodeEl.value : '';
  
  if (discountCode.trim() !== '') {
    const formData = new FormData();
    formData.append('action', 'validate_discount');
    formData.append('discountCode', discountCode);

    try {
      const response = await fetch('charge.php', { method: 'POST', body: formData });
      const data = await response.json();

      if (data.valid && data.discountPercent > 0) {
        var previousTotal = total;
        total = Math.floor(total * (1 - data.discountPercent));
        discountTotal = previousTotal - total;
        discountText = (data.discountPercent * 100) + "% Discount";
        doesDiscountExist = true;
        setBorderColor(document.getElementById('discountCode'), true);
        
        // Update existing PaymentIntent with new discounted amount
        if (currentPaymentIntentId) {
          const updateFormData = new FormData();
          updateFormData.append('action', 'update_payment_intent');
          updateFormData.append('paymentIntentId', currentPaymentIntentId);
          updateFormData.append('amount', total + '00');
          updateFormData.append('discountCode', discountCode);
          
          await fetch('charge.php', { method: 'POST', body: updateFormData });
        }
      } else if (data.valid && data.discountPercent === 0) {
        // Empty discount code - reset border to default
        setBorderColor(document.getElementById('discountCode'), null);
      } else {
        // Invalid discount code - show red border
        setBorderColor(document.getElementById('discountCode'), false);
      }
    } catch (error) {}
  } else {
    // No discount code entered - reset border to default
    if (discountCodeEl) {
      discountCodeEl.style.borderColor = '';
    }
  }

  if (doesDiscountExist) {
    cartData += "<strong><p class=\"discountHeader\">Discount:</p>" +
      "<p class=\"discountAmount\"> -$" + discountTotal + " </p>" +
      "<p class=\"discountText\">(" + discountText + ")</p><hr>\n";
  }

  cartData += "<strong><p class=\"total\">Total:</p> " + "<p class=\"amount\">$" + total + "</p></strong>";

  if (counter > 0) {
    document.getElementById('cartData').innerHTML = cartData;
    document.getElementById('paymentColumn').style.visibility = 'visible';
  } else {
    document.getElementById('cartData').innerHTML = "<h1 align=\"center\"><br/>The cart is empty!<br/><br/></h1>";
    document.getElementById('paymentColumn').style.visibility = 'visible';
  }

  if (elements) {
    setPayButton("Submit Payment");
  } else {
    setPayButton("Pay $" + total.toString());
  }

  document.getElementById('totalAmount').value = total;
}

// Array of form fields with their validation functions
const fields = [
  { id: 'email', validate: checkEmail },
  { id: 'firstName', validate: checkFirstName },
  { id: 'lastName', validate: checkLastName },
  { id: 'address', validate: checkAddress },
  { id: 'city', validate: checkCity },
  { id: 'state', validate: checkState },
  { id: 'zip', validate: checkZip }
];

// Attaches input event listeners to form fields for real-time validation
fields.forEach(field => {
  const element = document.getElementById(field.id);
  if (element) {
    element.addEventListener('input', () => {
      field.validate();
    });
  }
});

// Attaches input event listener to discount code field
const discountInput = document.getElementById('discountCode');
if (discountInput) {
  discountInput.addEventListener('input', () => {
    updateCart();
  });
}

// Removes an item from the cart and updates the display
function removeFromCart(keyToRemove) {
  localStorage.removeItem(keyToRemove);
  updateCart();
}

// Sets the border color of an element based on validation state
function setBorderColor(element, isValid) {
  if (!element) return;
  
  if (isValid === null) {
    // Reset to default
    element.style.borderColor = '';
  } else {
    element.style.borderColor = isValid ? '#0F0' : '#F00';
  }
}

// Checks if the cart contains any items
function checkCartItems() {
  return localStorage.length > 0;
}

// Validates the first name field
function checkFirstName() {
  var firstNameInput = document.getElementById('firstName');
  var firstName = firstNameInput.value.trim();
  var result = firstName.length > 0 && checkAlphaOnly(firstName);
  setBorderColor(firstNameInput, result);
  return result;
}

// Validates the last name field
function checkLastName() {
  var lastNameInput = document.getElementById('lastName');
  var lastName = lastNameInput.value.trim();
  var result = lastName.length > 0 && checkAlphaOnly(lastName);
  setBorderColor(lastNameInput, result);
  return result;
}

// Validates the address field
function checkAddress() {
  var addressInput = document.getElementById('address');
  var address = addressInput.value.trim();
  var result = address.length > 0;
  setBorderColor(addressInput, result);
  return result;
}

// Validates the city field
function checkCity() {
  var cityInput = document.getElementById('city');
  var city = cityInput.value.trim();
  var result = city.length > 0 && checkAlphaOnly(city);
  setBorderColor(cityInput, result);
  return result;
}

// Validates the state field
function checkState() {
  var stateInput = document.getElementById('state');
  var state = stateInput.value.trim();
  var result = state.length > 0 && checkAlphaOnly(state);
  setBorderColor(stateInput, result);
  return result;
}

// Validates the zip code field
function checkZip() {
  var zipInput = document.getElementById('zip');
  var zip = zipInput.value.trim();
  var result = zip.length === 5 && checkNumberOnly(zip);
  setBorderColor(zipInput, result);
  return result;
}

// Displays a modal dialog with a title and message
function showModal(title, body) {
  const modal = document.getElementById('formValidationModal');
  if (!modal) return;

  const header = document.getElementById('modalHeader');
  const content = document.getElementById('modalBody');

  if (header) header.textContent = title;
  if (content) content.textContent = body;

  modal.style.display = 'block';
  modal.classList.add('show');

  const closeButtons = modal.querySelectorAll('.modalCloseButton, .modalXClose');
  closeButtons.forEach(button => button.addEventListener('click', hideModal));

  window.addEventListener('click', (event) => {
    if (event.target === modal) hideModal();
  });
}

// Hides the modal dialog
function hideModal() {
  const modal = document.getElementById('formValidationModal');
  if (!modal) return;
  modal.style.display = 'none';
  modal.classList.remove('show');
}

// Validates all required fields when the pay button is clicked
function payButtonClicked() {
  var title = 'Error';

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

  for (var i = 0; i < checks.length; i++) {
    if (!checks[i].check()) {
      showModal(title, checks[i].message);
      updateCart();
      return false;
    }
  }

  return true;
}

// Validates that a string contains only alphabetic characters and spaces
function checkAlphaOnly(name) {
  var alphaCheck = /^[a-zA-Z\s]+$/;
  return alphaCheck.test(name);
}

// Validates the email address field using RFC 822 specification
function checkEmail() {
  var emailInput = document.getElementById('email');
  var emailAddress = emailInput.value;

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
  var result = reValidEmail.test(emailAddress);

  emailInput.style.borderColor = result ? '#0F0' : '#F00';
  return result;
}

// Validates that a string contains only numeric characters
function checkNumberOnly(zip) {
  var numberCheck = new RegExp("^[0-9]+$");
  return numberCheck.test(zip);
}
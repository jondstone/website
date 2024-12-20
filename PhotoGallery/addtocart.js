// Load Google Maps API
(function(g) {
    var h, a, k, p = "The Google Maps JavaScript API", c = "google", l = "importLibrary", q = "__ib__", m = document, b = window;
    b = b[c] || (b[c] = {}); var d = b.maps || (b.maps = {}), r = new Set, e = new URLSearchParams;
    u = () => h || (h = new Promise(async (f, n) => {
      await (a = m.createElement("script"));
      e.set("libraries", [...r] + "");
      for (k in g) e.set(k.replace(/[A-Z]/g, t => "_" + t[0].toLowerCase()), g[k]);
      e.set("callback", c + ".maps." + q);
      a.src = `https://maps.${c}apis.com/maps/api/js?` + e;
      d[q] = f;
      a.onerror = () => h = n(Error(p + " could not load."));
      a.nonce = m.querySelector("script[nonce]")?.nonce || "";
      m.head.append(a)
    }));
    d[l] ? console.warn(p + " only loads once. Ignoring:", g) : d[l] = (f, ...n) => r.add(f) && u().then(() => d[l](f, ...n));
  })({
    key: "googleapikey",
    v: "weekly",
  });

// Call Shopping Cart on page load
document.addEventListener('DOMContentLoaded', function () {
    updateCart();
});

// Handles new drop-down upon selection
document.addEventListener("DOMContentLoaded", function () {
    var styleSelect = document.querySelector('select[name=style]');
    var finishSelect = document.querySelector('select[name=finish]');
    var sizeSelect = document.querySelector('select[name=size]');
    var showSizeSelections = document.getElementById('showSizeSelections');
    var printCost = document.getElementById('printCost');
    var cartButton = document.getElementById('cartButton');
    var showSize = document.getElementById('showSize');

    // Updates Finish options based on selected Style
    styleSelect.addEventListener('change', function () {
        var selectedStyle = this.selectedOptions[0];
        var rel = selectedStyle.getAttribute('rel');
        var relatedOptions = finishSelect.querySelectorAll('option.' + rel);

        // Hide Finish if no related options
        if (relatedOptions.length === 0) {
            finishSelect.style.display = 'none';
            return;
        }

        // Show related Finish options
        finishSelect.style.display = 'block';
        Array.from(finishSelect.querySelectorAll('option')).forEach(function (option) {
            option.style.display = 'none';
        });
        relatedOptions.forEach(function (option) {
            option.style.display = 'block';
        });
        relatedOptions[0].selected = true;

        // Reset Size options when style changes
        sizeSelect.style.display = 'none';
        showSizeSelections.style.display = 'none';
        printCost.innerHTML = '';
        cartButton.innerHTML = '';
    });

    // Updates Size options based on selected Finish
    finishSelect.addEventListener('change', function () {
        var selectedFinish = this.selectedOptions[0];
        var rel = selectedFinish.getAttribute('rel');
        var relatedOptions = sizeSelect.querySelectorAll('option.' + rel);

        // Hide Size if no related options
        if (relatedOptions.length === 0) {
            sizeSelect.style.display = 'none';
            return;
        }

        // Show related Size options
        sizeSelect.style.display = 'block';
        Array.from(sizeSelect.querySelectorAll('option')).forEach(function (option) {
            option.style.display = 'none';
        });
        relatedOptions.forEach(function (option) {
            option.style.display = 'block';
        });
        relatedOptions[0].selected = true;

        showSizeSelections.style.display = 'block';
        showSize.innerHTML = '<h4>3. Select a Size</h4>';
        printCost.innerHTML = '';
        cartButton.innerHTML = '';
    });
});


// Global Vars
let printStyle,
    abbrvFinish,
    printSize,
    printPrice,
    _toCart,
    finishCounter = 0,
    sizeCounter = 0;

/**
 * Handles the selection of print style.
 * Updates the print style based on the user's selection from the dropdown.
 * If a finish has already been selected, it resets the size selection.
 * Otherwise, it displays the finish selection option.
 */
function showStyle(element) {
    printStyle = element.options[element.selectedIndex].text;

    // Reset Finish and Size if already selected
    if (finishCounter > 0) {
        resetSelections('size');
    } else {
        document.getElementById('showFinish').innerHTML = '<h4>2. Select a Finish</h4>';
    }
    finishCounter++;
}

/**
 * Handles the selection of print finish.
 * Maps the selected finish style to its corresponding abbreviation (Luster -> L, Metallic -> M).
 * If a size is already selected, it resets the price selections. 
 * Otherwise, it displays the size selection options.
 */
function showFinish(element) {
    var finish_Style = element.options[element.selectedIndex].text;

    // Map Finish selection to abbreviation
    abbrvFinish = finish_Style === 'Luster' ? 'L' : finish_Style === 'Metallic' ? 'M' : '';

    // Reset Size if already selected
    if (sizeCounter > 0) {
        resetSelections('price');
    } else {
        document.getElementById('showSize').innerHTML = '<h4>3. Select a Size</h4>';
        document.getElementById('showSizeSelections').style.display = 'block';
    }
    sizeCounter++;
}

/**
 * Handles the selection of Print Size and Price, 
 * updates the displayed total price and prepares the 
 * Add to Cart button with the selected item details.
 *
 * @param {HTMLSelectElement} element - The select element containing size and price options.
 */
function showSize(element) {
    var szpr_Selection = element.options[element.selectedIndex].text.split(' ');
    printSize = szpr_Selection[0];
    printPrice = szpr_Selection[1]?.replace('$', '');

    // If Price exists, display it and prepare Add to Cart button
    if (printPrice) {
        document.getElementById('printCost').innerHTML = `<h4>&nbsp;Total: $${printPrice}</h4>`;
        _toCart = `${print_Name}_${printStyle}_${abbrvFinish}_${printSize}_${printPrice}`;
        document.getElementById('cartButton').innerHTML = `
            <div class='addToCartButton'>
                <a style='text-decoration:none;' onclick='addToCart("${_toCart}")'>Add To Cart</a>
            </div>`;
    }
}

/**
 * Resets the selections and UI elements based on the specified level.
 * @param {string} level - The level to reset ('size' or 'price').
 */
function resetSelections(level) {
    if (level === 'size' || level === 'price') {
        document.getElementById('printCost').innerHTML = '';
        document.getElementById('cartButton').innerHTML = '';
    }
    if (level === 'size') {
        document.getElementById('showSize').innerHTML = '';
        document.getElementById('showSizeSelections').style.display = 'none';
    }
}

/**
 * Initializes tooltips and updates the shopping cart.
 * 
 * - Tooltips are shown when hovering over elements with the 'data-toggle="tooltip"'
 * - Tooltips are removed when the mouse leaves the element.
 * - Calls the updateCart function to update the cart's contents.
 * 
 * This code uses vanilla JavaScript for DOM manipulation and event handling.
 */	
document.addEventListener('DOMContentLoaded', function () {
    // Initialize tooltips for elements with the 'data-toggle="tooltip"'
    document.querySelectorAll('[data-toggle="tooltip"]').forEach(element => {
        element.addEventListener('mouseenter', () => {
            const tooltip = document.createElement('span');
            tooltip.textContent = element.getAttribute('title');
            tooltip.className = 'tooltip';
            element.appendChild(tooltip);
        });
        element.addEventListener('mouseleave', () => {
            const tooltip = element.querySelector('.tooltip');
            if (tooltip) tooltip.remove();
        });
    });

    // Call updateCart function
    updateCart();
});

/**
 * Adds an item to the shopping cart.
 * Stores the item in localStorage with a unique ID, displays a success toast message,
 * and updates the cart display.
 * 
 * @param {string} itemName - The name of the item to add to the cart.
 */
function addToCart(itemName) {
    // Store item in localStorage with a unique ID
    localStorage.setItem(generateUUID(), itemName);
    showToast('Item was added to cart', 'success');
    updateCart();
}

// Vanilla JS function to show a toast notification
function showToast(message, type) {
    const toast = document.createElement('div');
    toast.classList.add('toast', type);
    toast.textContent = message;

    // Style and append toast to body
    toast.style.position = 'fixed';
    toast.style.bottom = '20px';
    toast.style.left = '50%';
    toast.style.transform = 'translateX(-50%)';
    toast.style.backgroundColor = type === 'success' ? 'green' : 'red';
    toast.style.color = 'white';
    toast.style.padding = '10px';
    toast.style.borderRadius = '5px';
    toast.style.zIndex = '1000';

    document.body.appendChild(toast);

    // Remove toast from view after 3 seconds
    setTimeout(() => {
        toast.remove();
    }, 3000);
}

/**
 * Updates the shopping cart icon to reflect the number of items in localStorage.
 * If there are items in the cart, the cart icon will show the item count.
 * If the cart is empty, the icon text will be cleared.
 */
function updateCart() {
    var cartIcon = document.getElementById('cartIconText');

    // Check if there are items in localStorage
    if (localStorage.length > 0) {
        cartIcon.textContent = localStorage.length;
    } else {
        cartIcon.textContent = '';  // Clear the cart icon text if empty
    }
}

/**
 * Generates a unique identifier (UUID) using the current timestamp and random values.
 * If available, uses high-precision timing to ensure uniqueness.
 * @returns {string} A UUID string in the format 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.
 */
function generateUUID() {
    var d = new Date().getTime();  // Current timestamp
    if (window.performance && typeof window.performance.now === "function") {
        d += performance.now();  // Use high precision timer if available
    }
    var uuid = 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function (c) {
        var r = (d + Math.random() * 16) % 16 | 0;
        d = Math.floor(d / 16);
        return (c === 'x' ? r : (r & 0x3 | 0x8)).toString(16);
    });
    return uuid;
}
# Shopping Cart and Checkout Example
This repository contains the code for the "Shopping Cart checkout" portion on my website. The primary goal of this project was to enhance performance, remove unnecessary dependencies, and clean up the codebase for better maintainability and readability.

### Inside this folder contains the working files needed for the shopping cart and checkout feature I built for my website. [www.jondstone.com](https://www.jondstone.com)
* cart.css (This file contains the CSS needed to stylize the shopping cart and order pages)
* cart.php (The shopping cart checkout page - contains base HTML code)
* charge.js (The first part is JS provided by Stripe, and the second half is code I wrote to update the shopping cart and validate all fields before checkout)
* charge.php (The main PHP code that calls Stripe's API, charges the credit card and returns success/failure along with a generated email)
* discountvalidation.php (A fail-safe in case the user manipulates the Discount Code JavaScript but does not know the actual Discount Code.)
* error.php (This page will display a unique error to the user depending on what Stripe's API sends back.)
* orderconfirmation.php (The page that displays if the checkout is successful.)

## Changes Made

### 1. **Dependencies Removed**
- Removed reliance on **Bootstrap** and **jQuery**.
- All JavaScript has been rewritten using **vanilla JavaScript** to streamline functionality and reduce external dependency overhead. These changes significantly enhance page load times and removes breaking change conflicts.

### 2. **Shopping Chart Checkout Page**
- Refactored the entire logic:
  - Rebuilt using vanilla JavaScript.
  - Improved code efficiency and cleanliness.
  - Addressed and resolved pre-existing bugs.

### 3. **Server Side Checkout**
- Refactored the entire logic:
  - A PHP script, `charge.php`, handles the server side checkout
  - Steps in the logic:
    1. **Field Value Sanitization**: Ensures safe handling of user input.
    2. **Charge Customer**: If certain conditions are met, it will charge the customer, and return a success or failure code
    3. **Send Email**: Function that initially sends me an email when the process starts, and then if successful sends a confirmation email to the customer
#Shopping Cart and Checkout Example

###Inside this folder contains the working files needed to replicate the shopping cart and checkout feature I wrote for my website. [www.jondstone.com](https://www.jondstone.com)
###Each file will a comment block in the header detailing how the code works.
* cart.css (This file contains the CSS needed to stylize the "add to cart" button feature and "shopping cart" page.)
* cart.php (The shopping cart checkout page, this contains JavaScript and HTML showing how the cart works.)
* charge.php (The main PHP code that calls Stripe's API and creates the check-out.)
* discountvalidation.php (A fail-safe in case the user manipulates the Discount Code JavaScript but does not know the actual Discount Code.)
* error.php (This page will display a unique error to the user depending on what Stripe's API passes back, if it fails to charge their card.)
* individualgallerypage.php (This is a JavaScript and HTML example of what is needed to construct the drop-down selections I use, and the "Add to Cart".)
* orderconfirmation.php (The page that displays if the checkout is successful.)
* style.css (The main CSS file that drives my website and part of the Shopping Cart.)
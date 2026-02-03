### Inside this folder contains the working files needed for the shopping cart and checkout feature I built for my website.  
[www.jondstone.com](https://www.jondstone.com)

- **cart.css**  
  CSS styling for the shopping cart layout, checkout form, validation states, and modal dialogs.

- **cart.php**  
  Main checkout page containing the base HTML structure for the cart, customer fields, and Stripe Payment Element container.

- **charge.js**  
  Client-side logic for cart management, field validation, Stripe PaymentIntent initialization, and real-time checkout updates.

- **charge.php**  
  Server-side checkout handler that validates input, manages Stripe PaymentIntents, processes payments, sends emails, and returns results.

- **orderresult.php**  
  Displays the final checkout result state (success or error) returned from the server.

## All Changes Made

### 2024 Changes

1. **Dependencies Removed**
   - Removed Bootstrap and jQuery.
   - Rewritten using vanilla JavaScript to reduce overhead and dependency conflicts.

2. **Shopping Cart Checkout Page**
   - Complete client-side refactor.
   - Improved performance, structure, and fixed legacy bugs.

3. **Server-Side Checkout**
   - Centralized payment processing in `charge.php`.
   - Input sanitization, charge execution, and email handling unified.

### 2026 Changes

1. **Stripe Upgrade**
   - Migrated legacy Stripe integration to Stripe PaymentIntents.

2. **Discount Handling**
   - Removed discount hash from the UI.
   - Discount validation moved server-side with live validation and PaymentIntent updates.

3. **Email Updates**
   - Refactored customer confirmation email to a modern layout.

4. **Result Page Refactor**
   - Consolidated three result pages into a single `orderresult` page.
   - Upon successful checkout, the items in the shopping cart is now cleared.

5. **Error Handling**
   - Added server-side error logging for diagnostics and recovery.
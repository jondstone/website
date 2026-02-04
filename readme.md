### Inside this repository contains selected projects and working files I built for my photography and documentary website.  
[www.jondstone.com](https://www.jondstone.com)

- **/Comments/**  
  Custom built PHP commenting system used across the Urban Exploration pages.

- **/ContactForm/**  
  PHP and JavaScript code for the site contact form and validation logic.

- **/MiniGallery/**  
  PHP and JavaScript code for the MiniGallery used on Real Estate and Weddings pages.

- **/Misc/**  
  Miscellaneous PHP and JavaScript utilities used throughout the site.

- **/PhotoGallery/**  
  PHP, JavaScript, and CSS powering the primary photo portfolio pages.

- **/Search/**  
  PHP, JavaScript, and CSS for the custom built site search engine.

- **/ShoppingCart/**  
  Custom shopping cart and checkout system with Stripe integration.

- **/UrbexGallery/**  
  PHP and JavaScript photo gallery used throughout the Urban Exploration pages.

## All Changes Made

### Initial Site Redesign (2019)

1. **Full Website Overhaul**
   - Merged *Jon Stone Photography* and *Forgotten Southeast* into a single site.
   - Entire site hand-coded with no templates.

2. **Custom Development**
   - Thousands of lines of PHP, JavaScript, HTML, and CSS rewritten.
   - Refactored Urban Exploration gallery JavaScript for performance.
   - Image assets optimized for faster load times.

3. **Photo Gallery**
   - New gallery dedicated to photos available for purchase.
   - Individual photo pages include descriptions, interactive Google Maps integration, and dynamic add-to-cart options.

4. **E-Commerce System**
   - Replaced static PayPal buttons with a dynamic PHP/JavaScript driven checkout.
   - Centralized pricing and option management.
   - Custom checkout flow utilizig Stripe payment processing.

5. **Security and Analytics**
   - Input sanitization for all user-submitted data.
   - Security headers implemented site-wide.
   - Google Analytics integration.

### Modernization and Optimization (2024)

1. **JavaScript Refactor**
   - Complete rewrite to vanilla JavaScript.
   - Removed Bootstrap and jQuery dependencies.
   - Major rewrites for shopping cart, photo galleries, and Urbex galleries.

2. **Contact Form Hardening**
   - Improved bot mitigation.
   - Eliminated spam submissions.

3. **CSS Cleanup**
   - Removed unused styles.
   - Renamed selectors for clarity.
   - Updated visual presentation.
   - All pages made mobile-friendly.

4. **PHP Refactor**
   - Removed redundant logic.
   - Improved structure and readability.

### Search and UI Expansion (2025)

1. **Custom Search Engine**
   - Designed and implemented a custom crawler.
   - Built a ranking and sorting algorithm.
   - Iterative testing to refine relevance.
   - Custom front-end search UI.

2. **Header and Footer Redesign**
   - New navigation system for desktop and mobile.
   - Cleaner structure and more modern appearance than the 2019 design.

### Platform Consolidation (2026)

1. **Shopping Cart Checkout**
   - Migrated legacy Stripe code to their new Payment Intents API.
   - Fixed legacy bug preventing cart clearing after successful checkout.

2. **Custom Comment System**
   - Replaced Disqus, removing the last third-party frontend dependency, and reducing page load overhead by eliminating their assets.
   - Full control over moderation, security, and UI.
   - Introduced a modern, lightweight commenting system tailored to the site.
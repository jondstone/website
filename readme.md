# This repo contains selected code I've written for my photography and documentary website

### Each Folder will contain a sub-project, with more to be added in the future.
* ContactForm (Folder contains PHP and JS code for the Contact Form and Validation)
* MiniGallery (Folder contains PHP and JS code for MiniGallery used on the Real Estate and Weddings pages)
* Misc (Folder contains Misc. PHP and JS code used throughout my website)
* PhotoGallery (contains PHP, JS and CSS code for the Photo Gallery portion of my website)
* ShoppingCart (This folder contains code I've written for a custom built shopping cart and checkout feature.)
* UrbexGallery (Folder contains PHP and JS code for the Gallery used throughout the Urban Exploration pages)

---------------------------------------

# 2019 Design:
Starting in late 2018 through early 2019, I undertook a complete overhaul of my website, merging two older sites — **Jon Stone Photography** and **Forgotten Southeast** — into a single cohesive site. This new website was entirely hand-coded, with no templates used at any stage.

## Key Highlights

### Custom Coding
- Thousands of lines of **PHP**, **JavaScript**, **HTML**, and **CSS** were meticulously written by hand.
- Existing JavaScript for the **Photo Galleries** in the Urban Exploring section was refactored for better performance, and images were optimized for faster loading times.

### New Photo Gallery for Sale Items
- Created a new gallery dedicated to displaying photos available for purchase.
- Each photo page includes:
  - A **description** of the image.
  - An **interactive map** (powered by Google Maps API, limited to my domain) showing the approximate location of the photo.
  - **Dynamic shopping cart functionality** allowing users to select size, finish, and other options before adding items to the cart.

### Enhanced E-Commerce System
- Transitioned from the 2011 implementation that relied on static PayPal "Add to Cart" buttons:
  - The old system required unique buttons for each page and lacked dynamic selection capabilities.
- Introduced a **single dynamic file** using JavaScript and PHP:
  - Users can dynamically select styles, finishes, and sizes via dropdown menus.
  - Updates to pricing or options are managed in one file, simplifying maintenance.
- Checkout process:
  - Custom-built checkout page where users:
    - Review their cart.
    - Apply discount codes.
    - Enter payment information securely handled by **Stripe's API**.
  - Orders are generated seamlessly upon payment completion.

### Security and Analytics
- **Data Sanitization**: All user input is sanitized before processing to protect against XSS and other malicious attacks.
- **Security Enhancements**: Implemented robust security headers for additional protection.
- **Google Analytics**: Added to provide insights into website usage and user behavior.
---
This overhaul has resulted in a faster, more secure, and feature-rich platform that is easier to maintain and provides a better user experience. All of these enhancements were designed and implemented with the goal of creating a modern, functional website while retaining full control over its features and design.

---------------------------------------

# 2024 Update:
In 2024, I focused on modernizing and optimizing my website by refactoring all JavaScript into vanilla JS to improve performance and remove external dependencies, enhancing the contact form to combat spam bots, refining CSS for clarity and a modern aesthetic, making several pages mobile-friendly, and streamlining PHP code for efficiency and readability.

## Key Highlights

### Refactored JavaScript
- All JavaScript has been rewritten in **vanilla JS**, eliminating the need for **Bootstrap** and **jQuery**.
  - This reduces file sizes, improves loading times, and removes dependency on external libraries.
  - Major rewrites include:
    - **Shopping cart functionality**.
    - **Photo gallery pages**.
    - **Urban exploration gallery pages**.
    - Various other components across the website.

### Contact Form Improvements
- Updated the contact form to mitigate spam bots, resulting in no spam submissions and improved communication reliability.

### CSS Refinements
- Refactored parts of the CSS:
  - Removed unnecessary styles.
  - Renamed fields for better clarity.
  - Added a "modern flair" to previously outdated pages.
  - Made several pages **mobile-friendly**, improving usability on smaller screens.

### PHP Optimization
- Cleaned up and refactored all PHP code:
  - Reduced redundant or unnecessary code.
  - Added comments for better readability and maintainability.

---------------------------------------

## TODO

### Q1 2025
- Ensure all pages are fully **mobile-friendly** for both tablets and smartphones.
- Address any remaining issues required for **Google SEO** compliance.

### Q1–Q2 2025
- Develop a custom **search engine** for the website.
- Update checkout pages:
  - Ensure the shopping cart clears items after successful checkout and payment.
- Add support for **international addresses** and **credit cards**.

### 2025 or Later
- Replace **Disqus** with a custom-built **commenting system** for the urban exploration pages.
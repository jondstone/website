# Gallery Photo Pages Rebuild
This repository contains the rebuilt code for the "Gallery Photo" pages on my website. The primary goal of this project was to enhance performance, remove unnecessary dependencies, and clean up the codebase for better maintainability.

### Inside this folder contains the working files for the Gallery Photo pages on my website. [www.jondstone.com](https://www.jondstone.com)
* galleryphotopage.php (This file contains the PHP code for the above mentioned pages)
* galleryquery.php (This PHP contains the functions used to construct the Navigation for each gallery page)
* photogallery.css (Contains the CSS for the Photo Gallery pages)
* addtocart.js (Contains the JS for the addtocart function, and the drop-down selections)

## Changes Made

### 1. **Dependencies Removed**
- Removed reliance on **Bootstrap** and **jQuery**.
- All JavaScript has been rewritten using **vanilla JavaScript** to streamline functionality and reduce external dependency overhead.

### 2. **Dropdown Selection**
- Refactored the dropdown selection logic:
  - Rebuilt using vanilla JavaScript.
  - Improved code efficiency and cleanliness.
  - Addressed and resolved a pre-existing bug.

### 3. **Gallery Navigation**
- **Navigation logic** is now driven by **MySQL**:
  - A PHP script, `galleryquery.php`, handles the gallery data and navigation.
  - Steps in the logic:
    1. **Input URL Sanitization**: Ensures safe handling of user input.
    2. **Gallery Name Determination**: Extracts the gallery name based on the input URL. If no matching gallery is found, it defaults to a pre-configured fallback.
    3. **Database Query**: Fetches necessary navigation details (e.g., left and right navigation) from the MySQL database
    4. **SetNav Query**: Sets the left and right navigation based on the results from above
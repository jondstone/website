# MiniGallery PHP and JS 

### Inside this folder contains the working files for the Real Estate and Wedding pages on my website. [www.jondstone.com](https://www.jondstone.com)
* index.php (This file contains the PHP code for the above mentioned page(s))
* minigallery.css (Contains the CSS for the MiniGallery)
* minigallery.js (Contains the JS for the MiniGallery)

This is a rebuild of existing code utilize for the "Mini Gallery" that was being used on the Real Estate and Wedding pages. At the bottom of the physical page
there was a "gallery" of 4 photos per row. Clicking the photo loaded a modal popup that allowed the user to navigate through the gallery to see samples of my work.

The original code was rather messy and depended on JQuery, which was required at loadtime. This made the gallery unresponive at times. 

So I refactored the code to break the dependency on JQuery and to simplify the code. The image gallery now loads exceptionally fast on click and navigation.
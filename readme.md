# This repo contains selected code I've written for my photography and documentary website

### Each Folder will contain a sub-project, with more to be added in the future.
* Cart (This folder contains code I've written for a custom built shopping cart and checkout feature.)
* ContactForm (Folder contains PHP and JS code for the Contact Form and Validation)
* SlideshowGallery (Folder contains PHP and JS code to create a smooth fading slideshow on my website)

Starting in late 2018 through early 2019 I completed an overhaul on my website, where I combined two of my older sites (Jon Stone Photography and Forgotten Southeast) into one. This website was built and coded entirely by hand, with no templates being used. 

Some existing JavaScript is still being used for the Photo Galleries in the Urban Exploring section. However, this was refactored as well and the images were optimized to allow for quick loading. Outside of that, thousands and thousands of lines of PHP, JavaScript, HTML, and CSS were all written by hand!

I created a new gallery to house all photos of mine that are currently for sale. Each respective photo page has a description, a map showing the approximate location (using Google Map's API, limited to my domain) and the ability for the end-user to add the image to a shopping cart (after selecting size, finish, etc.)

When I originally built my website in 2011, each page had a PayPal "add to cart" button, which did not allow the creation of unique choices. In addition I had to create a unique button for each page. Now, a single file is driven with JavaScript and PHP to each individual gallery page that allows the user to dynamically go through drop-downs to select the style, finish, and size that they want. If I need to change a price, or add a new style or finish, then I make a change to one file, instead of many. Once the user has added items to the cart, they checkout through a custom built page. Here, they can enter their information (including a discount code) and view the items in the cart. Then they enter their payment information (stored and handled by Stripe's API), which generates an order.

Elsewhere, all data input is santized before being passed on. I've added protection against XSS and other malicious attacks to my security headers. Also, I have added Google Analytics to the site. All new features that my existing websites never had.
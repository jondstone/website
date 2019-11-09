# This repo contains selected code from my website

### Each Folder will contain a sub-project, with more to be added in the future.
* Cart (This folder contains code I've written for a custom built shopping cart and checkout feature.)
* Misc (This folder contains misc. coding examples for my website.)

Starting in late 2018 through early 2019 I completed an overhaul on my website, where I combined two older sites into one. The website was coded entirely by hand, with no templates being used. I did start with the original code base I wrote in 2011 but completely refactored it. Some existing JavaScript is still being used for the Photo Galleries in the Urban Exploring section. However, this was refactored as well and the images were optimized to allow for quick loading. Outside of that, thousands and thousands of lines of PHP, JavaScript, HTML, and CSS were all written by hand!

I created a new gallery page to house all pictures that I am currently selling. Each page has a description about the picture, a map showing the approximate location (using Google Map's API, limited to my domain) and a way for the user to add the item to a shopping cart. 

When I originally built my website in 2011, each page had a PayPal "add to cart" button, which did not allow the creation of many choices. In addition I had to create a unique button for each page. Now, a single file is driven with JavaScript and PHP to each individual gallery page that allows the user to dynamically go through drop-downs to select the style, finish, and size that they want. If I need to change a price, or add a new style or finish, then I make a change to one file, instead of many. Once the user has added items to the cart, they checkout through a custom built page. Here, they can enter their information (including a discount code) and view the items in the cart. Then they enter their payment information (stored and handled by Stripe's API), which generates an order.

Elsewhere, all data input is santized before being passed on. I've added protection against XSS and other malicious attacks to my security headers. Also, I have added Google Analytics to the site. All new features that my existing websites never had.

### To Do:
* Add a database in MySQL for each Gallery (i.e. Best Sellers, Landscapes, Wildlife, etc.). This database will store all the unique pages in that gallery.
* Then implement a previous/next button on each individual page inside of that gallery. This will allow the user to traverse through all the images
* Show a count (i.e. 4/26) at the bottom so they know how many images are inside this gallery and which one they are currently viewing

Currently the user has to click back to the gallery and then click on a new image to view it. This is not user friendly, but I did not consider this to be part of the MVP.
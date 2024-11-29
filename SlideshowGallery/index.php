<!DOCTYPE html>
<html lang="en-US">
<head>
	<!-- 
	Wrote JS to display a slideshow on my homepage and elsewhere.
	All photos except for the first are hidden; and then as each image displays 
	in the slideshow, there is a smooth fade transition.
	Set timers as variables so it is easy to change.
	Code can handle a multitude of images. 
	-->
    <!-- Header content removed as it's not necessary to be displayed in Git. -->
</head>

<body>
	<noscript>
        Javascript is currently disabled. This site requires Javascript to be enabled.
    </noscript>

	<div id="content-home">
		<div id="slideshow">
			<!-- Set the first image as visible along with the transition -->
			<img src="image1.jpg" alt="" style="visibility: visible; opacity: 1; transition: opacity 1.5s;">
			<img src="image2.jpg" alt="" style="visibility: hidden; opacity: 0;">
			<img src="image3.jpg" alt="" style="visibility: hidden; opacity: 0;">
			<img src="image4.jpg" alt="" style="visibility: hidden; opacity: 0;">
			<img src="image5.jpg" alt="" style="visibility: hidden; opacity: 0;">
		</div>
	</div>

	<script>
		window.onload = function() {
			let images = document.querySelectorAll('#slideshow img'); // Get all images in the slideshow
			let currentIndex = 0; // Set starting index
			const interval = 3000; // Set time interval
			const transitionDuration = 1500; // Set transition duration 

			// Function to display the next image in the slide
			function nextSlide() {
				images[currentIndex].style.transition = `opacity ${transitionDuration}ms`; 
				
				// Hide the current image (fade out)
				images[currentIndex].style.opacity = 0;

				// Update the index to the next image
				currentIndex = (currentIndex + 1) % images.length;

				// Show the next image (fade in)
				images[currentIndex].style.visibility  = 'visible';
				images[currentIndex].style.transition = `opacity ${transitionDuration}ms`; 
				images[currentIndex].style.opacity = 1; // Fade in the next image
			}

			// Initialize the slideshow by setting the first image to be visible
			images[currentIndex].style.opacity = 1;
			images[currentIndex].style.visibility = 'visible'; 

			// Start the slideshow after a brief delay
			setTimeout(() => {
				setInterval(nextSlide, interval); // Start the loop
			}, 100); // Quick delay before starting the slideshow
		};
	</script>

</body>
</html>
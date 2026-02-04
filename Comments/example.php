<?php
	// Forces a last modified time for GA/GS
	$lastModifiedTime = filemtime(__FILE__);
	header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $lastModifiedTime) . ' GMT', true);

	// Required Data for Comment System
	require $_SERVER['DOCUMENT_ROOT'] . 'config/csrf.php';
	$page_key = 'page_key';
	$api_base = 'public';
	$ugc_page_key = htmlspecialchars($page_key, ENT_QUOTES, 'UTF-8');
	$ugc_api_base = htmlspecialchars($api_base, ENT_QUOTES, 'UTF-8');
	$ugc_csrf     = htmlspecialchars(Csrf::token(), ENT_QUOTES, 'UTF-8');
	$ugc_timing   = htmlspecialchars(Timing::token($page_key), ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="en-US">
<head>
	<!-- Header Information Redacted -->
</head>

<body>
	<noscript>
        Javascript is currently disabled. This site requires Javascript to be enabled.
    </noscript>

	<div id="urbexPageContent">
		<h1>Spectre, Alabama</h1>
		<br />
		<img src="images/spectre/spectre.jpg"
			alt="Spectre Alabama"
			style="float: left; padding-right: 10px; max-width: 50%; height: auto;" />
		Spectre, Alabama is a fictional town and custom movie set created for Tim Burton's fantasy film <i>Big Fish</i>. It is located on Jackson 
		Lake Island in the middle of a river, just outside Montgomery. 
		<br /><br /> 
		During the movie, Edward Bloom (played by both Ewan McGregor and Albert Finney) visited the hidden town at two different points in his life. 
		He first stumbled upon it when he was "too early" and it was a perfect paradise, then visited again later when he was "too late" and 
		the town had started to fade. 
		<br /><br /> 
		To the left is a still photo from the movie showing the town of Spectre in its prime, before the "outsiders" came and ruined it. 
		<br /><br /> 
		Today the set sits derelict on a private island. Most of the outbuildings have collapsed or been burned by lightning over the years. 
		The movie magic has worn thin as the fake Spanish Moss-covered trees have started to fall apart, exposing the Styrofoam underneath 
		and showing they were always just part of the set.
		<br /><br />
		<p class="courtesynote">Note: This movie set sits on a private island. You will need permission from the land owners to visit.</p>

		<h2>Photo Gallery</h2>
		<br /><br />

		<div class="urbexGallery-container">
			<div class="thumbnail-container">
				<div class="arrow-thumbnail left"></div>
				<div class="thumbnail-strip">
					<img src="images/spectre/1.jpg" alt="Main Street" />
					<img src="images/spectre/2.jpg" alt="Moss" />
					<img src="images/spectre/3.jpg" alt="Porch Swing" />
					<img src="images/spectre/4.jpg" alt="Curtains" />
					<img src="images/spectre/5.jpg" alt="Old Store" />
					<img src="images/spectre/6.jpg" alt="Store Facade" />
					<img src="images/spectre/7.jpg" alt="House" />
					<img src="images/spectre/8.jpg" alt="House" />
					<img src="images/spectre/9.jpg" alt="Porch" />
					<img src="images/spectre/10.jpg" alt="Church" />
					<img src="images/spectre/11.jpg" alt="Church" />
					<img src="images/spectre/12.jpg" alt="Jenny's House" />
					<img src="images/spectre/13.jpg" alt="Jenny's House" />
					<img src="images/spectre/14.jpg" alt="Trees" />
					<img src="images/spectre/15.jpg" alt="Moss" />
				</div>
				<div class="arrow-thumbnail right"></div>
			</div>
			<div class="urbexGallery">
				<div class="arrow-container left-arrow-container">
					<div class="arrow left"></div>
				</div>
				<!-- Dynamically populated by JS -->
				<img id="urbexGallery-image" src="" alt="Gallery Image">
				<div class="description"></div>
				<!-- Dynamically populated by JS -->
				<div class="arrow-container right-arrow-container">
					<div class="arrow right"></div>
				</div>
			</div>
		</div>

		<script type="text/javascript" src="urbexgallery.js"></script>

		<br /><br /><br />
		If you would like to comment on this article, you may do so below. You can comment and reply as a guest (name required).

		<input type="hidden" id="ugc-csrf" value="<?=$ugc_csrf?>">
		<input type="hidden" id="ugc-timing" value="<?=$ugc_timing?>">

		<div id="ugc-comments"
			data-page-key="<?=$ugc_page_key?>"
			data-api-base="<?=$ugc_api_base?>"
		></div>
		<script type="text/javascript" src="ugc.js"></script>

	</div>

</body>
</html>
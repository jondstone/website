<?php
/*
The 'gallery' query parameter (?gallery=galleryname) is expected in the URL 
(e.g., atmosphere, bestsellers, etc.). 

However, it may be missing if the visitor arrives via a search result 
or a shared social media link.
*/

// Vars
$imageName = 'ghosttrain';
$defaultGalleryName = 'lostplaces';

// Determine if the connection is secure and get current URL
$protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
$currentURL = $protocol . "://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

if (file_exists('galleryquery.php')) {
    include 'galleryquery.php';

    // read gallery name from the URL, and sanitize it
    $rawGalleryName = isset($_GET['gallery']) ? $_GET['gallery'] : '';
    $sanitizedGalleryName = SanitizeGallery($rawGalleryName, $defaultGalleryName);

    // Set the gallery name
    $galleryName = SetGalleryName($sanitizedGalleryName, $defaultGalleryName, $currentURL);

    // Final check to proceed only if the gallery name is valid
    if ($galleryName != '') {
        $queryResults = GalleryQuery($galleryName, $imageName, $currentURL);

        // Process the results if available
        if (!empty($queryResults)) {
            $queryResultsSplit = explode(',', $queryResults);
            $counter = $queryResultsSplit[0];
            $laPath = $queryResultsSplit[1];
            $raPath = $queryResultsSplit[2];
            $height = $queryResultsSplit[3];
        } else {
            $height = '1'; // Default height if no results are found
        }
    }

    // Sets Gallery Name and Gallery URL for Nav Links at top of page
    $galleryNavDetails = SetGalleryNavDetails($sanitizedGalleryName, $defaultGalleryName, $currentURL);

    if (!empty($galleryNavDetails) && isset($galleryNavDetails['name'], $galleryNavDetails['url'])) {
        $galleryNameForNav = $galleryNavDetails['name']; // Case-sensitive display name
        $galleryUrlLink = $galleryNavDetails['url'];    // Navigation URL
    } else {
        // Provide fallback values in case of null or empty $galleryDetails
        $galleryNameForNav = ucfirst($defaultGalleryName);
        $galleryUrlLink = "/galleries/" . strtolower($defaultGalleryName) . ".php";
    }
} else {
    // Use case where the gallery query data does not exist for some reason.
    $height = '1'; // Default height
}
?>
<!DOCTYPE html>
<html lang="en-US">
<head>
    <link rel="stylesheet" href="photogallery.css" type="text/css" media="all" />
    <style type="text/css">
        #galleryNavLeftArrow, #galleryNavRightArrow {
            height: <?=$height?>px;  
        }
    </style>
    <script type="application/ld+json">
        {
        "@context": "https://schema.org",
        "@type": "ImageObject",
        "name": "Ghost Train by Jon Stone",
        "contentUrl": "urltojpg",
        "creator": {
            "@type": "Person",
            "name": "Jon Stone"
        },
        "description": "An eerie, abandoned roller coaster stands still at Joyland Amusement Park in Wichita, Kansas, a silent reminder of the past.",
        "url": "https://www.jondstone.com/galleries/ghosttrain.php",
        "thumbnailUrl": "urltojpg"
        }
    </script>
    <!-- Header content removed as it's not necessary to be displayed in Git.-->
    <!--------- Page-Specific Tags ----------->
</head>

<body oncontextmenu="return false">
    <noscript>
        Javascript is currently disabled. This site requires Javascript to be enabled.
    </noscript>
    <script src="addtocart.js"></script>
    <script>
        var print_Name = 'Ghost Train';
    </script>

	<div id="individualGalleryPageContent">
		<br />
        <div id="galleryNavLinks">
            <?php
            if (!empty($galleryUrlLink) && !empty($galleryNameForNav)) {
                echo '<h1><a href="/galleries/">Galleries</a> <span>></span> <a href="'.$galleryUrlLink.'">'.$galleryNameForNav.'</a> <span>></span> '.$imageName.'</h1>';
            }
            ?>
        </div>
        
        <h2>Ghost Train</h2>
        
        <div id="galleryPhotoContainer">

            <div id="galleryNavLeftArrow">
                <?php 
                if (!empty($laPath))
                    echo '<a href="'.$laPath.'"><img src="leftArrow.png" alt="Left Arrow"/></a>'; 
                ?>
            </div>
            <div id="galleryImage">
                <img src="ghosttrain.jpg" height="533px" width="800px" alt="Ghost Train" />
            </div>
            <div id="galleryNavRightArrow">
                <?php 
                if (!empty($raPath))
                    echo '<a href="'.$raPath.'"><img src="rightArrow.png" alt="Right Arrow"/></a>'; 
                ?>
            </div>
        </div>
        <div id="displayCounter">
            <?php 
            if (!empty($counter))
                echo $counter;
            ?>
        </div>
		
		<div id="galleryPhotoInformation">
			<div id="galleryInfoLeftSide">
			</div>

			<div id="galleryInfoRightSide">	
				<h3 class="purchaseOptions">Purchase Options</h3>
                <div class="purchaseOptions">
                    <a href="/cart/index.php" target="_blank" id="cartID" title="cart">
                        <img src="./../images/shoppingCartIcon.png" alt="Shopping Cart Icon">
                        <span class="menuitem" id="cartIconText"></span>
                    </a>
                </div>
			</div>	
            
            <div id="galleryInfoMap" style=""></div> 
            <script>
                let map;

                async function initMap() {
                const position = {lat: 37.6407177, lng: -97.3046476};
                const { Map } = await google.maps.importLibrary("maps");
                const { AdvancedMarkerElement } = await google.maps.importLibrary("marker");

                map = new Map(document.getElementById("galleryInfoMap"), {
                    zoom: 13,
                    center: position,
                    mapId: "mapid",
                });

                const marker = new AdvancedMarkerElement({
                    map: map,
                    position: position,
                    title: "Ghost Train",
                });
                }

                initMap();
            </script>
        </div>	
	</div>

</body>
</html>
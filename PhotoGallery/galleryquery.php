<?php
/**
 * Sanitize the gallery parameter and set a default if blank.
 * 
 * @param string $inputGallery The raw gallery name from user input ($_GET['gallery']).
 * @param string $defaultGallery The default gallery name to use if $inputGallery is blank.
 * @return string The sanitized and defaulted gallery name.
 */
function SanitizeGallery($inputGallery, $defaultGallery)
{
    // Remove unwanted characters (allow alphanumeric, hyphens, and underscores only)
    $sanitizedGallery = preg_replace("/[^a-zA-Z0-9-_]/", "", $inputGallery);

    // Convert the gallery name to lowercase for consistency
    $sanitizedGallery = strtolower($sanitizedGallery);

    // Set default gallery if the input is empty
    $sanitizedGallery = $sanitizedGallery ?: $defaultGallery;

    // Sanitize for HTML output, if necessary
    return htmlspecialchars($sanitizedGallery, ENT_QUOTES, 'UTF-8');
}

/**
 * Sets the gallery name based on the input.
 *
 * This function checks if the provided gallery name is one of a predefined set. If it is, it returns
 * the gallery name. Otherwise, it returns a default gallery name specified by $useDefaultGalleryIfBlank.
 * In case of an error, an exception is caught, and an email is sent.
 *
 * @param string $gallery The gallery name to set.
 * @param string $useDefaultGalleryIfBlank The default gallery name to use if the provided gallery name is invalid.
 * @param string $url The URL to be included in the exception email.
 * @return string The determined gallery name.
 */
function SetGalleryName($gallery, $useDefaultGalleryIfBlank, $currentURL)
{
    try
    {
        // Current list of Valid Galleries on my site
        $validGalleries = [
            'myfavorites', 'bestsellers', 'atmosphere', 'cityscapes', 'lostplaces', 
            'nightscapes', 'wildlife', 'desertscapes', 'mountains', 'waterscapes', 'newreleases'
        ];

        // Check if gallery is in the list
        if (in_array($gallery, $validGalleries)) {
            return $gallery;
        } else {
            return $useDefaultGalleryIfBlank; // Return default gallery if $gallery (faulty URL get) isn't in list
        }
    }
    catch (Exception $e)
    {
        $errorMessage = $e->getMessage();
        SendExceptionEmail($errorMessage, $currentURL);
        return $useDefaultGalleryIfBlank;
    }
}

/**
 * Gallery Query
 *
 * This function performs a query on the gallery table to retrieve the position of an image 
 * based on the provided gallery name and image name. It also fetches the left and right 
 * arrow URLs, and the image height for the current image.
 *
 * @param string $galleryName The name of the gallery.
 * @param string $imageName The name of the image to query.
 * @param string $currentURL The URL to be used for error handling only.
 *
 * @return string A comma-separated string containing the display counter, left arrow URL, right arrow URL, and image height.
*/
function GalleryQuery($gallery, $imageName, $currentURL)
{
    try
    {
        $imageHeight = '';
        $displayCounter = '';
        $laURL = '';
        $raURL = '';
        
        //DB connection
        $serverName = 'localhost';
        $username = 'username';
        $password = 'password';
        $db = 'databasename';

        // Kill if error is returned, and we don't alert them
        $conn = new mysqli($serverName, $username, $password, $db);
        if ($conn->connect_error) {
            $errorMessage = "Database connection failed: " . $conn->connect_error;
            SendExceptionEmail($errorMessage, $currentURL);
            die;
        } else {
            // Find Number of Rows in Table
            $numRows = mysqli_num_rows(mysqli_query($conn, "SELECT url FROM {$gallery}"));

            // Grab Position of current in Table
            $tablePositionQuery = "SELECT position FROM {$gallery} WHERE imageName= '{$imageName}'";
            $position = null;

            if ($result = mysqli_query($conn, $tablePositionQuery)) {
                $row = mysqli_fetch_row($result); // Fetch the row
                if ($row) {
                    $position = $row[0]; // Access  first element of the row
                    $displayCounter = $position.'/'.$numRows; // Display Counter

                    $combinedQuery = "
                        SELECT
                            imageHeight,
                            (SELECT url FROM {$gallery} WHERE position = {$position} - 1) AS laURL,
                            (SELECT url FROM {$gallery} WHERE position = {$position} + 1) AS raURL
                        FROM
                            {$gallery}
                        WHERE
                            position = {$position}";
                    
                    // Fetch result as an associative array
                    if ($combinedResult = mysqli_query($conn, $combinedQuery)) {
                        $row = mysqli_fetch_assoc($combinedResult); 
                        $imageHeight = $row['imageHeight'];
                        // Left/Right Arrow URL is ignored if Null
                        $laURL = $row['laURL'] ? 'https://www.jondstone.com/' . $row['laURL'] . '?gallery=' . $gallery : null;
                        $raURL = $row['raURL'] ? 'https://www.jondstone.com/' . $row['raURL'] . '?gallery=' . $gallery : null;
                    } else {
                        $errorMessage = "Unable to get laURL, raURL, and ImageHeight in galleryquery.GalleryQuery"; 
                        SendExceptionEmail($errorMessage, $currentURL);
                        die;
                    }
                }
            } else {
                $errorMessage = "Unable to get results in galleryquery.GalleryQuery";
                SendExceptionEmail($errorMessage, $currentURL);
                die;
            }

            // Close connection
            $conn -> close();

            // Return result of queries
            $results = $displayCounter.','.$laURL.','.$raURL.','.$imageHeight;
            return $results;
        }
    }
    catch (Exception $e) {
        $errorMessage = $e->getMessage();
        SendExceptionEmail($errorMessage, $currentURL);
        die;
    }
}

/**
 * Sets the case-sensitive gallery name and navigation URL for a given gallery.
 *
 * This function maps a gallery name to its corresponding case-sensitive display name and navigation URL.
 * If the provided gallery name is blank, it falls back to a default gallery name.
 *
 * @param string $gallery The gallery name to process.
 * @param string $useDefaultGalleryIfBlank The fallback gallery name if the provided gallery name is blank.
 * @param string $url The URL to be included in the exception email for error handling.
 * @return array An associative array containing 'name' (case-sensitive display name) and 'url' (navigation URL).
 */
function SetGalleryNavDetails($gallery, $useDefaultGalleryIfBlank, $currentURL)
{
    try {
        // Array for gallery name to case-sensitive display name mapping
        $galleryDetails = [
            "myfavorites"  => ["name" => "My Favorites",  "url" => "/galleries/myfavorites.php"],
            "bestsellers"  => ["name" => "Best Sellers",  "url" => "/galleries/bestsellers.php"],
            "atmosphere"   => ["name" => "Atmosphere",    "url" => "/galleries/atmosphere.php"],
            "cityscapes"   => ["name" => "Cityscapes",    "url" => "/galleries/cityscapes.php"],
            "nightscapes"  => ["name" => "Nightscapes",   "url" => "/galleries/nightscapes.php"],
            "lostplaces"   => ["name" => "Lost Places",   "url" => "/galleries/lostplaces.php"],
            "wildlife"     => ["name" => "Wildlife",      "url" => "/galleries/wildlife.php"],
            "desertscapes" => ["name" => "Desertscapes",  "url" => "/galleries/desertscapes.php"],
            "mountains"    => ["name" => "Mountains",     "url" => "/galleries/mountains.php"],
            "waterscapes"  => ["name" => "Waterscapes",   "url" => "/galleries/waterscapes.php"],
            "newreleases"  => ["name" => "New Releases",   "url" => "/galleries/newreleases.php"]
        ];

        // If the gallery is blank, use the fallback gallery name
        if ($gallery == '') {
            $gallery = $useDefaultGalleryIfBlank;
        }

        // Set the Name and URL
        $details = $galleryDetails[$gallery] ?? [
            "name" => ucfirst($useDefaultGalleryIfBlank),
            "url"  => "/galleries/" . strtolower($useDefaultGalleryIfBlank) . ".php"
        ];
        
        return $details;
    } catch (Exception $e) {
        $errorMessage = $e->getMessage();
        SendExceptionEmail($errorMessage, $currentURL);
        die;
    }
}

/**
 * Sends an exception email notification.
 *
 * This function constructs an email message containing the provided error message
 * and the URL where the exception occurred. It then sends the email to a predefined
 * recipient with the details of the exception.
 *
 * @param string $errorMessage The error message describing the exception.
 * @param string $url The URL where the exception was triggered, used for context.
 *
 * @return void This function does not return any value.
 */
function SendExceptionEmail($errorMessage, $url)
{
    $emailBody = "The follow exception: $errorMessage was generated at $url.";
    $to = 'email@email.com';
    $subject = 'Gallery Exception Triggered';
    $headers = 'From: email@email.com'."\r\n".'X-Mailer: PHP/'.phpversion();
    mail ($to, $subject, $emailBody, $headers);
}
?>
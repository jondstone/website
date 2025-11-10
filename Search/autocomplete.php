<?php
// Include search.php to access the search() function
include('searchengine.php');

// Helper function to truncate a string to a maximum number of words
function truncateToWords($text, $maxWords = 5) {
    $words = preg_split('/\s+/', trim($text), -1, PREG_SPLIT_NO_EMPTY);
    $truncatedWords = array_slice($words, 0, $maxWords);
    return implode(' ', $truncatedWords);
}

$fileName = 'config/pageData.json';
$jsonData = readJsonFile($fileName);

$query = isset($_GET['query']) ? strtolower(trim($_GET['query'])) : '';

$suggestions = [];
if (!empty($query)) {
    $matchingTags = [];

    foreach ($jsonData as $item) {
        if (isset($item['tags'])) {
            foreach ($item['tags'] as $tag) {
                if (stripos($tag, $query) !== false) {
                    $matchingTags[] = $tag;
                }
            }
        }
    }

    // Remove duplicate full tags before truncation
    $uniqueFullTags = array_unique($matchingTags);

    // Truncate each unique full tag to 5 words
    foreach ($uniqueFullTags as $fullTag) {
        $suggestions[] = truncateToWords($fullTag, 5);
    }

    // Remove duplicate shortened suggestions
    $suggestions = array_unique($suggestions);

    // Sort suggestions: exact prefix matches first, then alphabetically
    usort($suggestions, function($a, $b) use ($query) {
        $aStartsWith = stripos($a, $query) === 0;
        $bStartsWith = stripos($b, $query) === 0;

        if ($aStartsWith && !$bStartsWith) {
            return -1;
        } elseif (!$aStartsWith && $bStartsWith) {
            return 1;
        } else {
            return strcasecmp($a, $b);
        }
    });
}

header('Content-Type: application/json');
echo json_encode(array_values($suggestions));
?>
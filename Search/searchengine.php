<?php

// Load search configuration from JSON file
$searchConfig = json_decode(file_get_contents('config/searchConfig.json'), true);
$metaDescStopwords = $searchConfig['stopwords'];
$abbreviationMap = $searchConfig['abbreviations'];

// Function to read and parse a JSON file into an associative array
function readJsonFile($filename) {
    $jsonData = file_get_contents($filename);
    return json_decode($jsonData, true);
}

/**
 * Normalizes a query string by removing extra whitespace, punctuation, and converting to lowercase
 *
 * @param string $query The raw query string
 * @return string The normalized query
 */
function normalizeQuery($query) {
    $query = strtolower($query);
    $query = preg_replace('/[^a-z0-9\s]/i', ' ', $query);
    $query = preg_replace('/\s+/', ' ', $query);
    $query = trim($query);

    return $query;
}

/**
 * Expands abbreviations in a given text using the above abbreviation map
 * Uses word boundaries to ensure "la" doesn't match inside "alabama"
 *
 * @param string $text The text to process
 * @param array $abbreviationMap The map of abbreviations
 * @return string The text with expanded abbreviations
 */
function expandAbbreviations($text, $abbreviationMap) {
    $lowerText = strtolower($text);
    $map = array_change_key_case($abbreviationMap, CASE_LOWER);

    // Check if the entire input text is an abbreviation key
    if (isset($map[$lowerText])) {
        $expansionTerms = preg_split('/\s+/', $map[$lowerText], -1, PREG_SPLIT_NO_EMPTY);
        return implode(' ', array_unique($expansionTerms));
    }

    // Fall back to word-by-word processing if no full match
    $originalWords = array_unique(
        array_filter(
            preg_split('/\s+/', $lowerText, -1, PREG_SPLIT_NO_EMPTY)
        )
    );

    $expandedWords = [];

    // Process each unique original word
    foreach ($originalWords as $word) {
        if (isset($map[$word])) {
            // Found a match, add its expansion terms
            $expansionTerms = preg_split('/\s+/', $map[$word], -1, PREG_SPLIT_NO_EMPTY);
            $expandedWords = array_merge($expandedWords, $expansionTerms);
        } else {
            // Not an abbreviation, keep the original word
            $expandedWords[] = $word;
        }
    }

    // Remove duplicates and join the final expanded words
    return implode(' ', array_unique($expandedWords));
}

/**
 * Determines if two words are similar based on exact match, prefix match, Levenshtein distance, or stemming.
 *
 * @param string $queryWord The search word
 * @param string $targetWord The word to compare against
 * @return bool Whether the words are considered similar
 */
function isSimilarWord($queryWord, $targetWord) {
    // Check for exact match
    if ($queryWord === $targetWord) {
        return true;
    }

    // Levenshtein distance for typos, only for words 7+ characters
    if (strlen($queryWord) >= 7 && strlen($targetWord) >= 7) {
        $distance = levenshtein($queryWord, $targetWord);
        if ($distance <= 1) {
            return true;
        }
    }

    // Check for root word similarity using stemming
    $queryStem = stemWord($queryWord);
    $targetStem = stemWord($targetWord);
    return $queryStem === $targetStem;
}

/**
 * Stems a word to its root form (using the Porter Stemmer algorithm)
 *
 * @param string $word The word to stem
 * @return string The stemmed word
 */
function stemWord($word) {
    return porterStem($word);
}

/**
 * Applies the Porter Stemmer algorithm to a word
 *
 * @param string $word The word to process
 * @return string The stemmed word
 */
function porterStem($word) {
    $word = strtolower($word);

    $suffixes = [
        '/(sses)$/i' => 'ss',
        '/(ied|ies)$/i' => 'i',
        '/(es)$/i' => 'e',
        '/(ed|ing)$/i' => '',
        '/(es|s)$/i' => 's',
    ];

    foreach ($suffixes as $pattern => $replacement) {
        if (preg_match($pattern, $word)) {
            $word = preg_replace($pattern, $replacement, $word);
            break;
        }
    }

    return $word;
}

/**
 * Calculates the relevance score of a page based on its meta description and tags compared to the search query
 *
 * @param string $querySearchString The search query string
 * @param array $page The page data to analyze
 * @return array Array containing 'score' and 'debug' breakdown
 */
function calculateRelevance($expandedQuery, $page) {
    global $metaDescStopwords;

    $metaDesc = strtolower($page["meta_desc"]);
    $title = strtolower($page["title"]);
    $tags = array_map('strtolower', $page["tags"]);

    $queryWords = explode(" ", $expandedQuery);

    // Filter out stopwords from meta description
    $metaDescWords = array_filter(explode(" ", $metaDesc), function($word) use ($metaDescStopwords) {
        return !in_array($word, $metaDescStopwords);
    });
    $filteredMetaDesc = implode(" ", $metaDescWords);

    $titleWords = explode(" ", $title);

    $titleScore = 0;
    $metaScore = 0;
    $tagScore = 0;
    $matchedWords = 0;

    // Exact phrase match in title gets highest priority
    if (strpos($title, $expandedQuery) !== false) {
        $titleScore += 100;
        $debug['matches'][] = [
            'type' => 'title_exact_phrase',
            'matched' => $expandedQuery,
            'points' => 100
        ];
    } else {
        // Check if any query words are contained in the title
        $titleWordsMatched = 0;
        foreach ($queryWords as $queryWord) {
            if (strpos($title, $queryWord) !== false) {
                $titleScore += 20;
                $titleWordsMatched++;
                $debug['matches'][] = [
                    'type' => 'title_contains_word',
                    'query_word' => $queryWord,
                    'points' => 20
                ];
            }
        }
    }

    // Exact phrase match in meta description
    if (strpos($filteredMetaDesc, $expandedQuery) !== false) {
        $metaScore += 50;
        $debug['matches'][] = [
            'type' => 'meta_exact_phrase',
            'matched' => $expandedQuery,
            'points' => 50
        ];
    }

    // Exact tag match
    $exactTagMatches = [];
    foreach ($tags as $tag) {
        if ($tag === $expandedQuery) {
            $tagScore += 30;
            $exactTagMatches[] = $tag;
            $debug['matches'][] = [
                'type' => 'tag_exact_match',
                'matched' => $expandedQuery,
                'in_tag' => $tag,
                'points' => 30
            ];
        }
    }

    // Individual word matches using expanded query words
    foreach ($queryWords as $queryWord) {
        $wordMatched = false;

        // Check for matches in title
        foreach ($titleWords as $titleWord) {
            if (isSimilarWord($queryWord, $titleWord)) {
                $titleScore += 10;
                $wordMatched = true;
                $debug['matches'][] = [
                    'type' => 'title_word',
                    'query_word' => $queryWord,
                    'matched_word' => $titleWord,
                    'points' => 10
                ];
            }
        }

        // Check for matches in meta description
        foreach ($metaDescWords as $metaWord) {
            if (isSimilarWord($queryWord, $metaWord)) {
                $metaScore += 5;
                $wordMatched = true;
                $debug['matches'][] = [
                    'type' => 'meta_word',
                    'query_word' => $queryWord,
                    'matched_word' => $metaWord,
                    'points' => 5
                ];
            }
        }

        // Check for matches in tags
        foreach ($tags as $tag) {
            $tagMatchedInThisTag = false;
            foreach (explode(" ", $tag) as $tagWord) {
                if (!$tagMatchedInThisTag && isSimilarWord($queryWord, $tagWord)) {
                    $tagScore += 10;
                    $wordMatched = true;
                    $debug['matches'][] = [
                        'type' => 'tag_word',
                        'query_word' => $queryWord,
                        'matched_word' => $tagWord,
                        'in_tag' => $tag,
                        'points' => 10
                    ];
                    $tagMatchedInThisTag = true;
                    break;
                }
            }
        }

        if ($wordMatched) {
            $matchedWords++;
        }
    }

    // Calculate final score with exponential multiplier based on matched words
    $baseScore = $titleScore + $metaScore + $tagScore;
    if ($matchedWords > 0) {
        $finalScore = $baseScore * (2 ** ($matchedWords - 1));
    } else {
        $finalScore = 0;
    }

    $debug['score_breakdown'] = [
        'title_score' => $titleScore,
        'meta_score' => $metaScore,
        'tag_score' => $tagScore,
        'base_score' => $baseScore,
        'matched_words_count' => $matchedWords,
        'multiplier' => $matchedWords > 0 ? (2 ** ($matchedWords - 1)) : 0,
        'final_score' => $finalScore
    ];

    return [
        'score' => $finalScore,
        'debug' => $debug
    ];
}

/**
 * Searches the page data for the query and ranks results based on relevance
 *
 * @param string $query The search query
 * @return array The ranked search results
 */
function search($query) {
    global $abbreviationMap;
    $data = readJsonFile('config/pageData.json');

    $normalizedQuery = normalizeQuery($query);
    $expandedQuery = expandAbbreviations($normalizedQuery, $abbreviationMap);

    $results = [];
    foreach ($data as $page) {
        $relevanceData = calculateRelevance($expandedQuery, $page);
        $relevance = $relevanceData['score'];

        if ($relevance >= 11) {
            $results[] = [
                'url' => $page['url'],
                'relevance' => $relevance,
                'meta_desc' => $page['meta_desc'],
                'title' => $page['title'],
                'og_image' => $page['og_image'],
                'debug' => $relevanceData['debug']
            ];
        }
    }

    // Sort results: gallery/urbex pages first, then by relevance score
    usort($results, function ($a, $b) {
        $aIsGallery = (strpos($a['url'], '/galleries/') !== false || strpos($a['url'], '/urbanexplorations/') !== false);
        $bIsGallery = (strpos($b['url'], '/galleries/') !== false || strpos($b['url'], '/urbanexplorations/') !== false);

        if ($aIsGallery && !$bIsGallery) {
            return -1;
        }
        if (!$aIsGallery && $bIsGallery) {
            return 1;
        }

        return $b['relevance'] <=> $a['relevance'];
    });

    // Return top 10 results and log the search
    $results = array_slice($results, 0, 10);
    logSearchResults($query, $results);

    return $results;
}

/**
 * Writes pertinent data to a file on the server. Allows me to see how often it's being used, what is being searched for
 * and the results returned. This can allow me to refine the search in the future.
 *
 * @param string $query The search query
 * @param array $results The results for the query
 */
function logSearchResults($query, $results) {
    $currentDateTime = date('dmy_H:i:s');

    $urls = array_map(function ($result) {
        return $result['url'];
    }, $results);
    $urlsCSV = implode(", ", $urls);

    $file = fopen('searchresults.txt', 'a');
    if ($file) {
        fwrite($file, "$currentDateTime, $query, ($urlsCSV)\n");
        fclose($file);
    }
}
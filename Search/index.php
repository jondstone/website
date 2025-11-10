<?php
    include('searchengine.php');

    // Get query from URL or POST request
    $query = isset($_GET['query']) ? $_GET['query'] : '';  // Use GET or POST based on your form setup

    $shortQueryMessage = '';
    $results = [];

    // Check if the query is valid for a search
    if (!empty($query) && strlen($query) > 2) {
        $results = search($query);
    } else if (!empty($query)) {
        $shortQueryMessage = "Please enter more than 2 characters to refine your search.";
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Stylesheets and Scripts-->
    <link rel="stylesheet" href="./../css/search.css" type="text/css" media="all" />
    <!-- Header content removed as it's not necessary to be displayed in Git.-->
</head>
<body>
    <div id="content">
        <h1>Search</h1>
        <hr>
        <form method="get" action="" class="searchForm">
            <div class="searchContent">
                <input type="text" name="query" autocomplete="off" value="<?php echo htmlspecialchars($query); ?>" placeholder="Search...">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 25 25" class="searchIcon">
                    <circle cx="11" cy="11" r="6" stroke="currentColor" stroke-width="2"></circle>
                    <line x1="15" y1="15" x2="21" y2="21" stroke="currentColor" stroke-width="2"></line>
                </svg>
                <div class="suggestionBox"></div>
            </div>
        </form>
        <div class="searchInfo">Search for pages and photos by keywords or phrases.</div>
        <hr>

        <?php if ($query): ?>
            <h2 class="resultsHeader">
                <span>Top results for "<?php echo htmlspecialchars($query); ?>"</span>
                <span class="resultsCount">
                    <?php
                        echo count($results) . " results";
                    ?>
                </span>
            </h2>
            <?php if (!empty($results)): ?>
                <?php foreach ($results as $result): ?>
                    <div class="searchResult">
                        <div class="searchResultImage">
                            <a href="<?php echo htmlspecialchars($result['url']); ?>" target="_blank">
                                <img src="<?php echo htmlspecialchars($result['og_image']); ?>" alt="Image"/>
                            </a>
                        </div>
                        <div class="searchResultContent">
                            <a href="<?php echo htmlspecialchars($result['url']); ?>" target="_blank" class="searchResultTitle"><?php echo htmlspecialchars($result['title']); ?></a>
                            <p class="searchResultDesc"><?php echo htmlspecialchars($result['meta_desc']); ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php elseif (empty($results) && !empty($shortQueryMessage)): ?>
                <p><?php echo htmlspecialchars($shortQueryMessage); ?></p>
            <?php else: ?>
                <p>No results found, please refine your search.</p>
            <?php endif; ?>
        <?php endif; ?>

    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            document.querySelector('.searchIcon').addEventListener('click', function () {
                this.closest('form').submit();
            });

            const searchInput = document.querySelector('input[name="query"]');
            const suggestionBox = document.querySelector('.suggestionBox');
            const searchForm = document.querySelector('form');

            let suggestions = [];
            let currentIndex = -1;

            searchInput.addEventListener('input', function () {
                const query = searchInput.value;

                if (query.length > 2) {
                    fetch(`autocomplete.php?query=${encodeURIComponent(query)}`)
                        .then(response => response.json())
                        .then(data => {
                            suggestions = data;
                            suggestionBox.innerHTML = '';
                            suggestionBox.style.display = suggestions.length > 0 ? 'block' : 'none';

                            const limitedSuggestions = suggestions.slice(0, 6);

                            limitedSuggestions.forEach((suggestion, index) => {
                                const suggestionItem = document.createElement('div');
                                suggestionItem.textContent = suggestion;

                                suggestionItem.addEventListener('click', function () {
                                    searchInput.value = suggestion;
                                    suggestionBox.innerHTML = '';
                                    suggestionBox.style.display = 'none';

                                    // Automatically submit the form on selection
                                    searchForm.submit();
                                });

                                suggestionBox.appendChild(suggestionItem);
                            });
                        })
                        .catch(error => console.error("Error fetching suggestions:", error));
                } else {
                    suggestionBox.innerHTML = '';
                    suggestionBox.style.display = 'none';
                }
            });

            document.addEventListener('click', function (event) {
                if (!suggestionBox.contains(event.target) && event.target !== searchInput) {
                    suggestionBox.innerHTML = '';
                    suggestionBox.style.display = 'none';
                }
            });

            searchInput.addEventListener('keydown', function (event) {
                const suggestionItems = suggestionBox.children;

                if (event.key === "ArrowDown") {
                    event.preventDefault();
                    if (currentIndex < suggestionItems.length - 1) {
                        currentIndex++;
                        updateActiveSuggestion();
                    }
                } else if (event.key === "ArrowUp") {
                    event.preventDefault();
                    if (currentIndex > 0) {
                        currentIndex--;
                        updateActiveSuggestion();
                    }
                } else if (event.key === "Enter") {
                    if (currentIndex >= 0 && suggestionItems[currentIndex]) {
                        event.preventDefault();
                        selectSuggestion(suggestionItems[currentIndex].textContent);
                    } else if (searchInput.value.length > 2) {
                        // Submit the form if Enter is pressed and no suggestion is active
                        searchForm.submit();
                    }
                }
            });

            function updateActiveSuggestion() {
                const suggestionItems = Array.from(suggestionBox.children);
                suggestionItems.forEach((item, index) => {
                    item.style.backgroundColor = index === currentIndex ? '#d3d3d3' : '';
                });
            }

            function selectSuggestion(suggestion) {
                searchInput.value = suggestion;
                suggestionBox.innerHTML = '';
                suggestionBox.style.display = 'none';

                // Automatically submit the form on selection
                searchForm.submit();
            }
        });
    </script>

</body>
</html>
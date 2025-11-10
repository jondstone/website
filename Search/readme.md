# Search Engine Example
This repository contains the code for the custom-built search engine used on my website. The process begins with a Python-based crawler that scans each page listed in the sitemap.xml file, extracts readable text, and generates meaningful tags that is then stored in a JSON file.

The search engine algorithm then searches through this JSON file for user queries, ranking results by the query's relevance to tags, titles, and meta descriptions. Each match receives a weighted score, and results are displayed based on their total ranking and page type.

### This folder includes the active working files for the Gallery Photo pages on my website. [www.jondstone.com/search](https://www.jondstone.com/search)
* autocomplete.php (PHP script that provides search suggestion autocomplete as users begin typing a query)
* index.php (The front-facing page; includes JS to navigate the suggestion dropdown and submit the query)
* searchengine.php (Core PHP logic for the search engine’s ranking and sorting algorithm)
* search.css (CSS stylesheet for the index.php page)
* crawler.py (Python script that crawls all sitemap.xml entries, extracts content, and generates relevant tags)
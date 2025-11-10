import requests
from bs4 import BeautifulSoup, NavigableString
import spacy
import re
import json
import xml.etree.ElementTree as ET

# Config
SITEMAP_URL = 'https://www.jondstone.com/sitemap.xml'
DIV_IDS = ['content', 'urbexPageContent', 'contentUrbex', 'individualGalleryPageContent']
IGNORE_IDS = ['galleryInfoRightSide', 'galleryInfoMap', 'displayCounter', 'urbexGallery-container', 'disqus_thread', 'footer', 'cookiesBanner']
IGNORE_CLASSES = ['table_format', 'glry_2items']
IGNORE_PAGES = ['https://www.jondstone.com']
OUTPUT_FILE = 'newPageData.json'

# Stopwords to filter from tags
CUSTOM_STOPWORDS = {
    "the", "a", "my", "about", "info", "print", "i", "few", "to", "for", "at",
    "am", "can", "down", "from", "as", "be", "are", "too", "through", "does",
    "but", "now", "some", "an", "we", "below", "against", "here", "did", "how",
    "yourselves", "was", "above", "him", "it", "which", "himself", "its", "most",
    "re", "or", "while", "your", "if", "yours", "she", "her", "other", "any",
    "off", "is", "of", "there", "than", "why", "has", "so", "in", "only",
    "have", "itself", "under", "own", "were", "those", "out", "very", "until",
    "hers", "after", "up", "they", "their", "not", "doing", "no", "them",
    "where", "ourselves", "themselves", "our", "on", "that", "nor", "ours",
    "again", "same", "over", "just", "because", "who", "before", "by", "more",
    "being", "had", "this", "with", "should", "what", "during", "herself", "and",
    "these", "such", "further", "do", "yourself", "his", "into", "once", "each",
    "all", "then", "both", "when", "he", "me", "whom", "myself", "been", "will",
    "between", "i"
}

# Web-related technical terms to filter out
WEB_TECHNICAL_TERMS = {
    "mouseover", "onclick", "onload", "href", "src", "div", "span", "class",
    "style", "script", "function", "var", "const", "let", "document", "window",
    "http", "https", "www", "com", "org", "net", "html", "css", "javascript",
    "js", "php", "asp", "jsp", "url", "link", "audio","tbody", "thead", "table",
    "td", "tr", "th", "px", "em", "rem", "vh", "vw", "larger", "pag", "n", "p",
    "d", "lt", "gt"
}

nlp = spacy.load("en_core_web_sm")

def get_page_content(url, div_ids, ignore_ids, ignore_classes):
    """
    Fetches and extracts content from specified divs on a webpage.
    Removes script tags, ignored divs/classes, and references sections.
    """
    response = requests.get(url)
    soup = BeautifulSoup(response.text, 'html.parser')

    title = soup.find('title')
    meta_desc = soup.find('meta', {'name': 'description'})
    og_image = soup.find('meta', {'property': 'og:image'})

    page_url = url

    div_contents = {}
    for div_id in div_ids:
        div_content = soup.find('div', id=div_id)
        if div_content:
            # Remove ignored div IDs
            for nested_id in ignore_ids:
                nested_element = div_content.find('div', id=nested_id)
                if nested_element:
                    nested_element.decompose()

            # Remove ignored div classes
            for nested_class in ignore_classes:
                nested_elements = div_content.find_all('div', class_=nested_class)
                for nested_element in nested_elements:
                    nested_element.decompose()

            # Remove all script tags
            for script in div_content.find_all('script'):
                script.decompose()

            # Remove references section and everything after it
            references_h2 = div_content.find('h2', string=re.compile(r'^\s*references\s*$', re.IGNORECASE))
            if references_h2:
                current = references_h2
                while current:
                    next_sibling = current.next_sibling
                    current.decompose()
                    current = next_sibling

            # Extract text from all remaining elements
            text_parts = []
            for element in div_content.descendants:
                if isinstance(element, NavigableString):
                    text = str(element).strip()
                    if text:
                        text_parts.append(text)

            div_contents[div_id] = ' '.join(text_parts)
        else:
            div_contents[div_id] = ""

    return {
        "url": page_url,
        "title": title.text if title else '',
        "meta_desc": meta_desc['content'] if meta_desc else '',
        "og_image": og_image['content'] if og_image else '',
        "div_contents": div_contents
    }

def clean_text(text):
    """
    Cleans text by removing URLs, special characters, fixing spacing issues,
    and filtering out web-related technical terms.
    """
    # Remove URLs
    text = re.sub(r'\b(?:https?://)?(?:www\.)?[\w\-\.]+\.(?:com|org|net|edu|gov|io|co|uk|de|fr|es|it|ca)\S*', ' ', text)

    # Remove special characters except apostrophes
    text = re.sub(r'[^\w\s\']', ' ', text)

    # Fix date spacing (20 th -> 20th)
    text = re.sub(r'\b(\d+)\s+(st|nd|rd|th)\b', r'\1\2', text, flags=re.IGNORECASE)

    # Fix possessive spacing (John 's -> John's)
    text = re.sub(r"(\w+)\s*'\s*s\b", r"\1's", text)

    # Add space between camelCase words
    text = re.sub(r'([a-z])([A-Z])', r'\1 \2', text)
    text = re.sub(r'\b([a-z]+)([A-Z][a-z]+)', r'\1 \2', text)

    # Collapse multiple spaces
    text = re.sub(r'\s+', ' ', text)

    # Filter out web technical terms
    words = text.split()
    filtered_words = []
    for word in words:
        word_lower = word.lower()
        if word_lower not in WEB_TECHNICAL_TERMS and len(word) > 1:
            filtered_words.append(word)

    text = ' '.join(filtered_words)
    text = ' '.join(text.split())

    return text.strip()

def generate_tags(text, custom_stopwords=None):
    """
    Generates tags from text using spaCy NLP to extract entities and noun phrases.
    Removes stopwords and filters tags by minimum length.
    """
    doc = nlp(text)
    entities = set(ent.text.lower() for ent in doc.ents)
    noun_phrases = set(chunk.text.lower() for chunk in doc.noun_chunks)
    tags = entities.union(noun_phrases)

    # Combine spaCy's default stopwords with custom ones
    stop_words = set(nlp.Defaults.stop_words)
    if custom_stopwords:
        stop_words.update(custom_stopwords)

    # Create regex to match stopwords
    stop_words_regex = r'\b(' + r'|'.join(re.escape(word) for word in stop_words) + r')\b'

    # Remove stopwords from tags and normalize spacing
    tags = [
        ' '.join(re.sub(stop_words_regex, ' ', tag).split())
        for tag in tags
        if len(tag) > 2
    ]

    # Filter out tags that are too short after stopword removal
    tags = [tag for tag in tags if len(tag) > 2]

    return tags

def get_urls_from_sitemap(sitemap_url):
    """
    Parses a sitemap.xml file and extracts all URLs.
    """
    response = requests.get(sitemap_url)
    sitemap = ET.fromstring(response.content)
    xmlns = {"ns": "http://www.sitemaps.org/schemas/sitemap/0.9"}
    urls = [url.text for url in sitemap.findall(".//ns:url/ns:loc", namespaces=xmlns)]
    return urls

def crawl_and_generate_tags():
    """
    Main function that orchestrates the crawling process.
    Fetches URLs from sitemap, extracts content, generates tags, and saves to JSON.
    """
    print(f"Fetching URLs from sitemap: {SITEMAP_URL}")
    urls = get_urls_from_sitemap(SITEMAP_URL)
    print(f"Found {len(urls)} URLs in sitemap")

    all_page_data = []

    for index, url in enumerate(urls, 1):
        if url in IGNORE_PAGES:
            print(f"[{index}/{len(urls)}] Skipping ignored page: {url}")
            continue

        print(f"[{index}/{len(urls)}] Processing: {url}")

        try:
            page_data = get_page_content(url, DIV_IDS, IGNORE_IDS, IGNORE_CLASSES)
            cleaned_contents = {div_id: clean_text(content) for div_id, content in page_data['div_contents'].items()}
            non_empty_content = next((content for content in cleaned_contents.values() if content), "")
            tags = generate_tags(non_empty_content, CUSTOM_STOPWORDS)

            page_output = {
                "url": page_data['url'],
                "title": page_data['title'],
                "meta_desc": page_data['meta_desc'],
                "og_image": page_data['og_image'],
                "tags": tags
            }
            all_page_data.append(page_output)
        except Exception as e:
            print(f"  ERROR: Failed to process {url}: {str(e)}")
            continue

    print(f"\nWriting {len(all_page_data)} pages to {OUTPUT_FILE}")
    with open(OUTPUT_FILE, 'w', encoding='utf-8') as f:
        json.dump(all_page_data, f, ensure_ascii=False, indent=4)

    print(f"Crawling completed successfully!")

if __name__ == '__main__':
    crawl_and_generate_tags()
document.addEventListener("DOMContentLoaded", () => {
    const thumbnails = document.querySelectorAll('.thumbnail-strip img');
    const gallery = document.querySelector(".urbexGallery");
    const galleryImage = document.getElementById('urbexGallery-image');
    const description = document.querySelector('.description');
    const leftArrow = document.querySelector('.arrow-container.left-arrow-container');
    const rightArrow = document.querySelector('.arrow-container.right-arrow-container');
    const leftThumbnailArrow = document.querySelector('.arrow-thumbnail.left');
    const rightThumbnailArrow = document.querySelector('.arrow-thumbnail.right');
    const thumbnailStrip = document.querySelector('.thumbnail-strip');

    let currentIndex = 0;
    let thumbnailStartIndex = 0;

    // Dynamically populate images array
    const images = Array.from(thumbnails).map(img => ({
        src: img.getAttribute('src'),
        desc: img.getAttribute('alt')
    }));

    // Update gallery content
    function updateGallery(index) {
        galleryImage.src = images[index].src;
        description.textContent = images[index].desc;
        window.getSelection().removeAllRanges();
        thumbnails.forEach((thumbnail, i) => {
            thumbnail.classList.toggle('active', i === index);
        });
        scrollToThumbnail(index);
    
        // Wait for the image to load before checking its dimensions
        galleryImage.onload = () => {
            if (galleryImage.naturalHeight > galleryImage.naturalWidth) {
                // Add the portrait class if the image is portrait
                gallery.classList.add("portrait");
            } else {
                // Remove the portrait class if the image is not portrait
                gallery.classList.remove("portrait");
            }
        };
    }    

    // Scroll to a specific thumbnail
    function scrollToThumbnail(index) {
        const visibleCount = calculateVisibleThumbnails();

        if (index < thumbnailStartIndex) {
            thumbnailStartIndex = index;
        } else if (index >= thumbnailStartIndex + visibleCount) {
            thumbnailStartIndex = index - (visibleCount - 1);
        }
        updateThumbnails();
    }

    // Update thumbnail strip based on the start index
    function updateThumbnails() {
        const visibleCount = calculateVisibleThumbnails();
        const maxStartIndex = Math.max(thumbnails.length - visibleCount, 0);
        thumbnailStartIndex = Math.min(thumbnailStartIndex, maxStartIndex);

        thumbnails.forEach((thumbnail, index) => {
            if (index >= thumbnailStartIndex && index < thumbnailStartIndex + visibleCount) {
                thumbnail.style.display = 'block';
            } else {
                thumbnail.style.display = 'none';
            }
        });

        updateArrowState();
    }

    // Update arrow state (enabled or disabled)
    function updateArrowState() {
        const visibleCount = calculateVisibleThumbnails();
        leftThumbnailArrow.classList.toggle('disabled', thumbnailStartIndex <= 0);
        rightThumbnailArrow.classList.toggle('disabled', thumbnailStartIndex + visibleCount >= thumbnails.length);
    }

    // Calculate how many thumbnails can be visible based on container width
    function calculateVisibleThumbnails() {
        const containerWidth = thumbnailStrip.offsetWidth;
        const thumbnailWidth = 67; // Width of a thumbnail (with margin)
        return Math.floor(containerWidth / thumbnailWidth);
    }

    // Thumbnail click event to show selected image
    thumbnails.forEach((thumbnail, index) => {
        thumbnail.addEventListener('click', () => {
            currentIndex = index;
            updateGallery(currentIndex);
        });
    });

    // Left and right arrows for main gallery
    leftArrow.addEventListener('click', () => {
        currentIndex = (currentIndex - 1 + images.length) % images.length;
        updateGallery(currentIndex);
    });

    rightArrow.addEventListener('click', () => {
        currentIndex = (currentIndex + 1) % images.length;
        updateGallery(currentIndex);
    });

    // Left and right arrows for Thumbnail strip
    leftThumbnailArrow.addEventListener('click', () => {
        const visibleCount = calculateVisibleThumbnails();
        if (thumbnailStartIndex > 0) {
            // Move left by 'visibleCount' thumbnails, with a delay
            setTimeout(() => {
                thumbnailStartIndex = Math.max(thumbnailStartIndex - visibleCount, 0);
                updateThumbnails();
            }, 200); // 200ms delay 
        }
    });

    rightThumbnailArrow.addEventListener('click', () => {
        const visibleCount = calculateVisibleThumbnails();
        if (thumbnailStartIndex + visibleCount < thumbnails.length) {
            // Move right by 'visibleCount' thumbnails, with a delay
            setTimeout(() => {
                thumbnailStartIndex = Math.min(thumbnailStartIndex + visibleCount, thumbnails.length - visibleCount);
                updateThumbnails();
            }, 200); // 200ms delay 
        }
    });

    // Initialize the gallery and thumbnails
    updateGallery(0);
    updateThumbnails();
});
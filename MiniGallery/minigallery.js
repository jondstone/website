const galleryImages = document.querySelectorAll('.miniGallery img');
const modal = document.querySelector('.modal');
const modalImage = modal.querySelector('.modalContent img');
const modalDescription = modal.querySelector('.description');
const leftControl = modal.querySelector('.control.leftArrow');
const rightControl = modal.querySelector('.control.rightArrow');
const closeControl = modal.querySelector('.close');

let currentImageIndex;

// Open Modal when clicking a gallery image
galleryImages.forEach((img, index) => {
  img.addEventListener('click', () => {
    currentImageIndex = index;
    openModal(img);
  });
});

// Open the modal and display the clicked image
function openModal(image) {
  modalImage.src = image.src;
  modalDescription.textContent = image.dataset.description;
  modal.classList.add('active');
  updateControls();
}

// Close the modal
function closeModal() {
  modal.classList.remove('active');
}

// Ensure the close button works correctly
closeControl.addEventListener('click', (e) => {
  e.stopPropagation(); // Prevent the click from bubbling to the modal content
  closeModal();
});

// Close the modal if clicked outside the modal content
modal.addEventListener('click', (e) => {
  if (e.target === modal) closeModal();
});

// Navigate through images (next/previous)
function navigate(direction) {
  const newIndex = currentImageIndex + direction;
  if (newIndex >= 0 && newIndex < galleryImages.length) {
    currentImageIndex = newIndex;
    const newImage = galleryImages[currentImageIndex];
    openModal(newImage);
  }
}

// Update control buttons based on the current image index
function updateControls() {
  leftControl.style.display = currentImageIndex > 0 ? 'block' : 'none';
  rightControl.style.display = currentImageIndex < galleryImages.length - 1 ? 'block' : 'none';
}

// Mouse hover behavior for showing controls
const modalContent = modal.querySelector('.modalContent');
modalContent.addEventListener('mousemove', (e) => {
  const { offsetWidth, offsetLeft } = modalContent;
  const relativeX = e.clientX - offsetLeft;

  const leftEdge = offsetWidth * 0.3;  // 30% from the left
  const rightEdge = offsetWidth * 0.7; // 30% from the right

  const isLeftSide = relativeX < leftEdge && currentImageIndex > 0; // Valid only if not on the first image
  const isRightSide = relativeX > rightEdge && currentImageIndex < galleryImages.length - 1; // Valid only if not on the last image

  leftControl.style.display = isLeftSide ? 'block' : 'none';
  rightControl.style.display = isRightSide ? 'block' : 'none';
  modalContent.style.cursor = isLeftSide || isRightSide ? 'pointer' : 'default';
});

// Hide controls when the mouse leaves the modal
modalContent.addEventListener('mouseleave', () => {
  leftControl.style.display = 'none';
  rightControl.style.display = 'none';
  modalContent.style.cursor = 'default'; // Reset cursor to default
});

// Click handler to navigate when clicking on the left or right side
modalContent.addEventListener('click', (e) => {
  // Prevent clicks on the close button from triggering navigation
  if (e.target === closeControl) return;

  const { offsetWidth, offsetLeft } = modalContent;
  const relativeX = e.clientX - offsetLeft;

  const leftEdge = offsetWidth * 0.3;  // 30% from the left
  const rightEdge = offsetWidth * 0.7; // 70% from the right

  if (relativeX < leftEdge && currentImageIndex > 0) {
    navigate(-1); // Navigate left
  } else if (relativeX > rightEdge && currentImageIndex < galleryImages.length - 1) {
    navigate(1); // Navigate right
  }
});
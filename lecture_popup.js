/**
 * ClassRoster - Lecture Popup Interaction Script
 * Handles the interactive behavior of lecture item popups
 */

document.addEventListener('DOMContentLoaded', function() {
    // Get all lecture items
    const lectureItems = document.querySelectorAll('.lecture-item');
    
    // For each lecture item
    lectureItems.forEach(item => {
        // Add click event to toggle the popup
        item.addEventListener('click', function(e) {
            // Close any other open popups first
            document.querySelectorAll('.lecture-item .lecture-details.active').forEach(popup => {
                if (popup !== this.querySelector('.lecture-details')) {
                    popup.classList.remove('active');
                }
            });
            
            // Toggle the active class on the current popup
            const popup = this.querySelector('.lecture-details');
            popup.classList.toggle('active');
            
            // Prevent the popup from closing when clicking inside it
            const popupContent = item.querySelector('.lecture-details');
            popupContent.addEventListener('click', function(e) {
                e.stopPropagation();
            });
            
            e.stopPropagation();
        });
    });
    
    // Close popup when clicking outside
    document.addEventListener('click', function() {
        document.querySelectorAll('.lecture-details.active').forEach(popup => {
            popup.classList.remove('active');
        });
    });

    // Optional: Close popups when pressing Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            document.querySelectorAll('.lecture-details.active').forEach(popup => {
                popup.classList.remove('active');
            });
        }
    });
    
    // Optional: Add hover-specific animations if desired
    lectureItems.forEach(item => {
        // Hover animation enhancement
        item.addEventListener('mouseenter', function() {
            // You could add additional hover effects here
            // Example: Add a subtle background animation
            this.style.transition = 'background-color 0.3s ease';
        });
        
        item.addEventListener('mouseleave', function() {
            // Reset any hover-specific styles when mouse leaves
            // This won't affect the click-activated popup
        });
    });
});
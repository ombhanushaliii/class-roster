/**
 * ClassRoster - Lecture Popup Interaction Script
 * Comprehensive fix for lecture popup overlapping issues
 */

document.addEventListener('DOMContentLoaded', function() {
    // Get all lecture items
    const lectureItems = document.querySelectorAll('.lecture-item');
    
    // Track the currently active popup
    let activePopup = null;
    
    // For each lecture item
    lectureItems.forEach(item => {
        const popup = item.querySelector('.lecture-details');
        
        // Add click event to toggle the popup
        item.addEventListener('click', function(e) {
            // Close any other open popups first
            if (activePopup && activePopup !== popup) {
                activePopup.classList.remove('active');
                activePopup.parentElement.style.zIndex = "1"; // Reset z-index
            }
            
            // Toggle the active class on the current popup
            popup.classList.toggle('active');
            
            // Update active popup tracking
            if (popup.classList.contains('active')) {
                activePopup = popup;
                // Bring the current item to front when active
                this.style.zIndex = "100";
                
                // Position the popup properly
                repositionPopup(this, popup);
            } else {
                activePopup = null;
                // Reset z-index when not active
                this.style.zIndex = "1";
            }
            
            e.stopPropagation();
        });
        
        // Prevent the popup from closing when clicking inside it
        popup.addEventListener('click', function(e) {
            e.stopPropagation();
        });
        
        // Handle hover state separately
        item.addEventListener('mouseenter', function() {
            // Only adjust z-index on hover if not in fullscreen mode
            if (!document.querySelector('#timetable-card.fullscreen')) {
                this.style.zIndex = "50";
                
                // Position the popup properly on hover
                repositionPopup(this, popup);
            }
        });
        
        item.addEventListener('mouseleave', function() {
            // Only reset z-index if not active
            if (popup !== activePopup) {
                this.style.zIndex = "1";
            }
        });
    });
    
    // Function to reposition popup to avoid overflow
    function repositionPopup(item, popup) {
        // Reset any previous positioning
        popup.style.left = '';
        popup.style.right = '';
        popup.style.top = '';
        popup.style.bottom = '';
        popup.style.marginTop = '8px';
        popup.style.marginBottom = '';
        
        // Get viewport dimensions
        const viewportWidth = window.innerWidth;
        const viewportHeight = window.innerHeight;
        
        // Get popup and item measurements
        const itemRect = item.getBoundingClientRect();
        
        // We need to temporarily make the popup visible to measure it
        const originalOpacity = popup.style.opacity;
        const originalVisibility = popup.style.visibility;
        popup.style.opacity = '0';
        popup.style.visibility = 'visible';
        
        const popupRect = popup.getBoundingClientRect();
        
        // Restore original visibility
        popup.style.opacity = originalOpacity;
        popup.style.visibility = originalVisibility;
        
        // Check for right edge overflow
        if (itemRect.left + popupRect.width > viewportWidth - 20) {
            popup.style.left = 'auto';
            popup.style.right = '0';
            
            // Move the arrow
            const arrow = popup.querySelector('::before');
            if (arrow) {
                arrow.style.left = 'auto';
                arrow.style.right = '20px';
            }
        }
        
        // Check for bottom overflow
        if (itemRect.bottom + popupRect.height > viewportHeight - 20) {
            popup.style.top = 'auto';
            popup.style.bottom = '100%';
            popup.style.marginTop = '0';
            popup.style.marginBottom = '8px';
            
            // Adjust arrow to point down
            const arrow = popup.querySelector('::before');
            if (arrow) {
                arrow.style.top = 'auto';
                arrow.style.bottom = '-8px';
                arrow.style.transform = 'rotate(225deg)';
            }
        }
        
        // Handle fullscreen mode specially
        if (document.querySelector('#timetable-card.fullscreen')) {
            popup.style.position = 'static';
            popup.style.marginTop = '12px';
            popup.style.width = '100%';
            popup.style.opacity = '1';
            popup.style.visibility = 'visible';
            popup.style.transform = 'none';
        }
    }
    
    // Close popup when clicking outside
    document.addEventListener('click', function() {
        if (activePopup) {
            activePopup.classList.remove('active');
            activePopup.parentElement.style.zIndex = "1";
            activePopup = null;
        }
    });

    // Close popups when pressing Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && activePopup) {
            activePopup.classList.remove('active');
            activePopup.parentElement.style.zIndex = "1";
            activePopup = null;
        }
    });
    
    // Handle fullscreen toggle
    const fullscreenToggle = document.getElementById('fullscreen-toggle');
    if (fullscreenToggle) {
        fullscreenToggle.addEventListener('click', function() {
            // Close any open popups when toggling fullscreen
            if (activePopup) {
                activePopup.classList.remove('active');
                activePopup.parentElement.style.zIndex = "1";
                activePopup = null;
            }
            
            // After a slight delay, recalculate positions for fullscreen mode
            setTimeout(function() {
                const timetableCard = document.getElementById('timetable-card');
                if (timetableCard.classList.contains('fullscreen')) {
                    // In fullscreen mode, adjust all popups
                    lectureItems.forEach(item => {
                        const popup = item.querySelector('.lecture-details');
                        item.classList.add('fullscreen-item');
                        
                        // Force static positioning in fullscreen mode
                        popup.style.position = 'static';
                        popup.style.marginTop = '12px';
                        popup.style.width = '100%';
                        popup.style.boxShadow = '0 2px 8px rgba(0, 0, 0, 0.05)';
                    });
                } else {
                    // In normal mode, reset all popup styles
                    lectureItems.forEach(item => {
                        const popup = item.querySelector('.lecture-details');
                        item.classList.remove('fullscreen-item');
                        
                        // Reset positioning
                        popup.style.position = '';
                        popup.style.marginTop = '';
                        popup.style.width = '';
                        popup.style.boxShadow = '';
                    });
                }
            }, 600); // Wait for fullscreen animation to complete
        });
    }
    
    // Monitor window resize to reposition popups
    window.addEventListener('resize', function() {
        if (activePopup) {
            repositionPopup(activePopup.parentElement, activePopup);
        }
    });
});
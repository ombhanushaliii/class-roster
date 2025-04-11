/**
 * ClassRoster - Enhanced Fullscreen Toggle Animation
 * Provides smooth, modern animations for the timetable fullscreen toggle
 */

document.addEventListener('DOMContentLoaded', function() {
    // Get the necessary elements
    const timetableCard = document.getElementById('timetable-card');
    const fullscreenToggle = document.getElementById('fullscreen-toggle');
    
    if (!timetableCard || !fullscreenToggle) return;
    
    function animateFullscreenTransition() {
        const iconUse = fullscreenToggle.querySelector('use');
        const lectureItems = document.querySelectorAll('.lecture-item');
        const isFullscreen = timetableCard.classList.contains('fullscreen');
        
        // Disable the button during animation to prevent multiple clicks
        fullscreenToggle.disabled = true;
        
        if (isFullscreen) {
            // Exit fullscreen with a nice collapse animation
            
            // First animate the icon rotation
            iconUse.setAttribute('href', '#icon-expand');
            fullscreenToggle.querySelector('svg').style.transform = 'rotate(180deg)';
            setTimeout(() => {
                fullscreenToggle.querySelector('svg').style.transform = 'rotate(0deg)';
            }, 300);
            
            // Prepare lecture items for collapse animation
            lectureItems.forEach((item, index) => {
                item.style.transition = 'all 0.4s ease';
                item.style.transitionDelay = `${0.1 + (lectureItems.length - index) * 0.05}s`;
                item.style.opacity = '0';
                item.style.transform = 'translateY(20px)';
            });
            
            // Start card shrinking animation
            setTimeout(() => {
                // Apply pre-collapse styles
                document.body.style.overflow = '';
                timetableCard.style.transition = 'all 0.5s cubic-bezier(0.68, -0.6, 0.32, 1.6)';
                
                // Create a subtle bounce effect
                timetableCard.style.transform = 'scale(0.95)';
                setTimeout(() => {
                    timetableCard.style.transform = 'scale(1.02)';
                    setTimeout(() => {
                        timetableCard.style.transform = 'scale(1)';
                    }, 150);
                }, 300);
                
                // Remove fullscreen class after initial animation
                timetableCard.classList.remove('fullscreen');
                
                // Reset lecture items to normal view
                setTimeout(() => {
                    lectureItems.forEach(item => {
                        item.classList.remove('fullscreen-item');
                        item.style.opacity = '1';
                        item.style.transform = 'translateY(0)';
                        
                        const details = item.querySelector('.lecture-details');
                        if (details) {
                            details.style.position = 'absolute';
                            details.style.opacity = '0';
                            details.style.visibility = 'hidden';
                        }
                    });
                    
                    // Re-enable button after animation completes
                    fullscreenToggle.disabled = false;
                }, 400);
            }, 300);
        } else {
            // Enter fullscreen with an elegant expansion animation
            
            // First animate the icon rotation
            iconUse.setAttribute('href', '#icon-collapse');
            fullscreenToggle.querySelector('svg').style.transform = 'rotate(180deg)';
            setTimeout(() => {
                fullscreenToggle.querySelector('svg').style.transform = 'rotate(0deg)';
            }, 300);
            
            // Start with a subtle zoom effect
            timetableCard.style.transition = 'all 0.5s cubic-bezier(0.34, 1.56, 0.64, 1)';
            timetableCard.style.transform = 'scale(1.03)';
            
            // Add fullscreen class with smooth transition
            setTimeout(() => {
                timetableCard.classList.add('fullscreen');
                document.body.style.overflow = 'hidden';
                timetableCard.style.transform = 'scale(1)';
                
                // Animate lecture items one by one with staggered effect
                setTimeout(() => {
                    lectureItems.forEach((item, index) => {
                        // First set initial state (invisible and moved down)
                        item.style.opacity = '0';
                        item.style.transform = 'translateY(30px)';
                        item.classList.add('fullscreen-item');
                        
                        // Staggered appearance
                        setTimeout(() => {
                            item.style.transition = 'all 0.5s cubic-bezier(0.34, 1.56, 0.64, 1)';
                            item.style.opacity = '1';
                            item.style.transform = 'translateY(0)';
                            
                            const details = item.querySelector('.lecture-details');
                            if (details) {
                                details.style.position = 'static';
                                details.style.opacity = '1';
                                details.style.visibility = 'visible';
                            }
                        }, 100 + index * 80); // Staggered timing for each item
                    });
                    
                    // Re-enable button after animation completes
                    setTimeout(() => {
                        fullscreenToggle.disabled = false;
                    }, lectureItems.length * 80 + 300);
                }, 300);
            }, 300);
        }
    }
    
    // Add event listener to the fullscreen toggle button
    fullscreenToggle.addEventListener('click', animateFullscreenTransition);
    
    // Add ESC key to exit fullscreen
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const timetableCard = document.querySelector('.card.fullscreen');
            if (timetableCard && !fullscreenToggle.disabled) {
                animateFullscreenTransition();
            }
        }
    });
});
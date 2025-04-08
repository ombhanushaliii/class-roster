/**
 * ClassRoster - Fullscreen Toggle Script
 * Handles the expansion of timetable card to fullscreen mode
 */

document.addEventListener('DOMContentLoaded', function() 
{
    function fullscreen_toggle() 
    {
        const timetableCard = document.getElementById('timetable-card');
        const toggleButton = document.getElementById('fullscreen-toggle');
        const iconUse = toggleButton.querySelector('use');
        const lectureItems = document.querySelectorAll('.lecture-item');

        // Add animation class
        timetableCard.classList.add('animating');

        if (timetableCard.classList.contains('fullscreen')) {
            // Exit fullscreen
            setTimeout(function() {
                timetableCard.classList.remove('fullscreen');
                iconUse.setAttribute('href', '#icon-expand');
                document.body.style.overflow = '';

                // Restore normal lecture item view
                lectureItems.forEach(item => {
                    item.classList.remove('fullscreen-item');
                    const details = item.querySelector('.lecture-details');
                    if (details) {
                        details.style.position = 'absolute';
                        details.style.opacity = '0';
                        details.style.visibility = 'hidden';
                    }
                });

                setTimeout(function() {
                    timetableCard.classList.remove('animating');
                }, 300);
            }, 1000);
        } else {
            // Enter fullscreen with delay
            setTimeout(function() {
                timetableCard.classList.add('fullscreen');
                iconUse.setAttribute('href', '#icon-collapse');
                document.body.style.overflow = 'hidden';

                // Switch to expanded lecture item view
                lectureItems.forEach(item => {
                    item.classList.add('fullscreen-item');
                    const details = item.querySelector('.lecture-details');
                    if (details) {
                        details.style.position = 'static';
                        details.style.opacity = '1';
                        details.style.visibility = 'visible';
                    }
                });

                setTimeout(function() {
                    timetableCard.classList.remove('animating');
                }, 300);
            }, 1000);
        }
    }

    // Add event listener to the fullscreen toggle button
    const fullscreenToggle = document.getElementById('fullscreen-toggle');
    if (fullscreenToggle) {
        fullscreenToggle.addEventListener('click', fullscreen_toggle);
    }

    // Add ESC key to exit fullscreen
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const timetableCard = document.querySelector('.card.fullscreen');
            if (timetableCard) {
                fullscreen_toggle();
            }
        }
    });
});
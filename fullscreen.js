document.addEventListener('DOMContentLoaded', function() 
{
    function fullscreen_toggle() 
    {
        const timetableCard = document.getElementById('timetable-card');
        const toggleButton = document.getElementById('fullscreen-toggle');
        const iconUse = toggleButton.querySelector('use');
        
        // Add animation class
        timetableCard.classList.add('animating');
        
        if (timetableCard.classList.contains('fullscreen')) {
            // Exit fullscreen
            setTimeout(function() {
                timetableCard.classList.remove('fullscreen');
                iconUse.setAttribute('href', '#icon-expand');
                document.body.style.overflow = '';
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
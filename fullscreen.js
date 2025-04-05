document.addEventListener('DOMContentLoaded', function() {
    function toggleTimetableFullscreen() {
        const timetableCard = document.getElementById('timetable-card');
        const toggleButton = document.getElementById('fullscreen-toggle');
        const iconUse = toggleButton.querySelector('use');
        
        if (timetableCard.classList.contains('fullscreen')) {
            // Exit fullscreen
            timetableCard.classList.remove('fullscreen');
            iconUse.setAttribute('href', '#icon-expand');
            document.body.style.overflow = '';
        } else {
            // Enter fullscreen
            timetableCard.classList.add('fullscreen');
            iconUse.setAttribute('href', '#icon-collapse');
            document.body.style.overflow = 'hidden';
        }
    }

    // Add event listener to the fullscreen toggle button
    const fullscreenToggle = document.getElementById('fullscreen-toggle');
    if (fullscreenToggle) {
        fullscreenToggle.addEventListener('click', toggleTimetableFullscreen);
    }
    
    // Add ESC key to exit fullscreen
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const timetableCard = document.querySelector('.card.fullscreen');
            if (timetableCard) {
                toggleTimetableFullscreen();
            }
        }
    });
});
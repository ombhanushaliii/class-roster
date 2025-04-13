/**
 * ClassRoster - Enhanced Fullscreen Toggle Animation + Real Fullscreen
 * Provides smooth, modern animations for the timetable fullscreen toggle
 */

document.addEventListener('DOMContentLoaded', function() {
    const timetableCard = document.getElementById('timetable-card');
    const fullscreenToggle = document.getElementById('fullscreen-toggle');

    if (!timetableCard || !fullscreenToggle) return;

    function requestTrueFullscreen() {
        const req = timetableCard.requestFullscreen ||
                    timetableCard.webkitRequestFullscreen ||
                    timetableCard.msRequestFullscreen;

        if (req) {
            req.call(timetableCard).catch(err => {
                console.error("Fullscreen error:", err.message);
            });
        }
    }

    function exitTrueFullscreen() {
        const exit = document.exitFullscreen ||
                     document.webkitExitFullscreen ||
                     document.msExitFullscreen;

        if (document.fullscreenElement && exit) {
            exit.call(document).catch(err => {
                console.error("Exit Fullscreen error:", err.message);
            });
        }
    }

    function animateFullscreenTransition() {
        const iconUse = fullscreenToggle.querySelector('use');
        const lectureItems = document.querySelectorAll('.lecture-item');
        const isFullscreen = timetableCard.classList.contains('fullscreen');

        fullscreenToggle.disabled = true;

        if (isFullscreen) {
            iconUse.setAttribute('href', '#icon-expand');
            iconUse.setAttribute('xlink:href', '#icon-expand');
            fullscreenToggle.querySelector('svg').style.transform = 'rotate(180deg)';
            setTimeout(() => {
                fullscreenToggle.querySelector('svg').style.transform = 'rotate(0deg)';
            }, 300);

            lectureItems.forEach((item, index) => {
                item.style.transition = 'all 0.4s ease';
                item.style.transitionDelay = `${0.1 + (lectureItems.length - index) * 0.05}s`;
                item.style.opacity = '0';
                item.style.transform = 'translateY(20px)';
            });

            setTimeout(() => {
                document.body.style.overflow = '';
                timetableCard.style.transition = 'all 0.5s cubic-bezier(0.68, -0.6, 0.32, 1.6)';
                timetableCard.style.transform = 'scale(0.95)';

                setTimeout(() => {
                    timetableCard.style.transform = 'scale(1.02)';
                    setTimeout(() => {
                        timetableCard.style.transform = 'scale(1)';
                    }, 150);
                }, 300);

                timetableCard.classList.remove('fullscreen');

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

                    fullscreenToggle.disabled = false;
                }, 400);

                exitTrueFullscreen();
            }, 300);
        } else {
            iconUse.setAttribute('href', '#icon-collapse');
            iconUse.setAttribute('xlink:href', '#icon-collapse');
            fullscreenToggle.querySelector('svg').style.transform = 'rotate(180deg)';
            setTimeout(() => {
                fullscreenToggle.querySelector('svg').style.transform = 'rotate(0deg)';
            }, 300);

            timetableCard.style.transition = 'all 0.5s cubic-bezier(0.34, 1.56, 0.64, 1)';
            timetableCard.style.transform = 'scale(1.03)';

            setTimeout(() => {
                timetableCard.classList.add('fullscreen');
                document.body.style.overflow = 'hidden';
                timetableCard.style.transform = 'scale(1)';

                setTimeout(() => {
                    lectureItems.forEach((item, index) => {
                        item.style.opacity = '0';
                        item.style.transform = 'translateY(30px)';
                        item.classList.add('fullscreen-item');

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
                        }, 100 + index * 80);
                    });

                    setTimeout(() => {
                        fullscreenToggle.disabled = false;
                    }, lectureItems.length * 80 + 300);
                }, 300);

                requestTrueFullscreen();
            }, 300);
        }
    }

    fullscreenToggle.addEventListener('click', animateFullscreenTransition);

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const isActive = timetableCard.classList.contains('fullscreen');
            if (isActive && !fullscreenToggle.disabled) {
                animateFullscreenTransition();
            }
        }
    });

    document.addEventListener('fullscreenchange', () => {
        if (!document.fullscreenElement && timetableCard.classList.contains('fullscreen')) {
            animateFullscreenTransition();
        }
    });
});

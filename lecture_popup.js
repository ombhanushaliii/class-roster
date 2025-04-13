/**
 * ClassRoster - Lecture Hover Popup
 * Adds hover tooltips to lecture items
 */

document.addEventListener('DOMContentLoaded', function () {
    const lectureItems = document.querySelectorAll('.lecture-item');

    lectureItems.forEach(item => {
        // Create popup element
        const popup = document.createElement('div');
        popup.className = 'lecture-popup';
        popup.textContent = item.getAttribute('data-info') || 'Lecture Details';
        
        // Style the popup
        Object.assign(popup.style, {
            position: 'absolute',
            background: '#fff',
            border: '1px solid #ccc',
            padding: '6px 10px',
            fontSize: '12px',
            borderRadius: '6px',
            boxShadow: '0 2px 6px rgba(0,0,0,0.15)',
            display: 'none',
            zIndex: '1000',
            whiteSpace: 'nowrap',
            pointerEvents: 'none',
            transition: 'opacity 0.2s ease',
        });

        document.body.appendChild(popup);

        // Hover in
        item.addEventListener('mouseenter', (e) => {
            const rect = item.getBoundingClientRect();
            popup.style.left = `${rect.left + window.scrollX + 10}px`;
            popup.style.top = `${rect.top + window.scrollY - 10}px`;
            popup.style.display = 'block';
            popup.style.opacity = '1';
        });

        // Hover out
        item.addEventListener('mouseleave', () => {
            popup.style.display = 'none';
            popup.style.opacity = '0';
        });
    });
});

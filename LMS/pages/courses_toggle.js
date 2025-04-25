/**
 * Course curriculum toggle functionality
 * Allows students to show/hide the complete course curriculum
 */

function toggleCurriculum(courseId) {
    const container = document.getElementById(`curriculum-${courseId}`);
    const button = event.target;
    
    if (container.style.display === 'none') {
        // Close any open curriculum first
        document.querySelectorAll('.curriculum-container').forEach(el => {
            el.style.display = 'none';
            
            // Reset any other buttons
            const buttons = document.querySelectorAll('.toggle-curriculum-btn');
            buttons.forEach(btn => {
                if (btn !== button) {
                    btn.textContent = 'Read more';
                    btn.setAttribute('aria-expanded', 'false');
                }
            });
        });
        
        // Open this curriculum
        container.style.display = 'block';
        button.textContent = 'Hide details';
        button.setAttribute('aria-expanded', 'true');
        
        // Smooth scroll to the curriculum
        container.scrollIntoView({ behavior: 'smooth', block: 'start' });
    } else {
        container.style.display = 'none';
        button.textContent = 'Read more';
        button.setAttribute('aria-expanded', 'false');
    }
}

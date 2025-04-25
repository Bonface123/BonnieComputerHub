// JS for dynamic week/topic fields in course schedule forms

document.addEventListener('DOMContentLoaded', function() {
    const scheduleFields = document.getElementById('schedule-fields');
    if (!scheduleFields) return;

    scheduleFields.addEventListener('click', function(e) {
        if (e.target.classList.contains('add-week')) {
            e.preventDefault();
            const weekCount = scheduleFields.querySelectorAll('input[type="text"]').length + 1;
            const div = document.createElement('div');
            div.className = 'flex mb-2';
            div.innerHTML = `<input type="text" name="schedule_weeks[Week ${weekCount}]" placeholder="Topic for Week ${weekCount}" class="w-full px-3 py-2 border rounded-l">
                <button type="button" class="remove-week bg-red-500 text-white px-4 rounded-r">&minus;</button>`;
            scheduleFields.appendChild(div);
        }
        if (e.target.classList.contains('remove-week')) {
            e.preventDefault();
            if (scheduleFields.querySelectorAll('div').length > 1) {
                e.target.parentElement.remove();
            }
        }
    });
});

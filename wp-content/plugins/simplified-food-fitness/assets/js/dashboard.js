document.addEventListener('DOMContentLoaded', () => {
    const checkboxes = document.querySelectorAll('.sff-meal-progress');
    const bar = document.getElementById('sff-progress-bar');
    const text = document.getElementById('sff-progress-text');

    function updateDisplay() {
        const total = checkboxes.length;
        const completed = Array.from(checkboxes).filter(cb => cb.checked).length;
        if (bar) {
            bar.max = total;
            bar.value = completed;
        }
        if (text) {
            text.textContent = `${completed}/${total} meals completed`;
        }
    }

    checkboxes.forEach(cb => {
        cb.addEventListener('change', function() {
            updateDisplay();
            const formData = new URLSearchParams();
            formData.append('action', 'sff_update_meal_progress');
            formData.append('meal_id', this.dataset.mealId);
            formData.append('completed', this.checked ? '1' : '0');
            formData.append('nonce', sff_dashboard.nonce);

            fetch(sff_dashboard.ajax_url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: formData.toString()
            });
        });
    });

    updateDisplay();
});

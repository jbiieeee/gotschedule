// Task Manager & Dashboard Logic with AJAX
document.addEventListener('DOMContentLoaded', () => {
    // Mini Calendar Logic
    const calendarWidget = document.getElementById('calendar-widget');
    const today = new Date();
    
    function renderMiniCalendar(date) {
        const month = date.getMonth();
        const year = date.getFullYear();
        const firstDay = new Date(year, month, 1).getDay();
        const daysInMonth = new Date(year, month + 1, 0).getDate();
        
        const monthNames = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
        
        let html = `
            <div class="calendar-header d-flex justify-content-between align-items-center mb-3">
                <span class="fw-bold text-primary">${monthNames[month]} ${year}</span>
            </div>
            <div class="calendar-grid">
                <div class="grid-row d-flex text-muted fs-8 mb-2">
                    <div class="flex-fill text-center">S</div>
                    <div class="flex-fill text-center">M</div>
                    <div class="flex-fill text-center">T</div>
                    <div class="flex-fill text-center">W</div>
                    <div class="flex-fill text-center">T</div>
                    <div class="flex-fill text-center">F</div>
                    <div class="flex-fill text-center">S</div>
                </div>
                <div class="grid-days d-flex flex-wrap">
        `;
        
        for (let i = 0; i < firstDay; i++) {
            html += `<div class="day-cell flex-fill text-center p-1 opacity-0" style="width: 14.28%"></div>`;
        }
        
        for (let day = 1; day <= daysInMonth; day++) {
            const isToday = day === today.getDate() && month === today.getMonth() && year === today.getFullYear();
            html += `
                <div class="day-cell flex-fill text-center p-1 fs-7 ${isToday ? 'bg-primary text-white rounded-circle shadow-sm' : 'text-muted'}" style="width: 14.28%; cursor: pointer;">
                    ${day}
                </div>
            `;
        }
        
        html += `</div></div>`;
        if (calendarWidget) calendarWidget.innerHTML = html;
    }

    renderMiniCalendar(today);

    // --- AJAX Logic ---

    // 1. Add Task AJAX
    const taskForm = document.getElementById('task-form');
    if (taskForm) {
        taskForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(taskForm);
            formData.append('add_task', '1');
            formData.append('ajax', '1');

            try {
                const response = await fetch('task.php', {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                const result = await response.json();

                if (result.status === 'success') {
                    window.notifier.success('Success', result.message);
                    // Close modal and reload content or inject HTML
                    const modal = bootstrap.Modal.getInstance(document.getElementById('taskModal'));
                    modal.hide();
                    setTimeout(() => window.location.reload(), 1000); // Reload for now to keep it simple, but with a nice notification first
                } else {
                    window.notifier.error('Error', result.message);
                }
            } catch (error) {
                window.notifier.error('Network Error', 'Could not reach the server.');
            }
        });
    }

    // 2. Toggle Task AJAX
    document.querySelectorAll('form button[name="toggle_task"]').forEach(btn => {
        btn.addEventListener('click', async (e) => {
            e.preventDefault();
            const form = btn.closest('form');
            const formData = new FormData(form);
            formData.append('toggle_task', '1');
            formData.append('ajax', '1');

            const taskCard = btn.closest('.task-card');

            try {
                const response = await fetch('task.php', {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                const result = await response.json();

                if (result.status === 'success') {
                    window.notifier.success('Updated', result.message);
                    // Update UI
                    if (result.data.status === 'completed') {
                        taskCard.classList.add('completed');
                        btn.innerHTML = '<i class="bi bi-check-circle-fill"></i>';
                        taskCard.querySelector('.badge').className = 'badge bg-success-subtle text-success rounded-pill px-3 py-2 fs-8 fw-bold border border-success border-opacity-20';
                        taskCard.querySelector('.badge').textContent = 'COMPLETED';
                    } else {
                        taskCard.classList.remove('completed');
                        btn.innerHTML = '<i class="bi bi-circle"></i>';
                        taskCard.querySelector('.badge').className = 'badge bg-primary-subtle text-primary rounded-pill px-3 py-2 fs-8 fw-bold border border-primary border-opacity-20';
                        taskCard.querySelector('.badge').textContent = 'PENDING';
                    }
                } else {
                    window.notifier.error('Error', result.message);
                }
            } catch (error) {
                window.notifier.error('Network Error', 'Could not reach the server.');
            }
        });
    });

    // 3. Delete Task AJAX
    document.querySelectorAll('form button[name="delete_task"]').forEach(btn => {
        btn.addEventListener('click', async (e) => {
            e.preventDefault();
            if (!confirm('Are you sure you want to delete this task?')) return;

            const form = btn.closest('form');
            const formData = new FormData(form);
            formData.append('delete_task', '1');
            formData.append('ajax', '1');

            const taskCard = btn.closest('.task-card');

            try {
                const response = await fetch('task.php', {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                const result = await response.json();

                if (result.status === 'success') {
                    window.notifier.success('Deleted', result.message);
                    taskCard.style.transition = 'all 0.4s ease';
                    taskCard.style.opacity = '0';
                    taskCard.style.transform = 'translateX(20px)';
                    setTimeout(() => taskCard.remove(), 400);
                } else {
                    window.notifier.error('Error', result.message);
                }
            } catch (error) {
                window.notifier.error('Network Error', 'Could not reach the server.');
            }
        });
    });
});

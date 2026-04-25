document.addEventListener('DOMContentLoaded', () => {
    // Shared Notifier
    const notifier = window.notifier;

    // 1. Search & Filter logic (existing)
    const searchInput = document.getElementById('task-search');
    const taskItems = document.querySelectorAll('.task-item');
    if (searchInput) {
        searchInput.addEventListener('input', (e) => {
            const query = e.target.value.toLowerCase();
            taskItems.forEach(item => {
                const name = item.getAttribute('data-name');
                item.style.display = name.includes(query) ? 'flex' : 'none';
            });
        });
    }

    // 2. Habit Tracker Logic
    const habitsContainer = document.getElementById('habits-container');
    async function loadHabits() {
        if (!habitsContainer) return;
        const res = await fetch('habit_api.php?action=fetch');
        const habits = await res.json();
        
        habitsContainer.innerHTML = habits.map(habit => {
            const daysArr = Array.from({length: 31}, (_, i) => i + 1);
            const todayStr = new Date().toISOString().split('T')[0];
            const monthPrefix = todayStr.substring(0, 8); // YYYY-MM-
            
            return `
                <div class="habit-item mb-4 animate__animated animate__fadeIn">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="fs-8 fw-bold text-success-emphasis">${habit.habit_name}</span>
                        <i class="bi bi-trash fs-9 text-muted cursor-pointer" onclick="deleteHabit(${habit.id})"></i>
                    </div>
                    <div class="habit-grid">
                        ${daysArr.map(day => {
                            const dateStr = monthPrefix + day.toString().padStart(2, '0');
                            const isCompleted = habit.history.includes(dateStr);
                            return `<div class="habit-day ${isCompleted ? 'completed' : ''}" 
                                        title="${dateStr}" 
                                        onclick="toggleHabitDay(${habit.id}, '${dateStr}')"></div>`;
                        }).join('')}
                    </div>
                </div>
            `;
        }).join('');
        if (!habits.length) habitsContainer.innerHTML = '<div class="text-center py-3 opacity-30 fs-9">No habits tracked yet.</div>';
    }

    window.toggleHabitDay = async (id, date) => {
        const formData = new FormData();
        formData.append('action', 'toggle_day');
        formData.append('id', id);
        formData.append('date', date);
        await fetch('habit_api.php', { method: 'POST', body: formData });
        loadHabits();
    };

    window.addHabitPrompt = async () => {
        const name = prompt("What habit do you want to track?");
        if (!name) return;
        const formData = new FormData();
        formData.append('action', 'add');
        formData.append('name', name);
        await fetch('habit_api.php', { method: 'POST', body: formData });
        loadHabits();
    };

    window.deleteHabit = async (id) => {
        if (!confirm("Delete this habit?")) return;
        const formData = new FormData();
        formData.append('action', 'delete');
        formData.append('id', id);
        await fetch('habit_api.php', { method: 'POST', body: formData });
        loadHabits();
    };

    loadHabits();

    // 3. Brain Dump Logic
    const dumpArea = document.getElementById('brain-dump-area');
    const dumpStatus = document.getElementById('dump-status');
    let dumpTimeout;

    if (dumpArea) {
        // Initial Fetch
        fetch('brain_dump_api.php?fetch=1').then(r => r.text()).then(text => dumpArea.value = text);

        dumpArea.addEventListener('input', () => {
            dumpStatus.innerText = 'Saving...';
            clearTimeout(dumpTimeout);
            dumpTimeout = setTimeout(() => {
                const formData = new FormData();
                formData.append('save', '1');
                formData.append('content', dumpArea.value);
                fetch('brain_dump_api.php', { method: 'POST', body: formData })
                .then(() => dumpStatus.innerText = 'Auto-saved');
            }, 1000);
        });
    }

    // 4. Enhanced Notes Logic (Pinned, Checklist)
    const notesContainer = document.getElementById('sticky-notes-container');
    async function loadNotes() {
        if (!notesContainer) return;
        const res = await fetch('notes_api.php?action=fetch');
        const notes = await res.json();
        
        notesContainer.innerHTML = notes.map(note => `
            <div class="note-item p-3 rounded-3 bg-white mb-2 shadow-sm border ${note.is_pinned ? 'border-primary border-opacity-30' : 'border-warning border-opacity-20'} animate__animated animate__fadeIn">
                <div class="d-flex justify-content-between mb-1">
                    <div class="d-flex align-items-center gap-2 flex-grow-1">
                        <i class="bi bi-pin-fill cursor-pointer ${note.is_pinned ? 'text-primary' : 'text-muted opacity-30'} fs-9" onclick="toggleNoteFeature(${note.id}, 'toggle_pin')"></i>
                        <input type="text" class="fw-bold fs-8 border-0 bg-transparent text-dark w-100" value="${note.title}" onblur="saveNote(${note.id}, this.value, null)">
                    </div>
                    <div class="d-flex gap-2">
                        <i class="bi bi-list-check cursor-pointer ${note.is_checklist ? 'text-success' : 'text-muted'} fs-9" title="Checklist Mode" onclick="toggleNoteFeature(${note.id}, 'toggle_checklist')"></i>
                        <i class="bi bi-share cursor-pointer text-muted fs-9" title="Share" onclick="shareNote(${note.id})"></i>
                        <i class="bi bi-trash fs-9 text-muted cursor-pointer" onclick="deleteNote(${note.id})"></i>
                    </div>
                </div>
                <textarea class="fs-8 text-muted border-0 bg-transparent w-100 hide-scroll" style="height: ${note.is_checklist ? 'auto' : '80px'};" onblur="saveNote(${note.id}, null, this.value)">${note.content}</textarea>
            </div>
        `).join('');
    }

    window.toggleNoteFeature = async (id, action) => {
        const formData = new FormData();
        formData.append('action', action);
        formData.append('id', id);
        await fetch('notes_api.php', { method: 'POST', body: formData });
        loadNotes();
    };

    window.shareNote = async (id) => {
        const formData = new FormData();
        formData.append('action', 'share');
        formData.append('id', id);
        const res = await fetch('notes_api.php', { method: 'POST', body: formData });
        const data = await res.json();
        if (data.status === 'success') {
            prompt("Collab Key Generated! Share this with your teammate:", data.collab_key);
        }
    };

    window.saveNote = async (id, title, content) => {
        const formData = new FormData();
        formData.append('action', 'save');
        if (id) formData.append('id', id);
        if (title !== null) formData.append('title', title);
        if (content !== null) formData.append('content', content);
        await fetch('notes_api.php', { method: 'POST', body: formData });
    };

    window.deleteNote = async (id) => {
        if (!confirm("Delete note?")) return;
        const formData = new FormData();
        formData.append('action', 'delete');
        formData.append('id', id);
        await fetch('notes_api.php', { method: 'POST', body: formData });
        loadNotes();
    };

    loadNotes();

    // 5. System Health & Sync (already exists)
});

// Global Task Functions
window.openTaskModal = function(date = null) {
    const modal = new bootstrap.Modal(document.getElementById('taskModal'));
    const dateInput = document.querySelector('input[name="due_date"]');
    if (date && dateInput) dateInput.value = date;
    modal.show();
};

window.toggleTaskStatus = async function(id, current) {
    const formData = new FormData();
    formData.append('toggle_task', '1');
    formData.append('task_id', id);
    formData.append('current_status', current);
    const res = await fetch('task.php', { method: 'POST', body: formData });
    const data = await res.json();
    if (data.status === 'success') location.reload();
};

window.deleteTask = async function(id) {
    if (!confirm('Are you sure you want to delete this task?')) return;
    const formData = new FormData();
    formData.append('delete_task', '1');
    formData.append('task_id', id);
    const res = await fetch('task.php', { method: 'POST', body: formData });
    const data = await res.json();
    if (data.status === 'success') location.reload();
};

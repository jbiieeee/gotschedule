/**
 * Notes Management Logic with AJAX
 */
document.addEventListener('DOMContentLoaded', () => {
    
    // 1. Add Note AJAX
    const addNoteForm = document.querySelector('#addNoteModal form');
    if (addNoteForm) {
        addNoteForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(addNoteForm);
            formData.append('add_note', '1');
            formData.append('ajax', '1');

            try {
                const response = await fetch('api/notes_action.php', {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                const result = await response.json();

                if (result.status === 'success') {
                    window.notifier.success('Note Saved', result.message);
                    bootstrap.Modal.getInstance(document.getElementById('addNoteModal')).hide();
                    setTimeout(() => window.location.reload(), 800);
                } else {
                    window.notifier.error('Error', result.message);
                }
            } catch (error) {
                window.notifier.error('Network Error', 'Failed to reach the server.');
            }
        });
    }

    // 2. Edit Note AJAX
    document.querySelectorAll('.modal[id^="editNoteModal"] form').forEach(form => {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(form);
            formData.append('edit_note', '1');
            formData.append('ajax', '1');

            const modalId = form.closest('.modal').id;

            try {
                const response = await fetch('api/notes_action.php', {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                const result = await response.json();

                if (result.status === 'success') {
                    window.notifier.success('Note Updated', result.message);
                    bootstrap.Modal.getInstance(document.getElementById(modalId)).hide();
                    setTimeout(() => window.location.reload(), 800);
                } else {
                    window.notifier.error('Error', result.message);
                }
            } catch (error) {
                window.notifier.error('Network Error', 'Failed to reach the server.');
            }
        });
    });

    // 3. Delete Note AJAX (Intercepting the anchor tag)
    document.querySelectorAll('a[href*="delete="]').forEach(link => {
        link.addEventListener('click', async (e) => {
            e.preventDefault();
            if (!confirm('Delete this note?')) return;

            const url = link.href + '&ajax=1';
            const noteCard = link.closest('.col-md-6, .col-lg-4, .col-xl-3');

            try {
                const response = await fetch(url, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                const result = await response.json();

                if (result.status === 'success') {
                    window.notifier.success('Deleted', result.message);
                    noteCard.style.transition = 'all 0.5s cubic-bezier(0.4, 0, 0.2, 1)';
                    noteCard.style.opacity = '0';
                    noteCard.style.transform = 'scale(0.9) translateY(20px)';
                    setTimeout(() => noteCard.remove(), 500);
                } else {
                    window.notifier.error('Error', result.message);
                }
            } catch (error) {
                window.notifier.error('Network Error', 'Failed to delete note.');
            }
        });
    });
});

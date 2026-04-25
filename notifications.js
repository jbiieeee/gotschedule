/**
 * Notification Manager for GotSchedule
 * Handles premium toast notifications with glassmorphic design.
 */

class NotificationManager {
    constructor() {
        this.container = document.createElement('div');
        this.container.className = 'notification-container';
        document.body.appendChild(this.container);
    }

    /**
     * Show a toast notification
     * @param {string} title - The title of the notification
     * @param {string} message - The message body
     * @param {string} type - 'success', 'error', or 'info'
     * @param {number} duration - Time in ms before auto-closing (0 for manual)
     */
    show(title, message, type = 'info', duration = 5000) {
        const toast = document.createElement('div');
        toast.className = `toast-modern toast-${type}`;
        
        const icons = {
            success: 'bi-check-circle-fill',
            error: 'bi-exclamation-triangle-fill',
            info: 'bi-info-circle-fill'
        };

        toast.innerHTML = `
            <div class="toast-icon">
                <i class="bi ${icons[type]}"></i>
            </div>
            <div class="toast-content">
                <div class="toast-title">${title}</div>
                <div class="toast-message">${message}</div>
            </div>
            <button class="toast-close">
                <i class="bi bi-x-lg"></i>
            </button>
            <div class="toast-progress"></div>
        `;

        this.container.appendChild(toast);

        const progress = toast.querySelector('.toast-progress');
        const closeBtn = toast.querySelector('.toast-close');

        if (duration > 0) {
            progress.style.animation = `toastProgress ${duration}ms linear forwards`;
        } else {
            progress.style.display = 'none';
        }

        const dismiss = () => {
            toast.classList.add('hide');
            setTimeout(() => {
                toast.remove();
            }, 400);
        };

        closeBtn.onclick = dismiss;

        if (duration > 0) {
            setTimeout(dismiss, duration);
        }
    }

    success(title, message) { this.show(title, message, 'success'); }
    error(title, message) { this.show(title, message, 'error'); }
    info(title, message) { this.show(title, message, 'info'); }
}

// Add progress bar animation keyframes dynamically if not in CSS
if (!document.getElementById('toast-progress-styles')) {
    const style = document.createElement('style');
    style.id = 'toast-progress-styles';
    style.innerHTML = `
        @keyframes toastProgress {
            from { transform: scaleX(1); }
            to { transform: scaleX(0); }
        }
    `;
    document.head.appendChild(style);
}

const notifier = new NotificationManager();
window.notifier = notifier; // Make it globally accessible

// Task Manager & Dashboard Logic
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
});

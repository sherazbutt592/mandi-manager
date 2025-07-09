document.addEventListener("DOMContentLoaded", function () {
    const badge = document.querySelector('.noti-icon-badge');
    const notiContainer = document.querySelector('#notification-content'); // fixed selector

    function fetchNotifications() {
        fetch('api/notifications_handler.php')
            .then(res => res.json())
            .then(data => {
                let html = '';
                let unreadCount = 0;
                const today = new Date().toDateString();
                const grouped = {};
                const totalCount = data.length;


                data.forEach(noti => {
                    const dateKey = new Date(noti.created_at).toDateString();
                    if (!grouped[dateKey]) grouped[dateKey] = [];
                    grouped[dateKey].push(noti);
                    if (!noti.is_read) unreadCount++;
                });

                for (const date in grouped) {
                    html += `<h5 class="text-muted font-size-13 fw-normal mt-2">${date === today ? 'Today' : date}</h5>`;
                    grouped[date].forEach(noti => {
                        html += `
<a href="${noti.link || 'javascript:void(0);'}" class="dropdown-item p-0 notify-item card ...
                            <div class="card-body">
                                <span class="float-end noti-close-btn text-muted" data-id="${noti.id}">
                                    <i class="mdi mdi-close"></i>
                                </span>
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0">
                                        <div class="notify-icon bg-primary">
                                            <i class="${noti.icon || 'mdi mdi-bell'}"></i>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1 text-truncate ms-2">
                                        <h5 class="noti-item-title fw-semibold font-size-14">${noti.title}
                                            <small class="fw-normal text-muted ms-1">${timeAgo(noti.created_at)}</small>
                                        </h5>
                                        <small class="noti-item-subtitle text-muted">${noti.message}</small>
                                    </div>
                                </div>
                            </div>
                        </a>`;
                    });
                }

                notiContainer.innerHTML = html || `<p class="text-center text-muted my-3">No notifications</p>`;
            // 🔔 Show total notifications count as badge
            badge.textContent = totalCount > 0 ? totalCount : '';
            badge.style.display = totalCount > 0 ? 'inline-block' : 'none';            })
            .catch(err => {
                console.error('Error fetching notifications:', err);
                notiContainer.innerHTML = `<p class="text-center text-danger my-3">Failed to load</p>`;
            });
    }

    function timeAgo(dateStr) {
        const now = new Date();
        const then = new Date(dateStr);
        const seconds = Math.floor((now - then) / 1000);
        if (seconds < 60) return `${seconds}s ago`;
        const minutes = Math.floor(seconds / 60);
        if (minutes < 60) return `${minutes}m ago`;
        const hours = Math.floor(minutes / 60);
        if (hours < 24) return `${hours}h ago`;
        return then.toLocaleDateString();
    }

    // Delete notification on close icon click
    document.querySelector('#notification-content').addEventListener('click', function (e) {
        const closeBtn = e.target.closest('.noti-close-btn');
        if (closeBtn) {
            const id = closeBtn.dataset.id;
            fetch('api/notifications_handler.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `id=${id}`
            })
            .then(res => res.json())
            .then(response => {
                if (response.status === 'success') {
                    fetchNotifications(); // reload after delete
                }
            });
        }
    });

    // Handle "Clear All" click
document.getElementById('clear-all-btn').addEventListener('click', function () {
    if (confirm('Are you sure you want to clear all notifications?')) {
        fetch('api/notifications_handler.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'clear_all=1'
        })
        .then(res => res.json())
        .then(response => {
            if (response.status === 'success') {
                fetchNotifications();
            }
        });
    }
});


    // Initial load
    fetchNotifications();

    // Refresh every 30 seconds (optional)
    setInterval(fetchNotifications, 30000);
});

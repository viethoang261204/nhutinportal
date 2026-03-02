(() => {
    const apiUrl = './api/dashboard.php';

    const dom = {
        statCustomers: document.getElementById('statCustomers'),
        statDocuments: document.getElementById('statDocuments'),
        statTickets: document.getElementById('statTickets'),
        statUsers: document.getElementById('statUsers'),
        statPosts: document.getElementById('statPosts'),
        recentCustomersBody: document.getElementById('recentCustomersBody'),
        recentTicketsBody: document.getElementById('recentTicketsBody'),
        quickTicketsDesc: document.getElementById('quickTicketsDesc'),
        welcomeText: document.getElementById('welcomeText'),
        welcomeSub: document.getElementById('welcomeSub'),
    };

    function escapeHtml(v) {
        return String(v ?? '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
    }

    function formatDate(str) {
        if (!str) return '--';
        const d = new Date(str.replace(' ', 'T'));
        if (Number.isNaN(d.getTime())) return '--';
        return d.toLocaleDateString('vi-VN');
    }

    function statusLabel(s) {
        if (s === 'active') return 'Đang hoạt động';
        if (s === 'pending') return 'Chờ duyệt';
        if (s === 'inactive') return 'Ngưng';
        return s || '--';
    }

    function ticketStatusLabel(s) {
        if (s === 'open') return 'Đang mở';
        if (s === 'progress') return 'Đang xử lý';
        if (s === 'resolved') return 'Đã giải quyết';
        return s || '--';
    }

    function renderStats(stats) {
        const s = stats || {};
        if (dom.statCustomers) dom.statCustomers.textContent = Number(s.customers || 0).toLocaleString('vi-VN');
        if (dom.statDocuments) dom.statDocuments.textContent = Number(s.documents || 0).toLocaleString('vi-VN');
        if (dom.statTickets) dom.statTickets.textContent = Number(s.tickets_pending || 0).toLocaleString('vi-VN');
        if (dom.statUsers) dom.statUsers.textContent = Number(s.users || 0).toLocaleString('vi-VN');
        if (dom.statPosts) dom.statPosts.textContent = Number(s.posts || 0).toLocaleString('vi-VN');
        if (dom.quickTicketsDesc) dom.quickTicketsDesc.textContent = `${s.tickets_pending || 0} tickets đang chờ`;
    }

    function renderRecentCustomers(list) {
        if (!dom.recentCustomersBody) return;
        if (!list || !list.length) {
            dom.recentCustomersBody.innerHTML = '<tr><td colspan="4" style="text-align:center;color:#888;padding:24px;">Chưa có khách hàng nào.</td></tr>';
            return;
        }
        dom.recentCustomersBody.innerHTML = list.map((c) => {
            const statusClass = (c.status || '').toLowerCase();
            return `
                <tr>
                    <td>
                        <div class="user-cell">
                            <img src="${escapeHtml(c.avatar_url)}" alt="" class="user-avatar-small" onerror="this.src='../img/default.png'">
                            <span style="font-weight: 600;">${escapeHtml(c.name)}</span>
                        </div>
                    </td>
                    <td>${escapeHtml(c.email)}</td>
                    <td><span class="status ${escapeHtml(statusClass)}">${escapeHtml(statusLabel(c.status))}</span></td>
                    <td>${escapeHtml(formatDate(c.created_at))}</td>
                </tr>
            `;
        }).join('');
    }

    function renderRecentTickets(list) {
        if (!dom.recentTicketsBody) return;
        if (!list || !list.length) {
            dom.recentTicketsBody.innerHTML = '<tr><td colspan="4" style="text-align:center;color:#888;padding:24px;">Chưa có ticket nào.</td></tr>';
            return;
        }
        dom.recentTicketsBody.innerHTML = list.map((t) => {
            const statusClass = (t.status || '').toLowerCase();
            return `
                <tr>
                    <td><a href="tickets.html" style="font-weight:600;color:#0077b6;text-decoration:none;">${escapeHtml(t.ticket_code)}</a></td>
                    <td>${escapeHtml(t.title)}</td>
                    <td>${escapeHtml(t.customer_name || '--')}</td>
                    <td><span class="status ${escapeHtml(statusClass)}">${escapeHtml(ticketStatusLabel(t.status))}</span></td>
                </tr>
            `;
        }).join('');
    }

    function setWelcome(adminName) {
        const name = adminName || 'Admin';
        if (dom.welcomeText) dom.welcomeText.textContent = `Chào mừng trở lại, ${name}! 👋`;
        const now = new Date();
        if (dom.welcomeSub) dom.welcomeSub.textContent = `Tổng quan hệ thống NHUTIN • ${now.toLocaleDateString('vi-VN')}`;
    }

    async function loadDashboard() {
        try {
            const res = await fetch(apiUrl, { credentials: 'same-origin' });
            if (res.status === 401) {
                window.location.href = 'login.php';
                return;
            }
            const json = await res.json();
            if (!json.success || !json.data) return;

            const { stats, recent_customers, recent_tickets } = json.data;
            renderStats(stats);
            renderRecentCustomers(recent_customers);
            renderRecentTickets(recent_tickets);

            const admin = JSON.parse(localStorage.getItem('nhutin_admin') || '{}');
            setWelcome(admin.name);
        } catch (e) {
            renderStats({});
            if (dom.recentCustomersBody) dom.recentCustomersBody.innerHTML = '<tr><td colspan="4" style="text-align:center;color:#dc2626;padding:24px;">Không thể tải dữ liệu.</td></tr>';
            if (dom.recentTicketsBody) dom.recentTicketsBody.innerHTML = '<tr><td colspan="4" style="text-align:center;color:#dc2626;padding:24px;">Không thể tải dữ liệu.</td></tr>';
        }
    }

    loadDashboard();
})();

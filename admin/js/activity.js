(() => {
    const apiUrl = './api/activity.php';

    const dom = {
        body: document.getElementById('activityBody'),
        paginationInfo: document.getElementById('paginationInfo'),
        limitSelect: document.getElementById('limitSelect'),
        prevPageBtn: document.getElementById('prevPageBtn'),
        pageText: document.getElementById('pageText'),
        nextPageBtn: document.getElementById('nextPageBtn'),
        searchInput: document.getElementById('searchInput'),
        entityTypeFilter: document.getElementById('entityTypeFilter'),
    };

    let currentPage = 1;
    let total = 0;
    let totalPages = 1;
    let searchTimeout = null;

    function getEntityBadgeClass(type) {
        const t = (type || '').toLowerCase();
        if (t === 'ticket') return 'badge-ticket';
        if (t === 'post') return 'badge-post';
        if (t === 'customer') return 'badge-customer';
        return '';
    }

    function formatDateTime(str) {
        if (!str) return '-';
        try {
            const d = new Date(str);
            return d.toLocaleString('vi-VN', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
            });
        } catch {
            return str;
        }
    }

    function renderRow(item) {
        const badgeClass = getEntityBadgeClass(item.entity_type);
        const entityLabel = item.entity_type ? `${item.entity_type}${item.entity_id ? ' #' + item.entity_id : ''}` : '-';
        const details = (item.details || '').substring(0, 80);
        const roleLabel = item.user_role === 'admin' ? 'Admin' : 'Staff';

        return `
            <tr>
                <td>${formatDateTime(item.created_at)}</td>
                <td>${escapeHtml(item.user_name || '-')}</td>
                <td><span class="badge ${item.user_role === 'admin' ? 'badge-ticket' : 'badge-post'}">${escapeHtml(roleLabel)}</span></td>
                <td>${escapeHtml(item.action || '-')}</td>
                <td>${item.entity_type ? `<span class="badge ${badgeClass}">${escapeHtml(entityLabel)}</span>` : '-'}</td>
                <td>${escapeHtml(details) || '-'}</td>
            </tr>
        `;
    }

    function escapeHtml(str) {
        if (str == null) return '';
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    async function fetchActivity() {
        if (!dom.body) return;

        const limit = Math.max(1, parseInt(dom.limitSelect?.value || '10', 10)) || 10;
        const params = new URLSearchParams();
        params.set('page', String(currentPage));
        params.set('limit', String(limit));
        if (dom.searchInput?.value.trim()) params.set('search', dom.searchInput.value.trim());
        if (dom.entityTypeFilter?.value) params.set('entity_type', dom.entityTypeFilter.value);

        dom.body.innerHTML = '<tr><td colspan="6" style="text-align:center;padding:32px;color:#888;">Đang tải...</td></tr>';

        try {
            const res = await fetch(apiUrl + '?' + params.toString(), { credentials: 'same-origin' });
            const json = await res.json();

            if (res.status === 403 || res.status === 401) {
                window.location.href = 'login.php';
                return;
            }

            if (!res.ok || !json.success) {
                dom.body.innerHTML = `<tr><td colspan="6" style="text-align:center;padding:32px;color:#DC2626;">${escapeHtml(json.message || 'Không thể tải dữ liệu.')}</td></tr>`;
                updatePaginationUI(0, 0, 1);
                return;
            }

            const items = json.data || [];
            const p = json.pagination || {};
            total = Number(p.total) || 0;
            totalPages = Math.max(1, Number(p.total_pages) || 1);

            if (items.length === 0) {
                dom.body.innerHTML = '<tr><td colspan="6" style="text-align:center;padding:32px;color:#888;">Chưa có nhật ký nào.</td></tr>';
                updatePaginationUI(0, 0, totalPages);
            } else {
                dom.body.innerHTML = items.map(renderRow).join('');
                const from = (currentPage - 1) * limit + 1;
                const to = (currentPage - 1) * limit + items.length;
                updatePaginationUI(from, to, totalPages);
            }
        } catch (e) {
            dom.body.innerHTML = `<tr><td colspan="6" style="text-align:center;padding:32px;color:#DC2626;">Lỗi kết nối: ${escapeHtml(e.message || 'Unknown')}</td></tr>`;
            updatePaginationUI(0, 0, 1);
        }
    }

    function updatePaginationUI(from, to, totalPagesVal) {
        totalPages = totalPagesVal;
        if (dom.paginationInfo) {
            if (total === 0) {
                dom.paginationInfo.textContent = 'Không có bản ghi nào.';
            } else {
                dom.paginationInfo.textContent = `Hiển thị ${from} - ${to} của ${total} bản ghi`;
            }
        }
        if (dom.pageText) dom.pageText.textContent = `Trang ${currentPage}/${totalPages}`;
        if (dom.prevPageBtn) dom.prevPageBtn.disabled = currentPage <= 1;
        if (dom.nextPageBtn) dom.nextPageBtn.disabled = currentPage >= totalPages;
    }

    function init() {
        fetchActivity();

        if (dom.prevPageBtn) {
            dom.prevPageBtn.addEventListener('click', () => {
                if (currentPage > 1) {
                    currentPage--;
                    fetchActivity();
                }
            });
        }
        if (dom.nextPageBtn) {
            dom.nextPageBtn.addEventListener('click', () => {
                if (currentPage < totalPages) {
                    currentPage++;
                    fetchActivity();
                }
            });
        }
        if (dom.limitSelect) {
            dom.limitSelect.addEventListener('change', () => {
                currentPage = 1;
                fetchActivity();
            });
        }

        if (dom.searchInput) {
            dom.searchInput.addEventListener('input', () => {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    currentPage = 1;
                    fetchActivity();
                }, 300);
            });
        }

        if (dom.entityTypeFilter) {
            dom.entityTypeFilter.addEventListener('change', () => {
                currentPage = 1;
                fetchActivity();
            });
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();

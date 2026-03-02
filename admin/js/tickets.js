(() => {
    const apiUrl = './api/tickets.php';

    const state = {
        tickets: [],
        customers: [],
        page: 1,
        limit: 10,
        total: 0,
        totalPages: 1,
        search: '',
        status: '',
        priority: '',
        stats: { open: 0, progress: 0, resolved: 0 },
        editingId: null,
        deletingId: null,
        loading: false,
    };

    const dom = {
        ticketsList: document.getElementById('ticketsList'),
        ticketsSubtitle: document.getElementById('ticketsSubtitle'),
        statOpen: document.getElementById('statOpen'),
        statProgress: document.getElementById('statProgress'),
        statResolved: document.getElementById('statResolved'),
        addTicketBtn: document.getElementById('addTicketBtn'),
        ticketSearch: document.getElementById('ticketSearch'),
        statusFilter: document.getElementById('statusFilter'),
        priorityFilter: document.getElementById('priorityFilter'),
        paginationInfo: document.getElementById('paginationInfo'),
        limitSelect: document.getElementById('limitSelect'),
        prevPageBtn: document.getElementById('prevPageBtn'),
        nextPageBtn: document.getElementById('nextPageBtn'),
        pageInfo: document.getElementById('pageInfo'),
        ticketFormModal: document.getElementById('ticketFormModal'),
        ticketModalTitle: document.getElementById('ticketModalTitle'),
        ticketForm: document.getElementById('ticketForm'),
        ticketFormError: document.getElementById('ticketFormError'),
        ticketId: document.getElementById('ticketId'),
        ticketTitle: document.getElementById('ticketTitle'),
        ticketDescription: document.getElementById('ticketDescription'),
        ticketCustomer: document.getElementById('ticketCustomer'),
        ticketStatus: document.getElementById('ticketStatus'),
        ticketPriority: document.getElementById('ticketPriority'),
        closeTicketModalBtn: document.getElementById('closeTicketModalBtn'),
        cancelTicketBtn: document.getElementById('cancelTicketBtn'),
        deleteTicketModal: document.getElementById('deleteTicketModal'),
        deleteTicketText: document.getElementById('deleteTicketText'),
        cancelDeleteTicketBtn: document.getElementById('cancelDeleteTicketBtn'),
        confirmDeleteTicketBtn: document.getElementById('confirmDeleteTicketBtn'),
        ticketToast: document.getElementById('ticketToast'),
    };

    function escapeHtml(v) {
        return String(v ?? '').replaceAll('&', '&amp;').replaceAll('<', '&lt;').replaceAll('>', '&gt;').replaceAll('"', '&quot;').replaceAll("'", '&#039;');
    }

    function formatRelativeTime(dateStr) {
        if (!dateStr) return '--';
        const d = new Date(dateStr.replace(' ', 'T'));
        if (Number.isNaN(d.getTime())) return '--';
        const now = new Date();
        const diffMs = now - d;
        const mins = Math.floor(diffMs / 60000);
        const hours = Math.floor(diffMs / 3600000);
        const days = Math.floor(diffMs / 86400000);
        if (mins < 60) return `${mins} phút trước`;
        if (hours < 24) return `${hours} giờ trước`;
        if (days < 7) return `${days} ngày trước`;
        return d.toLocaleDateString('vi-VN');
    }

    function statusLabel(s) {
        if (s === 'open') return 'Đang mở';
        if (s === 'progress') return 'Đang xử lý';
        return 'Đã giải quyết';
    }

    function priorityLabel(p) {
        if (p === 'high') return 'Ưu tiên cao';
        if (p === 'medium') return 'Trung bình';
        return 'Thấp';
    }

    function showToast(msg, isError = false) {
        if (!dom.ticketToast) return;
        dom.ticketToast.textContent = msg || '';
        dom.ticketToast.classList.toggle('error', !!isError);
        dom.ticketToast.classList.add('show');
        setTimeout(() => dom.ticketToast.classList.remove('show'), 2500);
    }

    function setFormError(msg) {
        if (!dom.ticketFormError) return;
        dom.ticketFormError.textContent = msg || '';
        dom.ticketFormError.classList.toggle('show', !!msg);
    }

    function updateSubtitle() {
        if (dom.ticketsSubtitle) dom.ticketsSubtitle.textContent = `Tổng số: ${state.total} tickets`;
    }

    function updateStats() {
        if (dom.statOpen) dom.statOpen.textContent = state.stats.open;
        if (dom.statProgress) dom.statProgress.textContent = state.stats.progress;
        if (dom.statResolved) dom.statResolved.textContent = state.stats.resolved;
    }

    function updatePagination() {
        const from = state.total === 0 ? 0 : (state.page - 1) * state.limit + 1;
        const to = state.total === 0 ? 0 : Math.min(state.page * state.limit, state.total);
        if (dom.paginationInfo) {
            dom.paginationInfo.textContent = state.total === 0
                ? 'Không có bản ghi nào.' : `Hiển thị ${from} - ${to} của ${state.total} bản ghi`;
        }
        if (dom.pageInfo) dom.pageInfo.textContent = `Trang ${state.page}/${Math.max(state.totalPages, 1)}`;
        if (dom.prevPageBtn) dom.prevPageBtn.disabled = state.page <= 1;
        if (dom.nextPageBtn) dom.nextPageBtn.disabled = state.page >= state.totalPages;
    }

    function renderCustomerSelect() {
        const cur = dom.ticketCustomer.value;
        const opts = ['<option value="">-- Chọn khách hàng --</option>'];
        state.customers.forEach((c) => {
            const sel = cur === String(c.id) ? ' selected' : '';
            opts.push(`<option value="${c.id}"${sel}>${escapeHtml(c.name)}</option>`);
        });
        dom.ticketCustomer.innerHTML = opts.join('');
    }

    function renderTickets() {
        if (state.loading) {
            dom.ticketsList.innerHTML = '<div class="empty-card">Đang tải...</div>';
            return;
        }
        if (!state.tickets.length) {
            dom.ticketsList.innerHTML = '<div class="empty-card">Không có ticket nào phù hợp.</div>';
            return;
        }
        dom.ticketsList.innerHTML = state.tickets.map((t) => {
            const statusClass = `status-${t.status}`;
            const priorityClass = `priority-${t.priority}`;
            return `
                <div class="ticket-card">
                    <div class="ticket-header">
                        <div class="ticket-info">
                            <div class="ticket-id">${escapeHtml(t.ticket_code)}</div>
                            <div class="ticket-title">${escapeHtml(t.title)}</div>
                            <div class="ticket-meta">
                                <div class="ticket-meta-item">
                                    <svg viewBox="0 0 24 24"><path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                    ${escapeHtml(t.customer_name || '--')}
                                </div>
                                <div class="ticket-meta-item">
                                    <svg viewBox="0 0 24 24"><path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    ${formatRelativeTime(t.created_at)}
                                </div>
                            </div>
                        </div>
                        <span class="ticket-status ${statusClass}">${statusLabel(t.status)}</span>
                    </div>
                    <div class="ticket-body"><div class="ticket-desc">${escapeHtml(t.description || '--')}</div></div>
                    <div class="ticket-footer">
                        <span class="ticket-priority ${priorityClass}">${priorityLabel(t.priority)}</span>
                        <div class="ticket-actions">
                            <button class="ticket-btn" data-action="edit" data-id="${t.id}" type="button">Sửa</button>
                            <button class="ticket-btn delete" data-action="delete" data-id="${t.id}" type="button">Xóa</button>
                        </div>
                    </div>
                </div>
            `;
        }).join('');
    }

    function buildQuery() {
        const p = new URLSearchParams();
        p.set('page', String(state.page));
        p.set('limit', String(state.limit));
        if (state.search) p.set('search', state.search);
        if (state.status) p.set('status', state.status);
        if (state.priority) p.set('priority', state.priority);
        return p.toString();
    }

    async function requestJson(url, options = {}) {
        const res = await fetch(url, { credentials: 'same-origin', ...options });
        const text = await res.text();
        let json;
        try { json = text ? JSON.parse(text) : {}; } catch {
            const preview = text.length > 120 ? text.slice(0, 120) + '…' : text;
            throw new Error('Phản hồi không hợp lệ. (' + preview.replace(/\s+/g, ' ').trim() + ')');
        }
        if (!res.ok || !json.success) throw new Error(json.message || 'Có lỗi xảy ra.');
        return json;
    }

    async function loadMeta() {
        try {
            const j = await requestJson(`${apiUrl}?action=meta`);
            state.customers = Array.isArray(j.customers) ? j.customers : [];
            renderCustomerSelect();
        } catch (e) {
            state.customers = [];
        }
    }

    async function loadStats() {
        try {
            const j = await requestJson(`${apiUrl}?action=stats`);
            state.stats = j.stats || { open: 0, progress: 0, resolved: 0 };
            updateStats();
        } catch (e) {}
    }

    async function loadTickets() {
        state.loading = true;
        renderTickets();
        try {
            const j = await requestJson(`${apiUrl}?${buildQuery()}`);
            state.tickets = Array.isArray(j.data) ? j.data : [];
            state.total = Number(j.pagination?.total ?? 0);
            state.totalPages = Math.max(1, Number(j.pagination?.total_pages ?? 1));
            await loadStats();
        } catch (e) {
            state.tickets = [];
            showToast(e.message || 'Không thể tải tickets.', true);
        } finally {
            state.loading = false;
            renderTickets();
            updatePagination();
            updateSubtitle();
        }
    }

    function openFormModal(ticket = null) {
        state.editingId = ticket ? ticket.id : null;
        setFormError('');
        const isEdit = !!ticket;
        dom.ticketModalTitle.textContent = isEdit ? 'Chỉnh sửa ticket' : 'Thêm ticket';
        dom.ticketId.value = ticket ? String(ticket.id) : '';
        dom.ticketTitle.value = ticket?.title || '';
        dom.ticketDescription.value = ticket?.description || '';
        dom.ticketCustomer.value = ticket?.customer_id ? String(ticket.customer_id) : '';
        dom.ticketStatus.value = ticket?.status || 'open';
        dom.ticketPriority.value = ticket?.priority || 'medium';
        renderCustomerSelect();
        dom.ticketCustomer.value = ticket?.customer_id ? String(ticket.customer_id) : '';

        // Không cho phép sửa tiêu đề, mô tả, khách hàng khi đang chỉnh sửa ticket đã gửi.
        dom.ticketTitle.readOnly = isEdit;
        dom.ticketDescription.readOnly = isEdit;
        dom.ticketCustomer.disabled = isEdit;

        dom.ticketFormModal.classList.add('show');
    }

    function closeFormModal() {
        dom.ticketFormModal.classList.remove('show');
        state.editingId = null;
    }

    function openDeleteModal(ticket) {
        state.deletingId = ticket.id;
        dom.deleteTicketText.textContent = `Bạn có chắc muốn xóa ticket "${ticket.title}"?`;
        dom.deleteTicketModal.classList.add('show');
    }

    function closeDeleteModal() {
        state.deletingId = null;
        dom.deleteTicketModal.classList.remove('show');
    }

    function collectPayload() {
        return {
            id: Number(dom.ticketId.value || 0),
            title: dom.ticketTitle.value.trim(),
            description: dom.ticketDescription.value.trim(),
            customer_id: dom.ticketCustomer.value || null,
            status: dom.ticketStatus.value,
            priority: dom.ticketPriority.value,
        };
    }

    async function submitForm(e) {
        e.preventDefault();
        setFormError('');
        const p = collectPayload();
        if (!p.title) {
            setFormError('Vui lòng nhập tiêu đề.');
            return;
        }
        const isEdit = p.id > 0;
        try {
            await requestJson(apiUrl, {
                method: isEdit ? 'PUT' : 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(p),
            });
            showToast(isEdit ? 'Cập nhật thành công.' : 'Tạo ticket thành công.');
            closeFormModal();
            await loadTickets();
        } catch (e) {
            setFormError(e.message || 'Không thể lưu.');
        }
    }

    async function confirmDelete() {
        if (!state.deletingId) return;
        try {
            await requestJson(apiUrl, {
                method: 'DELETE',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: state.deletingId }),
            });
            showToast('Đã xóa ticket.');
            closeDeleteModal();
            if (state.page > 1 && state.tickets.length === 1) state.page -= 1;
            await loadTickets();
        } catch (e) {
            showToast(e.message || 'Không thể xóa.', true);
        }
    }

    function handleClick(e) {
        const btn = e.target.closest('[data-action]');
        if (!btn) return;
        const id = Number(btn.dataset.id || 0);
        const ticket = state.tickets.find((t) => t.id === id);
        if (!ticket) return;
        if (btn.dataset.action === 'edit') openFormModal(ticket);
        else if (btn.dataset.action === 'delete') openDeleteModal(ticket);
    }

    function debounce(fn, ms) {
        let t = 0;
        return (...args) => {
            clearTimeout(t);
            t = setTimeout(() => fn(...args), ms);
        };
    }

    function init() {
        dom.addTicketBtn.addEventListener('click', () => openFormModal(null));
        dom.closeTicketModalBtn.addEventListener('click', closeFormModal);
        dom.cancelTicketBtn.addEventListener('click', closeFormModal);
        dom.ticketFormModal.addEventListener('click', (e) => { if (e.target === dom.ticketFormModal) closeFormModal(); });
        dom.ticketForm.addEventListener('submit', submitForm);
        dom.ticketsList.addEventListener('click', handleClick);
        dom.cancelDeleteTicketBtn.addEventListener('click', closeDeleteModal);
        dom.deleteTicketModal.addEventListener('click', (e) => { if (e.target === dom.deleteTicketModal) closeDeleteModal(); });
        dom.confirmDeleteTicketBtn.addEventListener('click', confirmDelete);

        dom.ticketSearch.addEventListener('input', debounce(async () => {
            state.search = dom.ticketSearch.value.trim();
            state.page = 1;
            await loadTickets();
        }, 400));

        dom.statusFilter.addEventListener('change', async () => {
            state.status = dom.statusFilter.value || '';
            state.page = 1;
            await loadTickets();
        });

        dom.priorityFilter.addEventListener('change', async () => {
            state.priority = dom.priorityFilter.value || '';
            state.page = 1;
            await loadTickets();
        });

        if (dom.limitSelect) {
            state.limit = Math.max(1, parseInt(dom.limitSelect.value, 10)) || 10;
            dom.limitSelect.addEventListener('change', async () => {
                state.limit = Math.max(1, parseInt(dom.limitSelect.value, 10)) || 10;
                state.page = 1;
                await loadTickets();
            });
        }

        dom.prevPageBtn.addEventListener('click', async () => {
            if (state.page <= 1) return;
            state.page -= 1;
            await loadTickets();
        });

        dom.nextPageBtn.addEventListener('click', async () => {
            if (state.page >= state.totalPages) return;
            state.page += 1;
            await loadTickets();
        });
    }

    (async () => {
        init();
        await loadMeta();
        await loadTickets();
    })();
})();

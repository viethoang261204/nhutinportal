const apiUrl = './api/customers.php';
const defaultAvatar = '../img/default.png';

const state = {
    customers: [],
    search: '',
    status: '',
    type: '',
    page: 1,
    limit: 10,
    total: 0,
    totalPages: 1,
    editingId: null,
    deletingId: null,
};

const dom = {
    subtitle: document.getElementById('customersSubtitle'),
    searchInput: document.getElementById('customerSearch'),
    statusFilter: document.getElementById('statusFilter'),
    typeFilter: document.getElementById('typeFilter'),
    tableBody: document.getElementById('customersTableBody'),
    addButton: document.getElementById('addCustomerBtn'),
    paginationInfo: document.getElementById('paginationInfo'),
    limitSelect: document.getElementById('limitSelect'),
    prevPageButton: document.getElementById('prevPageBtn'),
    nextPageButton: document.getElementById('nextPageBtn'),
    pageInfo: document.getElementById('pageInfo'),
    formModal: document.getElementById('customerFormModal'),
    formTitle: document.getElementById('customerModalTitle'),
    customerForm: document.getElementById('customerForm'),
    customerId: document.getElementById('customerId'),
    customerName: document.getElementById('customerName'),
    customerEmail: document.getElementById('customerEmail'),
    customerPhone: document.getElementById('customerPhone'),
    customerType: document.getElementById('customerType'),
    customerStatus: document.getElementById('customerStatus'),
    customerAvatar: document.getElementById('customerAvatar'),
    customerAvatarFile: document.getElementById('customerAvatarFile'),
    customerAvatarPreview: document.getElementById('customerAvatarPreview'),
    chooseAvatarBtn: document.getElementById('chooseAvatarBtn'),
    clearAvatarBtn: document.getElementById('clearAvatarBtn'),
    customerAddress: document.getElementById('customerAddress'),
    customerNote: document.getElementById('customerNote'),
    formError: document.getElementById('customerFormError'),
    deleteModal: document.getElementById('deleteCustomerModal'),
    deleteText: document.getElementById('deleteCustomerText'),
    toast: document.getElementById('customerToast'),
};

function escapeHtml(value) {
    return String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

function formatDate(value) {
    if (!value) return '--';
    const date = new Date(value);
    if (Number.isNaN(date.getTime())) return '--';
    return date.toLocaleDateString('vi-VN');
}

function mapStatusLabel(status) {
    if (status === 'active') return 'Đang hoạt động';
    if (status === 'pending') return 'Chờ duyệt';
    return 'Ngưng hoạt động';
}

function mapTypeLabel(type) {
    return type === 'individual' ? 'Cá nhân' : 'Doanh nghiệp';
}

function setAvatarPreview(url) {
    if (!dom.customerAvatarPreview) return;
    dom.customerAvatarPreview.src = url || defaultAvatar;
}

function showToast(message, type = 'success') {
    if (!dom.toast) return;
    dom.toast.textContent = message;
    dom.toast.className = `toast show ${type}`;
    window.setTimeout(() => {
        dom.toast.className = 'toast';
    }, 3000);
}

function setFormError(message = '') {
    if (!dom.formError) return;
    dom.formError.textContent = message;
    dom.formError.classList.toggle('show', message !== '');
}

function updatePagination() {
    const from = state.total === 0 ? 0 : (state.page - 1) * state.limit + 1;
    const to = state.total === 0 ? 0 : Math.min(state.page * state.limit, state.total);
    if (dom.paginationInfo) {
        dom.paginationInfo.textContent = state.total === 0
            ? 'Không có bản ghi nào.' : `Hiển thị ${from} - ${to} của ${state.total} bản ghi`;
    }
    if (dom.pageInfo) dom.pageInfo.textContent = `Trang ${state.page}/${state.totalPages || 1}`;
    if (dom.prevPageButton) dom.prevPageButton.disabled = state.page <= 1;
    if (dom.nextPageButton) dom.nextPageButton.disabled = state.page >= state.totalPages;
}

function updateSubtitle() {
    if (!dom.subtitle) return;
    dom.subtitle.textContent = `Tổng số: ${state.total} khách hàng`;
}

function renderRows() {
    if (!dom.tableBody) return;

    if (state.customers.length === 0) {
        dom.tableBody.innerHTML = `
            <tr>
                <td colspan="6" class="empty-cell">Không có dữ liệu khách hàng phù hợp.</td>
            </tr>
        `;
        return;
    }

    dom.tableBody.innerHTML = state.customers
        .map((customer) => {
            const safeName = escapeHtml(customer.name);
            const safeEmail = escapeHtml(customer.email);
            const safePhone = escapeHtml(customer.phone);
            const safeCode = escapeHtml(customer.customer_code);
            const safeAvatar = escapeHtml(customer.avatar_url);
            return `
                <tr>
                    <td>
                        <div class="customer-cell">
                            <img src="${safeAvatar}" class="customer-avatar" alt="${safeName}">
                            <div class="customer-info">
                                <div class="customer-name">${safeName}</div>
                                <div class="customer-id">${safeCode} • ${mapTypeLabel(customer.customer_type)}</div>
                            </div>
                        </div>
                    </td>
                    <td>${safeEmail}</td>
                    <td>${safePhone}</td>
                    <td><span class="status ${escapeHtml(customer.status)}">${mapStatusLabel(customer.status)}</span></td>
                    <td>${formatDate(customer.created_at)}</td>
                    <td>
                        <div class="actions">
                            <button class="action-btn action-edit" type="button" data-id="${customer.id}" title="Chỉnh sửa">
                                <svg viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </button>
                            <button class="action-btn action-delete" type="button" data-id="${customer.id}" data-name="${safeName}" title="Xóa">
                                <svg viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </div>
                    </td>
                </tr>
            `;
        })
        .join('');
}

function buildQuery() {
    const params = new URLSearchParams();
    if (state.search) params.set('search', state.search);
    if (state.status) params.set('status', state.status);
    if (state.type) params.set('type', state.type);
    params.set('page', String(state.page));
    params.set('limit', String(state.limit));
    return params.toString();
}

async function requestJson(url, options = {}) {
    const response = await fetch(url, {
        credentials: 'same-origin',
        ...options,
    });
    let payload = null;
    try {
        payload = await response.json();
    } catch (error) {
        const fallbackMessage = response.ok
            ? 'Phản hồi từ server không hợp lệ.'
            : `Server trả lỗi ${response.status}.`;
        throw new Error(fallbackMessage);
    }

    if (response.status === 401) {
        localStorage.removeItem('nhutin_admin');
        localStorage.removeItem('nhutin_admin_logged_in');
        window.location.href = 'login.php';
        throw new Error('Phiên đăng nhập đã hết hạn.');
    }

    if (!response.ok || !payload.success) {
        throw new Error(payload.message || 'Có lỗi xảy ra.');
    }

    return payload;
}

async function loadCustomers() {
    dom.tableBody.innerHTML = `
        <tr>
            <td colspan="6" class="empty-cell">Đang tải dữ liệu...</td>
        </tr>
    `;

    try {
        const data = await requestJson(`${apiUrl}?${buildQuery()}`);
        state.customers = Array.isArray(data.data) ? data.data : [];
        state.total = Number(data.pagination?.total || 0);
        state.totalPages = Math.max(1, Number(data.pagination?.total_pages || 1));
    } catch (error) {
        state.customers = [];
        state.total = 0;
        state.totalPages = 1;
        showToast(error.message, 'error');
    }

    renderRows();
    updateSubtitle();
    updatePagination();
}

function openFormModal(mode, customer = null) {
    state.editingId = customer?.id ?? null;
    dom.formTitle.textContent = mode === 'edit' ? 'Cập nhật khách hàng' : 'Thêm khách hàng';
    setFormError('');

    dom.customerId.value = customer?.id ?? '';
    dom.customerName.value = customer?.name ?? '';
    dom.customerEmail.value = customer?.email ?? '';
    dom.customerPhone.value = customer?.phone ?? '';
    dom.customerType.value = customer?.customer_type ?? 'business';
    dom.customerStatus.value = customer?.status ?? 'active';
    dom.customerAvatar.value = customer?.avatar_url === defaultAvatar ? '' : (customer?.avatar_url ?? '');
    setAvatarPreview(customer?.avatar_url || defaultAvatar);
    if (dom.customerAvatarFile) dom.customerAvatarFile.value = '';
    dom.customerAddress.value = customer?.address ?? '';
    dom.customerNote.value = customer?.note ?? '';

    dom.formModal.classList.add('show');
    document.body.style.overflow = 'hidden';
}

function closeFormModal() {
    dom.formModal.classList.remove('show');
    document.body.style.overflow = '';
}

function openDeleteModal(customerId, customerName) {
    state.deletingId = customerId;
    dom.deleteText.textContent = `Bạn có chắc chắn muốn xóa khách hàng "${customerName}" không?`;
    dom.deleteModal.classList.add('show');
    document.body.style.overflow = 'hidden';
}

function closeDeleteModal() {
    dom.deleteModal.classList.remove('show');
    document.body.style.overflow = '';
}

function collectFormPayload() {
    return {
        id: Number(dom.customerId.value || 0),
        name: dom.customerName.value.trim(),
        email: dom.customerEmail.value.trim(),
        phone: dom.customerPhone.value.trim(),
        customer_type: dom.customerType.value,
        status: dom.customerStatus.value,
        avatar_url: dom.customerAvatar.value.trim(),
        address: dom.customerAddress.value.trim(),
        note: dom.customerNote.value.trim(),
    };
}

async function uploadAvatarFile(file) {
    const formData = new FormData();
    formData.append('avatar', file);
    const response = await fetch(`${apiUrl}?action=upload_avatar`, {
        method: 'POST',
        credentials: 'same-origin',
        body: formData,
    });

    let payload = null;
    try {
        payload = await response.json();
    } catch (error) {
        throw new Error('Upload ảnh thất bại, phản hồi không hợp lệ.');
    }

    if (!response.ok || !payload.success) {
        throw new Error(payload.message || 'Không thể tải ảnh lên.');
    }

    return String(payload.avatar_url || '');
}

async function submitForm(event) {
    event.preventDefault();
    setFormError('');

    const payload = collectFormPayload();
    const isEdit = payload.id > 0;

    if (!payload.name || !payload.email || !payload.phone) {
        setFormError('Vui lòng điền đầy đủ tên, email và số điện thoại.');
        return;
    }

    try {
        const res = await requestJson(apiUrl, {
            method: isEdit ? 'PUT' : 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload),
        });

        closeFormModal();
        showToast(res.message || (isEdit ? 'Cập nhật khách hàng thành công.' : 'Tạo khách hàng thành công.'));
        await loadCustomers();
    } catch (error) {
        setFormError(error.message);
    }
}

async function confirmDeleteCustomer() {
    if (!state.deletingId) return;
    try {
        await requestJson(apiUrl, {
            method: 'DELETE',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: state.deletingId }),
        });
        closeDeleteModal();
        state.deletingId = null;
        showToast('Đã xóa khách hàng.');
        await loadCustomers();
    } catch (error) {
        showToast(error.message, 'error');
    }
}

async function handleTableClick(event) {
    const editButton = event.target.closest('.action-edit');
    const deleteButton = event.target.closest('.action-delete');

    if (editButton) {
        const customerId = Number(editButton.dataset.id || 0);
        const customer = state.customers.find((item) => item.id === customerId);
        if (customer) {
            openFormModal('edit', customer);
        }
        return;
    }

    if (deleteButton) {
        const customerId = Number(deleteButton.dataset.id || 0);
        const customerName = deleteButton.dataset.name || 'khách hàng này';
        openDeleteModal(customerId, customerName);
    }
}

function debounce(fn, delay) {
    let timeoutId = null;
    return (...args) => {
        window.clearTimeout(timeoutId);
        timeoutId = window.setTimeout(() => fn(...args), delay);
    };
}

function initEvents() {
    dom.addButton.addEventListener('click', () => openFormModal('create'));
    dom.customerForm.addEventListener('submit', submitForm);
    dom.tableBody.addEventListener('click', handleTableClick);
    dom.chooseAvatarBtn.addEventListener('click', () => dom.customerAvatarFile.click());
    dom.clearAvatarBtn.addEventListener('click', () => {
        dom.customerAvatar.value = '';
        if (dom.customerAvatarFile) dom.customerAvatarFile.value = '';
        setAvatarPreview(defaultAvatar);
    });
    dom.customerAvatarFile.addEventListener('change', async () => {
        const file = dom.customerAvatarFile.files && dom.customerAvatarFile.files[0];
        if (!file) return;
        setFormError('');
        try {
            const avatarUrl = await uploadAvatarFile(file);
            dom.customerAvatar.value = avatarUrl;
            setAvatarPreview(avatarUrl);
            showToast('Tải ảnh đại diện thành công.');
        } catch (error) {
            dom.customerAvatar.value = '';
            setAvatarPreview(defaultAvatar);
            setFormError(error.message);
        }
    });

    dom.searchInput.addEventListener(
        'input',
        debounce(() => {
            state.search = dom.searchInput.value.trim();
            state.page = 1;
            loadCustomers();
        }, 300)
    );

    dom.statusFilter.addEventListener('change', () => {
        state.status = dom.statusFilter.value;
        state.page = 1;
        loadCustomers();
    });

    dom.typeFilter.addEventListener('change', () => {
        state.type = dom.typeFilter.value;
        state.page = 1;
        loadCustomers();
    });

    if (dom.limitSelect) {
state.limit = Math.max(1, parseInt(dom.limitSelect.value, 10)) || 10;
            dom.limitSelect.addEventListener('change', () => {
                state.limit = Math.max(1, parseInt(dom.limitSelect.value, 10)) || 10;
            state.page = 1;
            loadCustomers();
        });
    }

    dom.prevPageButton.addEventListener('click', () => {
        if (state.page <= 1) return;
        state.page -= 1;
        loadCustomers();
    });

    dom.nextPageButton.addEventListener('click', () => {
        if (state.page >= state.totalPages) return;
        state.page += 1;
        loadCustomers();
    });

    document.getElementById('closeCustomerModalBtn').addEventListener('click', closeFormModal);
    document.getElementById('cancelCustomerBtn').addEventListener('click', closeFormModal);
    document.getElementById('cancelDeleteCustomerBtn').addEventListener('click', closeDeleteModal);
    document.getElementById('confirmDeleteCustomerBtn').addEventListener('click', confirmDeleteCustomer);

    dom.formModal.addEventListener('click', (event) => {
        if (event.target === dom.formModal) closeFormModal();
    });

    dom.deleteModal.addEventListener('click', (event) => {
        if (event.target === dom.deleteModal) closeDeleteModal();
    });
}

initEvents();
loadCustomers();

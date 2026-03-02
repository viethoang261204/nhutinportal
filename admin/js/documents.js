const apiUrl = './api/documents.php';

const state = {
    documents: [],
    documentTypes: [],
    customers: [],
    search: '',
    status: '',
    documentTypeId: '',
    page: 1,
    limit: 10,
    total: 0,
    totalPages: 1,
    deletingId: null,
    selectedCustomerIds: [],
    pickerCustomerIds: [],
};

const dom = {
    subtitle: document.getElementById('documentsSubtitle'),
    searchInput: document.getElementById('documentSearch'),
    statusFilter: document.getElementById('statusFilter'),
    typeFilter: document.getElementById('documentTypeFilter'),
    documentsGrid: document.getElementById('documentsGrid'),
    addButton: document.getElementById('addDocumentBtn'),
    paginationInfo: document.getElementById('paginationInfo'),
    limitSelect: document.getElementById('limitSelect'),
    prevPageButton: document.getElementById('prevPageBtn'),
    nextPageButton: document.getElementById('nextPageBtn'),
    pageInfo: document.getElementById('pageInfo'),
    formModal: document.getElementById('documentFormModal'),
    formTitle: document.getElementById('documentModalTitle'),
    formError: document.getElementById('documentFormError'),
    documentForm: document.getElementById('documentForm'),
    documentId: document.getElementById('documentId'),
    documentTitle: document.getElementById('documentTitle'),
    documentType: document.getElementById('documentType'),
    selectedCustomersDisplay: document.getElementById('selectedCustomersDisplay'),
    openCustomerPickerBtn: document.getElementById('openCustomerPickerBtn'),
    addCustomerQuickBtn: document.getElementById('addCustomerQuickBtn'),
    documentStatus: document.getElementById('documentStatus'),
    documentFilePath: document.getElementById('documentFilePath'),
    documentFileName: document.getElementById('documentFileName'),
    documentFileSize: document.getElementById('documentFileSize'),
    documentMimeType: document.getElementById('documentMimeType'),
    uploadFileInput: document.getElementById('uploadFileInput'),
    uploadFileButton: document.getElementById('uploadFileButton'),
    uploadFileLabel: document.getElementById('uploadFileLabel'),
    deleteModal: document.getElementById('deleteDocumentModal'),
    deleteText: document.getElementById('deleteDocumentText'),
    toast: document.getElementById('documentToast'),
    customerPickerModal: document.getElementById('customerPickerModal'),
    closeCustomerPickerBtn: document.getElementById('closeCustomerPickerBtn'),
    cancelCustomerPickerBtn: document.getElementById('cancelCustomerPickerBtn'),
    applyCustomerPickerBtn: document.getElementById('applyCustomerPickerBtn'),
    customerPickerSearch: document.getElementById('customerPickerSearch'),
    customerPickerBody: document.getElementById('customerPickerBody'),
    customerSelectAll: document.getElementById('customerSelectAll'),
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

function formatFileSize(bytes) {
    const size = Number(bytes || 0);
    if (!Number.isFinite(size) || size <= 0) return '--';
    if (size < 1024) return `${size} B`;
    if (size < 1024 * 1024) return `${(size / 1024).toFixed(1)} KB`;
    return `${(size / (1024 * 1024)).toFixed(1)} MB`;
}

function mapStatusLabel(status) {
    if (status === 'published') return 'Published';
    if (status === 'archived') return 'Archived';
    return 'Draft';
}

function showToast(message, type = 'success') {
    dom.toast.textContent = message;
    dom.toast.className = `toast show ${type}`;
    window.setTimeout(() => {
        dom.toast.className = 'toast';
    }, 3000);
}

function setFormError(message = '') {
    dom.formError.textContent = message;
    dom.formError.classList.toggle('show', message !== '');
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
        throw new Error(response.ok ? 'Phản hồi từ server không hợp lệ.' : `Server trả lỗi ${response.status}.`);
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

async function uploadDocumentFile(file) {
    const formData = new FormData();
    formData.append('file', file);
    const response = await fetch(`${apiUrl}?action=upload_file`, {
        method: 'POST',
        credentials: 'same-origin',
        body: formData,
    });

    let payload = null;
    try {
        payload = await response.json();
    } catch (error) {
        throw new Error('Upload file thất bại, phản hồi không hợp lệ.');
    }

    if (!response.ok || !payload.success) {
        throw new Error(payload.message || 'Không thể upload tài liệu.');
    }

    return payload.file || {};
}

function buildQuery() {
    const params = new URLSearchParams();
    if (state.search) params.set('search', state.search);
    if (state.status) params.set('status', state.status);
    if (state.documentTypeId) params.set('document_type_id', state.documentTypeId);
    params.set('page', String(state.page));
    params.set('limit', String(state.limit));
    return params.toString();
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
    dom.subtitle.textContent = `Tổng số: ${state.total} tài liệu`;
}

function renderSelectedCustomersDisplay() {
    const names = state.customers
        .filter((item) => state.selectedCustomerIds.includes(Number(item.id)))
        .map((item) => item.company_name);
    dom.selectedCustomersDisplay.value = names.length > 0 ? names.join(', ') : '';
    dom.selectedCustomersDisplay.placeholder = names.length > 0 ? '' : 'Chưa chọn khách hàng';
}

function getFilteredCustomersByPickerKeyword() {
    const keyword = String(dom.customerPickerSearch.value || '').trim().toLowerCase();
    return state.customers.filter((item) => {
        if (keyword === '') return true;
        const text = `${item.customer_code} ${item.company_name}`.toLowerCase();
        return text.includes(keyword);
    });
}

function renderCustomerPickerRows() {
    const filtered = getFilteredCustomersByPickerKeyword();
    if (filtered.length === 0) {
        dom.customerPickerBody.innerHTML = '<tr><td colspan="3">Không tìm thấy khách hàng phù hợp.</td></tr>';
        dom.customerSelectAll.checked = false;
        return;
    }

    dom.customerPickerBody.innerHTML = filtered
        .map((item) => {
            const id = Number(item.id);
            const checked = state.pickerCustomerIds.includes(id) ? 'checked' : '';
            return `
                <tr>
                    <td><input type="checkbox" class="picker-customer-item" data-id="${id}" ${checked}></td>
                    <td>${escapeHtml(item.customer_code)}</td>
                    <td>${escapeHtml(item.company_name)}</td>
                </tr>
            `;
        })
        .join('');

    dom.customerSelectAll.checked = filtered.every((item) => state.pickerCustomerIds.includes(Number(item.id)));
}

function openCustomerPicker() {
    state.pickerCustomerIds = [...state.selectedCustomerIds];
    dom.customerPickerSearch.value = '';
    renderCustomerPickerRows();
    dom.customerPickerModal.classList.add('show');
    document.body.style.overflow = 'hidden';
}

function closeCustomerPicker() {
    dom.customerPickerModal.classList.remove('show');
    document.body.style.overflow = '';
}

function applyCustomerPicker() {
    state.selectedCustomerIds = [...state.pickerCustomerIds];
    renderSelectedCustomersDisplay();
    closeCustomerPicker();
}

function renderDocuments() {
    if (state.documents.length === 0) {
        dom.documentsGrid.innerHTML = '<div class="empty-state">Không có tài liệu phù hợp.</div>';
        return;
    }

    dom.documentsGrid.innerHTML = state.documents
        .map((doc) => {
            const safeTitle = escapeHtml(doc.title);
            const safeCode = escapeHtml(doc.document_code);
            const safeType = escapeHtml(doc.document_type_name || 'Không rõ loại');
            const safeCustomer = escapeHtml(doc.customer_names || doc.customer_name || 'Không rõ khách hàng');
            const safeFileName = escapeHtml(doc.file_name || '--');
            return `
                <div class="document-card">
                    <div class="doc-top">
                        <div class="doc-title">${safeTitle}</div>
                        <div class="doc-code">${safeCode}</div>
                    </div>
                    <div class="doc-meta">
                        <div class="meta-row"><span>Loại:</span><strong>${safeType}</strong></div>
                        <div class="meta-row"><span>Khách hàng:</span><strong>${safeCustomer}</strong></div>
                        <div class="meta-row"><span>File:</span><strong>${safeFileName}</strong></div>
                        <div class="meta-row"><span>Kích thước:</span><strong>${formatFileSize(doc.file_size)}</strong></div>
                        <div class="meta-row"><span>Tạo:</span><strong>${formatDate(doc.created_at)}</strong></div>
                        <div class="meta-row"><span>Trạng thái:</span><strong class="status ${escapeHtml(doc.status)}">${mapStatusLabel(doc.status)}</strong></div>
                    </div>
                    <div class="doc-actions">
                        <a class="doc-btn btn-view" href="${escapeHtml(doc.file_path)}" target="_blank" rel="noopener noreferrer">Xem file</a>
                        <button class="doc-btn btn-edit action-edit" data-id="${doc.id}" type="button">Sửa</button>
                        <button class="doc-btn btn-delete action-delete" data-id="${doc.id}" data-title="${safeTitle}" type="button">Xóa</button>
                    </div>
                </div>
            `;
        })
        .join('');
}

function populateMetaOptions() {
    dom.documentType.innerHTML = state.documentTypes
        .map((item) => `<option value="${item.id}">${escapeHtml(item.name)}</option>`)
        .join('');

    dom.typeFilter.innerHTML = [
        '<option value="">Tất cả loại tài liệu</option>',
        ...state.documentTypes.map((item) => `<option value="${item.id}">${escapeHtml(item.name)}</option>`),
    ].join('');
}

async function loadMeta() {
    const data = await requestJson(`${apiUrl}?action=meta`);
    state.documentTypes = Array.isArray(data.data?.document_types) ? data.data.document_types : [];
    state.customers = Array.isArray(data.data?.customers) ? data.data.customers : [];
    populateMetaOptions();
}

async function loadDocuments() {
    dom.documentsGrid.innerHTML = '<div class="empty-state">Đang tải dữ liệu...</div>';
    try {
        const data = await requestJson(`${apiUrl}?${buildQuery()}`);
        state.documents = Array.isArray(data.data) ? data.data : [];
        state.total = Number(data.pagination?.total || 0);
        state.totalPages = Math.max(1, Number(data.pagination?.total_pages || 1));
    } catch (error) {
        state.documents = [];
        state.total = 0;
        state.totalPages = 1;
        showToast(error.message, 'error');
    }
    renderDocuments();
    updateSubtitle();
    updatePagination();
}

function openFormModal(mode, doc = null) {
    const isEdit = mode === 'edit';
    dom.formTitle.textContent = isEdit ? 'Cập nhật tài liệu' : 'Thêm tài liệu';
    setFormError('');

    dom.documentId.value = doc?.id ?? '';
    dom.documentTitle.value = doc?.title ?? '';
    dom.documentType.value = String(doc?.document_type_id ?? state.documentTypes[0]?.id ?? '');
    state.selectedCustomerIds = Array.isArray(doc?.customer_ids) && doc.customer_ids.length > 0
        ? doc.customer_ids.map((id) => Number(id))
        : [Number(doc?.customer_id ?? state.customers[0]?.id ?? 0)].filter((id) => id > 0);
    renderSelectedCustomersDisplay();
    dom.documentStatus.value = doc?.status ?? 'published';
    dom.documentFilePath.value = doc?.file_path ?? '';
    dom.documentFileName.value = doc?.file_name ?? '';
    dom.documentFileSize.value = String(doc?.file_size ?? 0);
    dom.documentMimeType.value = doc?.mime_type ?? '';
    dom.uploadFileInput.value = '';
    dom.uploadFileLabel.textContent = doc?.file_name ? `Đã chọn: ${doc.file_name}` : 'Chưa chọn file';

    dom.formModal.classList.add('show');
    document.body.style.overflow = 'hidden';
}

function closeFormModal() {
    dom.formModal.classList.remove('show');
    document.body.style.overflow = '';
}

function openDeleteModal(id, title) {
    state.deletingId = id;
    dom.deleteText.textContent = `Bạn có chắc chắn muốn xóa tài liệu "${title}" không?`;
    dom.deleteModal.classList.add('show');
    document.body.style.overflow = 'hidden';
}

function closeDeleteModal() {
    dom.deleteModal.classList.remove('show');
    document.body.style.overflow = '';
}

function collectFormPayload() {
    return {
        id: Number(dom.documentId.value || 0),
        title: dom.documentTitle.value.trim(),
        document_type_id: Number(dom.documentType.value || 0),
        customer_ids: [...state.selectedCustomerIds],
        status: dom.documentStatus.value,
        file_path: dom.documentFilePath.value.trim(),
        file_name: dom.documentFileName.value.trim(),
        file_size: Number(dom.documentFileSize.value || 0),
        mime_type: dom.documentMimeType.value.trim(),
    };
}

async function submitForm(event) {
    event.preventDefault();
    setFormError('');

    const payload = collectFormPayload();
    const isEdit = payload.id > 0;

    if (!payload.title || !payload.document_type_id || payload.customer_ids.length === 0 || !payload.file_path || !payload.file_name) {
        setFormError('Vui lòng nhập đủ tiêu đề, loại, chọn ít nhất một khách hàng và upload file.');
        return;
    }

    try {
        await requestJson(apiUrl, {
            method: isEdit ? 'PUT' : 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload),
        });
        closeFormModal();
        showToast(isEdit ? 'Cập nhật tài liệu thành công.' : 'Tạo tài liệu thành công.');
        await loadDocuments();
    } catch (error) {
        setFormError(error.message);
    }
}

async function confirmDeleteDocument() {
    if (!state.deletingId) return;
    try {
        await requestJson(apiUrl, {
            method: 'DELETE',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: state.deletingId }),
        });
        closeDeleteModal();
        state.deletingId = null;
        showToast('Đã xóa tài liệu.');
        await loadDocuments();
    } catch (error) {
        showToast(error.message, 'error');
    }
}

function handleCardActions(event) {
    const editButton = event.target.closest('.action-edit');
    const deleteButton = event.target.closest('.action-delete');

    if (editButton) {
        const id = Number(editButton.dataset.id || 0);
        const doc = state.documents.find((item) => item.id === id);
        if (doc) openFormModal('edit', doc);
        return;
    }

    if (deleteButton) {
        const id = Number(deleteButton.dataset.id || 0);
        const title = deleteButton.dataset.title || 'tài liệu này';
        openDeleteModal(id, title);
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
    dom.documentForm.addEventListener('submit', submitForm);
    dom.documentsGrid.addEventListener('click', handleCardActions);

    dom.openCustomerPickerBtn.addEventListener('click', openCustomerPicker);
    dom.addCustomerQuickBtn.addEventListener('click', () => {
        window.open('customers.html', '_blank');
    });
    dom.closeCustomerPickerBtn.addEventListener('click', closeCustomerPicker);
    dom.cancelCustomerPickerBtn.addEventListener('click', closeCustomerPicker);
    dom.applyCustomerPickerBtn.addEventListener('click', applyCustomerPicker);
    dom.customerPickerSearch.addEventListener('input', renderCustomerPickerRows);

    dom.customerPickerBody.addEventListener('change', (event) => {
        const checkbox = event.target.closest('.picker-customer-item');
        if (!checkbox) return;
        const id = Number(checkbox.dataset.id || 0);
        if (id <= 0) return;
        if (checkbox.checked) {
            if (!state.pickerCustomerIds.includes(id)) state.pickerCustomerIds.push(id);
        } else {
            state.pickerCustomerIds = state.pickerCustomerIds.filter((item) => item !== id);
        }
        renderCustomerPickerRows();
    });

    dom.customerSelectAll.addEventListener('change', () => {
        const visibleIds = getFilteredCustomersByPickerKeyword().map((item) => Number(item.id));
        if (dom.customerSelectAll.checked) {
            const merged = new Set([...state.pickerCustomerIds, ...visibleIds]);
            state.pickerCustomerIds = Array.from(merged);
        } else {
            state.pickerCustomerIds = state.pickerCustomerIds.filter((id) => !visibleIds.includes(id));
        }
        renderCustomerPickerRows();
    });

    dom.searchInput.addEventListener(
        'input',
        debounce(() => {
            state.search = dom.searchInput.value.trim();
            state.page = 1;
            loadDocuments();
        }, 300)
    );

    dom.statusFilter.addEventListener('change', () => {
        state.status = dom.statusFilter.value;
        state.page = 1;
        loadDocuments();
    });

    dom.typeFilter.addEventListener('change', () => {
        state.documentTypeId = dom.typeFilter.value;
        state.page = 1;
        loadDocuments();
    });

    if (dom.limitSelect) {
        state.limit = Math.max(1, parseInt(dom.limitSelect.value, 10)) || 10;
        dom.limitSelect.addEventListener('change', () => {
            state.limit = Math.max(1, parseInt(dom.limitSelect.value, 10)) || 10;
            state.page = 1;
            loadDocuments();
        });
    }

    dom.prevPageButton.addEventListener('click', () => {
        if (state.page <= 1) return;
        state.page -= 1;
        loadDocuments();
    });

    dom.nextPageButton.addEventListener('click', () => {
        if (state.page >= state.totalPages) return;
        state.page += 1;
        loadDocuments();
    });

    dom.uploadFileButton.addEventListener('click', () => dom.uploadFileInput.click());
    dom.uploadFileInput.addEventListener('change', async () => {
        const file = dom.uploadFileInput.files && dom.uploadFileInput.files[0];
        if (!file) return;
        setFormError('');
        try {
            const uploaded = await uploadDocumentFile(file);
            dom.documentFilePath.value = uploaded.file_path || '';
            dom.documentFileName.value = uploaded.file_name || file.name;
            dom.documentFileSize.value = String(uploaded.file_size || file.size || 0);
            dom.documentMimeType.value = uploaded.mime_type || '';
            dom.uploadFileLabel.textContent = `Đã chọn: ${dom.documentFileName.value}`;
            showToast('Upload file tài liệu thành công.');
        } catch (error) {
            setFormError(error.message);
        }
    });

    document.getElementById('closeDocumentModalBtn').addEventListener('click', closeFormModal);
    document.getElementById('cancelDocumentBtn').addEventListener('click', closeFormModal);
    document.getElementById('cancelDeleteDocumentBtn').addEventListener('click', closeDeleteModal);
    document.getElementById('confirmDeleteDocumentBtn').addEventListener('click', confirmDeleteDocument);

    dom.formModal.addEventListener('click', (event) => {
        if (event.target === dom.formModal) closeFormModal();
    });

    dom.deleteModal.addEventListener('click', (event) => {
        if (event.target === dom.deleteModal) closeDeleteModal();
    });

    dom.customerPickerModal.addEventListener('click', (event) => {
        if (event.target === dom.customerPickerModal) closeCustomerPicker();
    });
}

async function initPage() {
    try {
        await loadMeta();
    } catch (error) {
        showToast(error.message, 'error');
    }
    await loadDocuments();
}

initEvents();
initPage();

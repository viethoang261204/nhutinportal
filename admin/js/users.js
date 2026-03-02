const apiUrl = './api/users.php';
const defaultAvatar = '../img/default.png';

const state = {
    users: [],
    search: '',
    status: '',
    role: '',
    page: 1,
    limit: 10,
    total: 0,
    totalPages: 1,
    deletingId: null,
};

const dom = {
    subtitle: document.getElementById('usersSubtitle'),
    searchInput: document.getElementById('userSearch'),
    roleFilter: document.getElementById('roleFilter'),
    statusFilter: document.getElementById('statusFilter'),
    usersContainer: document.getElementById('usersGrid'),
    addButton: document.getElementById('addUserBtn'),
    paginationInfo: document.getElementById('paginationInfo'),
    limitSelect: document.getElementById('limitSelect'),
    prevPageButton: document.getElementById('prevPageBtn'),
    nextPageButton: document.getElementById('nextPageBtn'),
    pageInfo: document.getElementById('pageInfo'),
    formModal: document.getElementById('userFormModal'),
    formTitle: document.getElementById('userModalTitle'),
    userForm: document.getElementById('userForm'),
    userId: document.getElementById('userId'),
    username: document.getElementById('username'),
    fullName: document.getElementById('fullName'),
    email: document.getElementById('email'),
    phone: document.getElementById('phone'),
    password: document.getElementById('password'),
    isActive: document.getElementById('isActive'),
    avatarValue: document.getElementById('avatarValue'),
    avatarFile: document.getElementById('avatarFile'),
    avatarPreview: document.getElementById('avatarPreview'),
    chooseAvatarBtn: document.getElementById('chooseAvatarBtn'),
    clearAvatarBtn: document.getElementById('clearAvatarBtn'),
    formError: document.getElementById('userFormError'),
    changePasswordModal: document.getElementById('changePasswordModal'),
    changePasswordError: document.getElementById('changePasswordError'),
    changePasswordForm: document.getElementById('changePasswordForm'),
    changePasswordUserId: document.getElementById('changePasswordUserId'),
    changePasswordUserName: document.getElementById('changePasswordUserName'),
    newPassword: document.getElementById('newPassword'),
    confirmPassword: document.getElementById('confirmPassword'),
    deleteModal: document.getElementById('deleteUserModal'),
    deleteText: document.getElementById('deleteUserText'),
    toast: document.getElementById('userToast'),
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

function showToast(message, type = 'success') {
    if (!dom.toast) return;
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

function setAvatarPreview(url) {
    dom.avatarPreview.src = url || defaultAvatar;
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
    dom.subtitle.textContent = `Tổng số: ${state.total} người dùng`;
}

function buildQuery() {
    const params = new URLSearchParams();
    if (state.search) params.set('search', state.search);
    if (state.status) params.set('status', state.status);
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

function renderUsers() {
    if (state.users.length === 0) {
        dom.usersContainer.innerHTML = '<div class="empty-card">Không có dữ liệu người dùng phù hợp.</div>';
        return;
    }

    dom.usersContainer.innerHTML = state.users
        .map((user) => {
            const safeName = escapeHtml(user.full_name);
            const safeUsername = escapeHtml(user.username);
            const safeEmail = escapeHtml(user.email);
            const safePhone = escapeHtml(user.phone || '--');
            const safeAvatar = escapeHtml(user.avatar_url || defaultAvatar);
            const statusClass = user.is_active ? 'active' : 'inactive';
            const statusText = user.is_active ? 'Active' : 'Inactive';
            const role = (user.role || 'staff').toLowerCase();
            const roleLabel = role === 'customer' ? 'Khách hàng' : 'Nhân viên';
            const roleBadgeClass = role === 'customer' ? 'customer' : 'staff';
            return `
                <div class="user-card">
                    <div class="user-card-header">
                        <img src="${safeAvatar}" class="user-card-avatar" alt="${safeName}">
                        <div class="user-card-info">
                            <div class="user-card-name">${safeName} <span class="user-role-badge ${roleBadgeClass}">${roleLabel}</span></div>
                            <div class="user-card-role">@${safeUsername}</div>
                        </div>
                    </div>
                    <div class="user-card-body">
                        <div class="user-detail">
                            <svg viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            ${safeEmail}
                        </div>
                        <div class="user-detail">
                            <svg viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                            ${safePhone}
                        </div>
                        <div class="user-detail">
                            <svg viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            Tạo: ${formatDate(user.created_at)}
                        </div>
                    </div>
                    <div class="user-card-footer">
                        <span class="user-status ${statusClass}">${statusText}</span>
                        <div class="user-actions">
                            <button class="user-action-btn action-changepw" data-id="${user.id}" data-name="${safeName}" title="Đổi mật khẩu">
                                <svg viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                            </button>
                            <button class="user-action-btn action-edit" data-id="${user.id}" title="Chỉnh sửa">
                                <svg viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </button>
                            <button class="user-action-btn action-delete" data-id="${user.id}" data-name="${safeName}" title="Xóa">
                                <svg viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </div>
                    </div>
                </div>
            `;
        })
        .join('');
}

async function loadUsers() {
    dom.usersContainer.innerHTML = '<div class="empty-card">Đang tải...</div>';
    try {
        const data = await requestJson(`${apiUrl}?${buildQuery()}`);
        state.users = Array.isArray(data.data) ? data.data : [];
        state.total = Number(data.pagination?.total || 0);
        state.totalPages = Math.max(1, Number(data.pagination?.total_pages || 1));
    } catch (error) {
        state.users = [];
        state.total = 0;
        state.totalPages = 1;
        dom.usersContainer.innerHTML = '<div class="empty-card">Không có dữ liệu người dùng phù hợp.</div>';
        showToast(error.message, 'error');
    }

    renderUsers();
    updateSubtitle();
    updatePagination();
}

function openFormModal(mode, user = null) {
    const isEdit = mode === 'edit';
    dom.formTitle.textContent = isEdit ? 'Cập nhật nhân viên' : 'Thêm nhân viên';
    setFormError('');

    dom.userId.value = user?.id ?? '';
    dom.username.value = user?.username ?? '';
    dom.fullName.value = user?.full_name ?? '';
    dom.email.value = user?.email ?? '';
    dom.phone.value = user?.phone ?? '';
    dom.password.value = '';
    dom.password.placeholder = isEdit ? 'Để trống nếu không đổi mật khẩu' : 'Nhập mật khẩu (>= 6 ký tự)';
    dom.password.required = !isEdit;
    dom.isActive.value = user?.is_active ? '1' : '0';
    dom.avatarValue.value = user?.avatar_url === defaultAvatar ? '' : (user?.avatar_url ?? '');
    dom.avatarFile.value = '';
    setAvatarPreview(user?.avatar_url || defaultAvatar);

    dom.formModal.classList.add('show');
    document.body.style.overflow = 'hidden';
}

function closeFormModal() {
    dom.formModal.classList.remove('show');
    document.body.style.overflow = '';
}

function openDeleteModal(userId, userName) {
    state.deletingId = userId;
    dom.deleteText.textContent = `Bạn có chắc chắn muốn xóa nhân viên "${userName}" không?`;
    dom.deleteModal.classList.add('show');
    document.body.style.overflow = 'hidden';
}

function closeDeleteModal() {
    dom.deleteModal.classList.remove('show');
    document.body.style.overflow = '';
}

function collectFormPayload() {
    return {
        id: Number(dom.userId.value || 0),
        username: dom.username.value.trim(),
        full_name: dom.fullName.value.trim(),
        email: dom.email.value.trim(),
        phone: dom.phone.value.trim(),
        password: dom.password.value,
        is_active: Number(dom.isActive.value || 1),
        avatar_url: dom.avatarValue.value.trim(),
    };
}

async function submitForm(event) {
    event.preventDefault();
    setFormError('');

    const payload = collectFormPayload();
    const isEdit = payload.id > 0;

    if (!payload.username || !payload.full_name || !payload.email) {
        setFormError('Vui lòng nhập đầy đủ username, họ tên và email.');
        return;
    }

    if (!isEdit && payload.password.length < 6) {
        setFormError('Mật khẩu tối thiểu 6 ký tự.');
        return;
    }

    try {
        await requestJson(apiUrl, {
            method: isEdit ? 'PUT' : 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload),
        });
        closeFormModal();
        showToast(isEdit ? 'Cập nhật nhân viên thành công.' : 'Tạo nhân viên thành công.');
        await loadUsers();
    } catch (error) {
        setFormError(error.message);
    }
}

async function confirmDeleteUser() {
    if (!state.deletingId) return;
    try {
        await requestJson(apiUrl, {
            method: 'DELETE',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: state.deletingId }),
        });
        closeDeleteModal();
        state.deletingId = null;
        showToast('Đã xóa nhân viên.');
        await loadUsers();
    } catch (error) {
        showToast(error.message, 'error');
    }
}

function debounce(fn, delay) {
    let timeoutId = null;
    return (...args) => {
        window.clearTimeout(timeoutId);
        timeoutId = window.setTimeout(() => fn(...args), delay);
    };
}

function openChangePasswordModal(user) {
    dom.changePasswordUserId.value = user?.id ?? '';
    dom.changePasswordUserName.value = (user?.full_name || user?.username || '') + ' (' + (user?.email || '') + ')';
    dom.newPassword.value = '';
    dom.confirmPassword.value = '';
    dom.changePasswordError.textContent = '';
    dom.changePasswordError.classList.remove('show');
    dom.changePasswordModal.classList.add('show');
    document.body.style.overflow = 'hidden';
}

function closeChangePasswordModal() {
    dom.changePasswordModal.classList.remove('show');
    document.body.style.overflow = '';
}

async function submitChangePassword(event) {
    event.preventDefault();
    const userId = Number(dom.changePasswordUserId.value || 0);
    const newPw = dom.newPassword.value;
    const confirmPw = dom.confirmPassword.value;
    dom.changePasswordError.textContent = '';
    dom.changePasswordError.classList.remove('show');

    if (newPw.length < 6) {
        dom.changePasswordError.textContent = 'Mật khẩu tối thiểu 6 ký tự.';
        dom.changePasswordError.classList.add('show');
        return;
    }
    if (newPw !== confirmPw) {
        dom.changePasswordError.textContent = 'Mật khẩu xác nhận không khớp.';
        dom.changePasswordError.classList.add('show');
        return;
    }

    try {
        await requestJson(apiUrl + '?action=change_password', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: userId, new_password: newPw }),
        });
        closeChangePasswordModal();
        showToast('Đã đổi mật khẩu thành công.');
    } catch (error) {
        dom.changePasswordError.textContent = error.message;
        dom.changePasswordError.classList.add('show');
    }
}

function handleCardActions(event) {
    const changePwButton = event.target.closest('.action-changepw');
    const editButton = event.target.closest('.action-edit');
    const deleteButton = event.target.closest('.action-delete');

    if (changePwButton) {
        const userId = Number(changePwButton.dataset.id || 0);
        const userName = changePwButton.dataset.name || '';
        const user = state.users.find((item) => item.id === userId);
        if (user) openChangePasswordModal(user);
        return;
    }

    if (editButton) {
        const userId = Number(editButton.dataset.id || 0);
        const user = state.users.find((item) => item.id === userId);
        if (user) {
            openFormModal('edit', user);
        }
        return;
    }

    if (deleteButton) {
        const userId = Number(deleteButton.dataset.id || 0);
        const userName = deleteButton.dataset.name || 'người dùng này';
        openDeleteModal(userId, userName);
    }
}

function initEvents() {
    dom.addButton.addEventListener('click', () => openFormModal('create'));
    dom.userForm.addEventListener('submit', submitForm);
    dom.usersContainer.addEventListener('click', handleCardActions);

    dom.searchInput.addEventListener(
        'input',
        debounce(() => {
            state.search = dom.searchInput.value.trim();
            state.page = 1;
            loadUsers();
        }, 300)
    );

    dom.statusFilter.addEventListener('change', () => {
        state.status = dom.statusFilter.value;
        state.page = 1;
        loadUsers();
    });

    if (dom.limitSelect) {
state.limit = Math.max(1, parseInt(dom.limitSelect.value, 10)) || 10;
            dom.limitSelect.addEventListener('change', () => {
                state.limit = Math.max(1, parseInt(dom.limitSelect.value, 10)) || 10;
            state.page = 1;
            loadUsers();
        });
    }

    dom.prevPageButton.addEventListener('click', () => {
        if (state.page <= 1) return;
        state.page -= 1;
        loadUsers();
    });

    dom.nextPageButton.addEventListener('click', () => {
        if (state.page >= state.totalPages) return;
        state.page += 1;
        loadUsers();
    });

    document.getElementById('closeUserModalBtn').addEventListener('click', closeFormModal);
    document.getElementById('cancelUserBtn').addEventListener('click', closeFormModal);
    document.getElementById('closeChangePasswordBtn').addEventListener('click', closeChangePasswordModal);
    document.getElementById('cancelChangePasswordBtn').addEventListener('click', closeChangePasswordModal);
    dom.changePasswordForm.addEventListener('submit', submitChangePassword);
    dom.changePasswordModal.addEventListener('click', (e) => { if (e.target === dom.changePasswordModal) closeChangePasswordModal(); });
    document.getElementById('cancelDeleteUserBtn').addEventListener('click', closeDeleteModal);
    document.getElementById('confirmDeleteUserBtn').addEventListener('click', confirmDeleteUser);

    dom.chooseAvatarBtn.addEventListener('click', () => dom.avatarFile.click());
    dom.clearAvatarBtn.addEventListener('click', () => {
        dom.avatarValue.value = '';
        dom.avatarFile.value = '';
        setAvatarPreview(defaultAvatar);
    });

    dom.avatarFile.addEventListener('change', async () => {
        const file = dom.avatarFile.files && dom.avatarFile.files[0];
        if (!file) return;
        setFormError('');
        try {
            const avatarUrl = await uploadAvatarFile(file);
            dom.avatarValue.value = avatarUrl;
            setAvatarPreview(avatarUrl);
            showToast('Tải ảnh đại diện thành công.');
        } catch (error) {
            dom.avatarValue.value = '';
            setAvatarPreview(defaultAvatar);
            setFormError(error.message);
        }
    });

    dom.formModal.addEventListener('click', (event) => {
        if (event.target === dom.formModal) closeFormModal();
    });

    dom.deleteModal.addEventListener('click', (event) => {
        if (event.target === dom.deleteModal) closeDeleteModal();
    });
}

initEvents();
loadUsers();

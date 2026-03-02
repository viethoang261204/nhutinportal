(() => {
    const apiUrl = './api/settings.php';
    const defaultAvatar = '../img/default.png';

    const dom = {
        companyName: document.getElementById('settingCompanyName'),
        contactEmail: document.getElementById('settingContactEmail'),
        contactPhone: document.getElementById('settingContactPhone'),
        companyAddress: document.getElementById('settingCompanyAddress'),
        emailNotifications: document.getElementById('settingEmailNotifications'),
        autoAssignTickets: document.getElementById('settingAutoAssignTickets'),
        customerRegistration: document.getElementById('settingCustomerRegistration'),
        maintenanceMode: document.getElementById('settingMaintenanceMode'),
        twoFactorAuth: document.getElementById('settingTwoFactorAuth'),
        sessionTimeout: document.getElementById('settingSessionTimeout'),
        currentPassword: document.getElementById('settingCurrentPassword'),
        newPassword: document.getElementById('settingNewPassword'),
        saveGeneralBtn: document.getElementById('saveGeneralBtn'),
        cancelGeneralBtn: document.getElementById('cancelGeneralBtn'),
        saveSystemBtn: document.getElementById('saveSystemBtn'),
        savePasswordBtn: document.getElementById('savePasswordBtn'),
        saveSecurityBtn: document.getElementById('saveSecurityBtn'),
        btnResetSettings: document.getElementById('btnResetSettings'),
        avatarPreview: document.getElementById('settingsAvatarPreview'),
        avatarFileInput: document.getElementById('avatarFileInput'),
        btnSelectAvatar: document.getElementById('btnSelectAvatar'),
        btnUploadAvatar: document.getElementById('btnUploadAvatar'),
        avatarFormError: document.getElementById('avatarFormError'),
        generalFormError: document.getElementById('generalFormError'),
        systemFormError: document.getElementById('systemFormError'),
        passwordFormError: document.getElementById('passwordFormError'),
        toast: document.getElementById('settingsToast'),
    };

    let lastLoaded = null;
    let selectedAvatarFile = null;

    function showToast(msg, isError = false) {
        if (!dom.toast) return;
        dom.toast.textContent = msg || '';
        dom.toast.classList.toggle('error', !!isError);
        dom.toast.classList.add('show');
        setTimeout(() => dom.toast.classList.remove('show'), 2500);
    }

    function setError(el, msg) {
        if (!el) return;
        el.textContent = msg || '';
        el.classList.toggle('show', !!msg);
    }

    function fillForm(data) {
        lastLoaded = data || {};
        if (dom.companyName) dom.companyName.value = data.company_name || '';
        if (dom.contactEmail) dom.contactEmail.value = data.contact_email || '';
        if (dom.contactPhone) dom.contactPhone.value = data.contact_phone || '';
        if (dom.companyAddress) dom.companyAddress.value = data.company_address || '';

        const on = (v) => v === '1' || v === true;
        if (dom.emailNotifications) dom.emailNotifications.checked = on(data.email_notifications);
        if (dom.autoAssignTickets) dom.autoAssignTickets.checked = on(data.auto_assign_tickets);
        if (dom.customerRegistration) dom.customerRegistration.checked = on(data.customer_registration);
        if (dom.maintenanceMode) dom.maintenanceMode.checked = on(data.maintenance_mode);
        if (dom.twoFactorAuth) dom.twoFactorAuth.checked = on(data.two_factor_auth);
        if (dom.sessionTimeout) dom.sessionTimeout.checked = on(data.session_timeout);

        if (dom.currentPassword) dom.currentPassword.value = '';
        if (dom.newPassword) dom.newPassword.value = '';
    }

    async function requestJson(url, options = {}) {
        const res = await fetch(url, { credentials: 'same-origin', ...options });
        const text = await res.text();
        let json;
        try { json = text ? JSON.parse(text) : {}; } catch { throw new Error('Phản hồi không hợp lệ.'); }
        if (res.status === 401) {
            window.location.href = 'login.php';
            throw new Error('Phiên đăng nhập hết hạn.');
        }
        if (!res.ok || !json.success) throw new Error(json.message || 'Có lỗi xảy ra.');
        return json;
    }

    async function loadProfile() {
        try {
            const j = await requestJson(apiUrl + '?action=get_profile');
            const p = j.profile || {};
            const avatarUrl = p.avatar_url || defaultAvatar;
            if (dom.avatarPreview) {
                dom.avatarPreview.src = avatarUrl;
                dom.avatarPreview.onerror = () => { dom.avatarPreview.src = defaultAvatar; };
            }
        } catch (e) {
            if (dom.avatarPreview) dom.avatarPreview.src = defaultAvatar;
        }
    }

    async function loadSettings() {
        try {
            const j = await requestJson(apiUrl);
            fillForm(j.data || {});
        } catch (e) {
            showToast(e.message || 'Không thể tải cài đặt.', true);
            fillForm({});
        }
    }

    async function saveGeneral() {
        setError(dom.generalFormError, '');
        const payload = {
            company_name: dom.companyName?.value.trim() || '',
            contact_email: dom.contactEmail?.value.trim() || '',
            contact_phone: dom.contactPhone?.value.trim() || '',
            company_address: dom.companyAddress?.value.trim() || '',
        };
        try {
            await requestJson(apiUrl, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload),
            });
            showToast('Đã lưu cài đặt chung.');
            await loadSettings();
        } catch (e) {
            setError(dom.generalFormError, e.message || 'Không thể lưu.');
        }
    }

    async function saveSystem() {
        setError(dom.systemFormError, '');
        const payload = {
            email_notifications: dom.emailNotifications?.checked ? '1' : '0',
            auto_assign_tickets: dom.autoAssignTickets?.checked ? '1' : '0',
            customer_registration: dom.customerRegistration?.checked ? '1' : '0',
            maintenance_mode: dom.maintenanceMode?.checked ? '1' : '0',
        };
        try {
            await requestJson(apiUrl, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload),
            });
            showToast('Đã lưu cấu hình hệ thống.');
            await loadSettings();
        } catch (e) {
            setError(dom.systemFormError, e.message || 'Không thể lưu.');
        }
    }

    async function saveSecurity() {
        const payload = {
            two_factor_auth: dom.twoFactorAuth?.checked ? '1' : '0',
            session_timeout: dom.sessionTimeout?.checked ? '1' : '0',
        };
        try {
            await requestJson(apiUrl, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload),
            });
            showToast('Đã lưu cài đặt bảo mật.');
            await loadSettings();
        } catch (e) {
            showToast(e.message || 'Không thể lưu cài đặt bảo mật.', true);
        }
    }

    async function savePassword() {
        setError(dom.passwordFormError, '');
        const current = dom.currentPassword?.value || '';
        const n = dom.newPassword?.value || '';
        if (!current) {
            setError(dom.passwordFormError, 'Vui lòng nhập mật khẩu hiện tại.');
            return;
        }
        if (!n || n.length < 6) {
            setError(dom.passwordFormError, 'Mật khẩu mới phải có ít nhất 6 ký tự.');
            return;
        }
        try {
            await requestJson(apiUrl + '?action=change_password', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ current_password: current, new_password: n }),
            });
            showToast('Đã cập nhật mật khẩu.');
            dom.currentPassword.value = '';
            dom.newPassword.value = '';
        } catch (e) {
            setError(dom.passwordFormError, e.message || 'Không thể đổi mật khẩu.');
        }
    }

    async function uploadAvatar() {
        if (!selectedAvatarFile) return;
        setError(dom.avatarFormError, '');
        const formData = new FormData();
        formData.append('avatar', selectedAvatarFile);
        try {
            const j = await requestJson(apiUrl + '?action=upload_avatar', {
                method: 'POST',
                body: formData,
            });
            const url = j.avatar_url || defaultAvatar;
            if (dom.avatarPreview) dom.avatarPreview.src = url;
            selectedAvatarFile = null;
            dom.btnUploadAvatar.disabled = true;
            if (dom.avatarFileInput) dom.avatarFileInput.value = '';
            const admin = JSON.parse(localStorage.getItem('nhutin_admin') || '{}');
            admin.avatar_url = url;
            localStorage.setItem('nhutin_admin', JSON.stringify(admin));
            if (typeof updateAdminInfo === 'function') updateAdminInfo();
            showToast('Đã cập nhật ảnh đại diện.');
        } catch (e) {
            setError(dom.avatarFormError, e.message || 'Không thể tải ảnh lên.');
        }
    }

    async function resetSettings() {
        if (!confirm('Bạn có chắc muốn khôi phục cài đặt mặc định? Cài đặt chung và cấu hình hệ thống sẽ bị reset.')) return;
        try {
            await requestJson(apiUrl, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    company_name: 'CÔNG TY CỔ PHẦN NHƯ TÍN',
                    contact_email: 'contact@nhutin.vn',
                    contact_phone: '0909 123 456',
                    company_address: '123 Đường ABC, Quận 1, TP.HCM',
                    email_notifications: '1',
                    auto_assign_tickets: '1',
                    customer_registration: '0',
                    maintenance_mode: '0',
                    two_factor_auth: '0',
                    session_timeout: '1',
                }),
            });
            showToast('Đã khôi phục cài đặt mặc định.');
            await loadSettings();
        } catch (e) {
            showToast(e.message || 'Không thể reset.', true);
        }
    }

    function init() {
        dom.saveGeneralBtn?.addEventListener('click', saveGeneral);
        dom.cancelGeneralBtn?.addEventListener('click', () => fillForm(lastLoaded));
        dom.saveSystemBtn?.addEventListener('click', saveSystem);
        dom.saveSecurityBtn?.addEventListener('click', saveSecurity);
        dom.savePasswordBtn?.addEventListener('click', savePassword);
        dom.btnResetSettings?.addEventListener('click', resetSettings);

        dom.btnSelectAvatar?.addEventListener('click', () => dom.avatarFileInput?.click());
        dom.avatarFileInput?.addEventListener('change', (e) => {
            const f = e.target?.files?.[0];
            if (!f) return;
            if (!f.type.startsWith('image/') || f.size > 5 * 1024 * 1024) {
                setError(dom.avatarFormError, 'Vui lòng chọn ảnh (JPG, PNG, WebP, GIF) dưới 5MB.');
                return;
            }
            setError(dom.avatarFormError, '');
            selectedAvatarFile = f;
            dom.btnUploadAvatar.disabled = false;
            const reader = new FileReader();
            reader.onload = () => { if (dom.avatarPreview) dom.avatarPreview.src = reader.result; };
            reader.readAsDataURL(f);
        });
        dom.btnUploadAvatar?.addEventListener('click', uploadAvatar);

        loadSettings();
        loadProfile();
    }

    init();
})();

// NHUTIN Admin Panel - Navigation
// admins = admin (menu đầy đủ), users = nhân viên (menu staff)

var adminMenu = [
    { title: 'Tổng quan', items: [
        { id: 'dashboard', icon: 'home', label: 'Dashboard', href: 'dashboard.html' }
    ]},
    { title: 'Quản lý', items: [
        { id: 'customers', icon: 'users', label: 'Khách hàng', href: 'customers.html' },
        { id: 'users', icon: 'user', label: 'Người dùng', href: 'users.html' },
        { id: 'documents', icon: 'file-text', label: 'Tài liệu', href: 'documents.html' },
        { id: 'posts', icon: 'edit', label: 'Bài viết', href: 'posts.html' }
    ]},
    { title: 'Hệ thống', items: [
        { id: 'tickets', icon: 'headphones', label: 'Tickets', href: 'tickets.html', badgeDynamic: true },
        { id: 'activity', icon: 'activity', label: 'Nhật ký hoạt động', href: 'activity.html' },
        { id: 'settings', icon: 'settings', label: 'Cài đặt', href: 'settings.html' }
    ]}
];

var staffMenu = [
    { title: 'Tổng quan', items: [
        { id: 'dashboard', icon: 'home', label: 'Dashboard', href: 'dashboard.html' }
    ]},
    { title: 'Quản lý', items: [
        { id: 'customers', icon: 'users', label: 'Khách hàng', href: 'customers' },
        { id: 'posts', icon: 'edit', label: 'Bài viết', href: 'posts' }
    ]},
    { title: 'Hệ thống', items: [
        { id: 'tickets', icon: 'headphones', label: 'Tickets', href: 'tickets', badgeDynamic: true },
        { id: 'settings', icon: 'settings', label: 'Cài đặt', href: 'settings' }
    ]}
];

var icons = {
    'home': '<path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>',
    'users': '<path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>',
    'user': '<path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>',
    'file-text': '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>',
    'edit': '<path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>',
    'headphones': '<path stroke-linecap="round" stroke-linejoin="round" d="M3 18v-6a9 9 0 0118 0v6M3 18a3 3 0 003 3h0a3 3 0 003-3v-3a3 3 0 00-3-3h0a3 3 0 00-3 3v3zm18 0a3 3 0 01-3 3h0a3 3 0 01-3-3v-3a3 3 0 013-3h0a3 3 0 013 3v3z"/>',
    'settings': '<path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>',
    'activity': '<path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>'
};

function getAuthRole() {
    try {
        var d = JSON.parse(localStorage.getItem('nhutin_admin') || '{}');
        return (d.role === 'staff') ? 'staff' : 'admin';
    } catch (e) { return 'admin'; }
}

function getIcon(name) {
    var p = icons[name] || icons['file-text'];
    return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">' + p + '</svg>';
}

function getMenuForRole() {
    return getAuthRole() === 'staff' ? staffMenu : adminMenu;
}

function renderAdminMenu(currentPage) {
    var navMenu = document.getElementById('navMenu');
    if (!navMenu) return;

    var menu = getMenuForRole();
    var html = '';
    for (var i = 0; i < menu.length; i++) {
        var section = menu[i];
        html += '<div class="nav-section"><div class="nav-section-title">' + section.title + '</div>';
        var items = section.items || [];
        for (var j = 0; j < items.length; j++) {
            var item = items[j];
            var href = item.href || '#';
            var isActive = (currentPage === item.id) || (window.location.href.indexOf(item.href) !== -1);
            var cls = 'nav-item' + (isActive ? ' active' : '');
            var badge = item.badgeDynamic ? '<span class="nav-badge" id="ticketNavBadge">0</span>' : '';
            html += '<a href="' + href + '" class="' + cls + '">' + getIcon(item.icon) + '<span>' + item.label + '</span>' + badge + '</a>';
        }
        html += '</div>';
    }
    navMenu.innerHTML = html;
}

function renderMobileNav(currentPage) {
    var mobileNav = document.getElementById('mobileNavItems');
    if (!mobileNav) return;

    var menu = getMenuForRole();
    var allItems = [];
    for (var i = 0; i < menu.length; i++) {
        var items = menu[i].items || [];
        for (var j = 0; j < items.length; j++) allItems.push(items[j]);
    }
    var html = '';
    for (var k = 0; k < Math.min(5, allItems.length); k++) {
        var it = allItems[k];
        var active = (currentPage === it.id || window.location.href.indexOf(it.href) !== -1) ? ' active' : '';
        html += '<a href="' + it.href + '" class="mobile-nav-item' + active + '">' + getIcon(it.icon) + '<span>' + it.label + '</span></a>';
    }
    mobileNav.innerHTML = html;
}

const DEFAULT_AVATAR_PLACEHOLDER = 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7';
const FALLBACK_AVATAR = 'https://i.pravatar.cc/150?img=8';

function _avatarUrlFromAdmin(admin) {
    var u = admin && admin.avatar_url ? admin.avatar_url : '';
    if (!u) return FALLBACK_AVATAR;
    return u.startsWith('http') ? u : (u.startsWith('../') ? u : '../' + u);
}

function updateAdminInfo() {
    var adminData = { name: 'Admin', email: 'admin@nhutin.vn' };
    try {
        adminData = JSON.parse(localStorage.getItem('nhutin_admin') || '{}') || adminData;
    } catch (_) {}
    
    var userNameEl = document.getElementById('userName');
    var userEmailEl = document.getElementById('userEmail');
    var userAvatarEl = document.getElementById('userAvatar');
    
    if (userNameEl) userNameEl.textContent = adminData.name || 'Admin';
    if (userEmailEl) userEmailEl.textContent = adminData.email || '';
    if (userAvatarEl) {
        userAvatarEl.src = _avatarUrlFromAdmin(adminData);
        userAvatarEl.onerror = function() { this.src = FALLBACK_AVATAR; };
    }
}

// Sidebar toggle
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('overlay');
    if (sidebar) sidebar.classList.toggle('open');
    if (overlay) overlay.classList.toggle('show');
}

function closeSidebar() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('overlay');
    if (sidebar) sidebar.classList.remove('open');
    if (overlay) overlay.classList.remove('show');
}

// Logout
function toggleUserMenu() {
    showLogoutModal();
}

function showLogoutModal() {
    const modal = document.getElementById('logoutModal');
    if (modal) {
        modal.classList.add('show');
        document.body.style.overflow = 'hidden';
    }
}

function closeLogoutModal() {
    const modal = document.getElementById('logoutModal');
    if (modal) {
        modal.classList.remove('show');
        document.body.style.overflow = '';
    }
}

function confirmLogout() {
    localStorage.removeItem('nhutin_admin');
    localStorage.removeItem('nhutin_admin_logged_in');
    window.location.href = 'logout.php';
}

// Fetch và cập nhật badge ticket chưa xử lý (open + progress) — luôn hiển thị số
async function updateTicketNavBadge() {
    const el = document.getElementById('ticketNavBadge');
    if (!el) return;
    try {
        const res = await fetch('./api/tickets.php?action=stats', { credentials: 'same-origin' });
        const json = await res.json();
        if (json.success && json.stats) {
            const count = (json.stats.open || 0) + (json.stats.progress || 0);
            if (el.textContent !== String(count)) {
                el.textContent = count;
            }
        }
    } catch {
        el.textContent = '0';
    }
}

// Inject mobile menu toggle button
function ensureMenuToggle() {
    if (document.getElementById('menuToggleBtn')) return;
    const btn = document.createElement('button');
    btn.id = 'menuToggleBtn';
    btn.className = 'menu-toggle';
    btn.type = 'button';
    btn.setAttribute('aria-label', 'Mở menu');
    btn.innerHTML = '<svg viewBox="0 0 24 24"><path d="M4 6h16M4 12h16M4 18h16"/></svg>';
    btn.onclick = toggleSidebar;
    document.body.appendChild(btn);
}

// Initialize admin navigation
function initAdminNav(currentPage) {
    ensureMenuToggle();
    renderAdminMenu(currentPage);
    renderMobileNav(currentPage);
    updateAdminInfo();
    updateTicketNavBadge();
    
    // Close modal when clicking outside
    const logoutModal = document.getElementById('logoutModal');
    if (logoutModal) {
        logoutModal.addEventListener('click', function(e) {
            if (e.target === this) {
                closeLogoutModal();
            }
        });
    }
    
    // Close sidebar when clicking overlay
    const overlay = document.getElementById('overlay');
    if (overlay) {
        overlay.addEventListener('click', closeSidebar);
    }
}

function getCurrentPage() {
    var h = (window.location.href || window.location.pathname || '') + '';
    if (h.indexOf('customers') !== -1) return 'customers';
    if (h.indexOf('users') !== -1) return 'users';
    if (h.indexOf('documents') !== -1) return 'documents';
    if (h.indexOf('posts') !== -1) return 'posts';
    if (h.indexOf('tickets') !== -1) return 'tickets';
    if (h.indexOf('settings') !== -1) return 'settings';
    if (h.indexOf('activity') !== -1) return 'activity';
    return 'dashboard';
}

function boot() {
    var h = (window.location.href || '') + '';
    if (getAuthRole() === 'staff' && (h.indexOf('users.html') !== -1 || h.indexOf('documents.html') !== -1 || h.indexOf('activity.html') !== -1)) {
        window.location.replace('dashboard.html');
        return;
    }
    initAdminNav(getCurrentPage());
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
} else {
    boot();
}




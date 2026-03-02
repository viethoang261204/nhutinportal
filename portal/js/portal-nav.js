// NHUTIN Portal - Unified Navigation
// Menu giống nhau cho tất cả khách hàng

const portalConfig = {
    menu: [
        {
            title: 'Tổng quan',
            items: [
                { 
                    id: 'dashboard', 
                    icon: 'home', 
                    label: 'Dashboard', 
                    href: 'dashboard.html'
                }
            ]
        },
        {
            title: 'Tài liệu & Hỗ trợ',
            items: [
                { 
                    id: 'documents', 
                    icon: 'file-text', 
                    label: 'Tài liệu', 
                    href: 'documents.html'
                },
                { 
                    id: 'tickets', 
                    icon: 'headphones', 
                    label: 'Ticket / Hỗ trợ', 
                    href: 'tickets.html'
                },
                { 
                    id: 'support', 
                    icon: 'user', 
                    label: 'Liên hệ tư vấn', 
                    href: 'support.html'
                }
            ]
        },
        {
            title: 'Tài khoản',
            items: [
                { 
                    id: 'settings', 
                    icon: 'settings', 
                    label: 'Cài đặt', 
                    href: 'settings.html'
                }
            ]
        }
    ]
};

// Icon SVGs
const icons = {
    'home': '<path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>',
    'file-text': '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>',
    'headphones': '<path stroke-linecap="round" stroke-linejoin="round" d="M3 18v-6a9 9 0 0118 0v6M3 18a3 3 0 003 3h0a3 3 0 003-3v-3a3 3 0 00-3-3h0a3 3 0 00-3 3v3zm18 0a3 3 0 01-3 3h0a3 3 0 01-3-3v-3a3 3 0 013-3h0a3 3 0 013 3v3z"/>',
    'user': '<path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>',
    'settings': '<path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>'
};

function getIcon(name) {
    return `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">${icons[name] || icons['file-text']}</svg>`;
}

// Render menu
function renderPortalMenu(currentPage) {
    const navMenu = document.getElementById('navMenu');
    if (!navMenu) return;

    navMenu.innerHTML = portalConfig.menu.map(section => `
        <div class="nav-section">
            <div class="nav-section-title">${section.title}</div>
            ${section.items.map(item => {
                const isActive = currentPage === item.id || window.location.pathname.includes(item.href);
                return `
                    <a href="${item.href}" class="nav-item ${isActive ? 'active' : ''}">
                        ${getIcon(item.icon)}
                        <span>${item.label}</span>
                        ${item.badge ? `<span class="nav-badge">${item.badge}</span>` : ''}
                    </a>
                `;
            }).join('')}
        </div>
    `).join('');
}

// Render mobile nav
function renderMobileNav(currentPage) {
    const mobileNav = document.getElementById('mobileNavItems');
    if (!mobileNav) return;

    const allItems = [];
    portalConfig.menu.forEach(section => {
        section.items.forEach(item => allItems.push(item));
    });

    mobileNav.innerHTML = allItems.map(item => {
        const isActive = currentPage === item.id || window.location.pathname.includes(item.href);
        return `
            <a href="${item.href}" class="mobile-nav-item ${isActive ? 'active' : ''}">
                ${getIcon(item.icon)}
                <span>${item.label}</span>
            </a>
        `;
    }).join('');
}

// Chuẩn hóa user từ API (company_name -> companyName cho tương thích)
function normalizePortalUser(u) {
    if (!u) return null;
    const staff = u.assigned_staff || u.assignedStaff || {};
    return {
        name: u.name || u.email || 'Khách hàng',
        email: u.email || '',
        companyName: u.company_name || u.companyName || '',
        customer_code: u.customer_code || '',
        avatar_url: u.avatar_url || '',
        assignedStaff: staff
    };
}

// Placeholder avatar (data URI, không load mạng → không nhấp nháy)
var DEFAULT_AVATAR = "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='rgba(255,255,255,0.6)' stroke-width='1.5'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'/%3E%3C/svg%3E";

// Update user info (ưu tiên session qua api/auth, fallback localStorage)
function updateUserInfo() {
    const userNameEl = document.getElementById('userName');
    const userEmailEl = document.getElementById('userEmail');
    const userAvatarEl = document.getElementById('userAvatar');
    
    let userData = null;
    try {
        const stored = localStorage.getItem('nhutin_user');
        if (stored) userData = normalizePortalUser(JSON.parse(stored));
    } catch (e) {}
    
    if (!userData) userData = { name: 'Khách hàng', email: '', companyName: '', assignedStaff: {} };
    
    if (userNameEl) userNameEl.textContent = userData.name || 'Khách hàng';
    if (userEmailEl) userEmailEl.textContent = userData.email || '';
    
    if (userAvatarEl) {
        var avatar = userData.avatar_url || (userData.assignedStaff && userData.assignedStaff.avatar);
        if (avatar && avatar.length > 0 && avatar.indexOf('pravatar') === -1) {
            var src = (avatar.indexOf('http') === 0 || avatar.indexOf('/') === 0) ? avatar : ('../' + avatar.replace(/^\.\.\//, ''));
            userAvatarEl.src = src;
            userAvatarEl.onerror = function() { this.src = DEFAULT_AVATAR; this.onerror = null; };
        } else {
            userAvatarEl.src = DEFAULT_AVATAR;
        }
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
    localStorage.removeItem('nhutin_user');
    localStorage.removeItem('nhutin_logged_in');
    window.location.href = 'logout.php';
}

// Pjax: chuyển trang không reload navbar/sidebar
function getPageFromHref(href) {
    if (!href || typeof href !== 'string') return null;
    if (href.includes('dashboard')) return 'dashboard';
    if (href.includes('tickets')) return 'tickets';
    if (href.includes('documents')) return 'documents';
    if (href.includes('support')) return 'support';
    if (href.includes('settings')) return 'settings';
    return null;
}

function setupPjax() {
    document.addEventListener('click', function(e) {
        const a = e.target.closest('a[href]');
        if (!a) return;
        const href = a.getAttribute('href');
        const page = getPageFromHref(href);
        if (!page) return;
        if (a.target === '_blank' || a.hasAttribute('download')) return;
        const url = href.startsWith('http') ? href : new URL(href, window.location.href).href;
        if (!url.startsWith(window.location.origin)) return;
        const path = window.location.pathname || '';
        const samePage = (path.includes(page + '.html') || (page === 'dashboard' && !path.includes('tickets') && !path.includes('documents') && !path.includes('support') && !path.includes('settings')));
        if (samePage) return;

        e.preventDefault();
        loadPagePjax(href, page);
    });
}

function loadPagePjax(href, page) {
    const main = document.querySelector('.main-content');
    if (!main) { window.location.href = href; return; }

    const url = href.startsWith('/') ? window.location.origin + href : new URL(href, window.location.href).href;
    fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(function(r) { return r.text(); })
        .then(function(html) {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const newMain = doc.querySelector('.main-content');
            const newStyles = doc.querySelectorAll('style');
            if (newMain) main.innerHTML = newMain.innerHTML;
            const styleEl = document.getElementById('portal-page-styles');
            if (styleEl && newStyles.length) styleEl.textContent = Array.from(newStyles).map(function(s) { return s.textContent; }).join('\n');

            document.title = doc.querySelector('title') ? doc.querySelector('title').textContent : document.title;
            history.pushState({ page: page }, '', href);

            renderPortalMenu(page);
            renderMobileNav(page);

            var scripts = doc.querySelectorAll('script:not([src])');
            scripts.forEach(function(oldScript) {
                var s = document.createElement('script');
                s.textContent = oldScript.textContent;
                document.body.appendChild(s);
                document.body.removeChild(s);
            });

            window.dispatchEvent(new CustomEvent('portal:page-ready', { detail: { page: page } }));
        })
        .catch(function() { window.location.href = href; });
}

// Initialize portal navigation
function initPortalNav(currentPage) {
    renderPortalMenu(currentPage);
    renderMobileNav(currentPage);
    updateUserInfo();
    setupPjax();

    window.addEventListener('popstate', function() {
        var path = window.location.pathname || '';
        var p = 'dashboard';
        if (path.includes('tickets')) p = 'tickets';
        else if (path.includes('documents')) p = 'documents';
        else if (path.includes('support')) p = 'support';
        else if (path.includes('settings')) p = 'settings';
        loadPagePjax(path, p);
    });

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

// Kiểm tra auth: nếu chưa đăng nhập thì chuyển về login (trừ trang login)
function ensurePortalAuth(callback) {
    const path = window.location.pathname || '';
    if (path.includes('login.html') || path.includes('login.php') || path.includes('register')) {
        if (typeof callback === 'function') callback();
        return;
    }
    
    fetch('api/auth.php', { credentials: 'same-origin' })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.authenticated && data.user) {
                localStorage.setItem('nhutin_user', JSON.stringify(data.user));
                localStorage.setItem('nhutin_logged_in', 'true');
                if (typeof callback === 'function') callback();
            } else {
                localStorage.removeItem('nhutin_user');
                localStorage.removeItem('nhutin_logged_in');
                window.location.href = '/portal/login.html';
            }
        })
        .catch(function() {
            // Nếu không gọi được API (CORS, lỗi), dùng localStorage
            const loggedIn = localStorage.getItem('nhutin_logged_in');
            const user = localStorage.getItem('nhutin_user');
            if (loggedIn === 'true' && user) {
                if (typeof callback === 'function') callback();
            } else {
                window.location.href = '/portal/login.html';
            }
        });
}

// Auto-initialize on DOMContentLoaded
function bootPortalNav() {
    const path = window.location.pathname;
    let currentPage = 'dashboard';
    if (path.includes('documents')) currentPage = 'documents';
    else if (path.includes('tickets')) currentPage = 'tickets';
    else if (path.includes('support')) currentPage = 'support';
    else if (path.includes('settings')) currentPage = 'settings';
    initPortalNav(currentPage);
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function() {
        ensurePortalAuth(bootPortalNav);
    });
} else {
    ensurePortalAuth(bootPortalNav);
}


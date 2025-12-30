// NHUTIN Admin Panel - Navigation
// Màu xanh dương chủ đạo

const adminConfig = {
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
            title: 'Quản lý',
            items: [
                { 
                    id: 'customers', 
                    icon: 'users', 
                    label: 'Khách hàng', 
                    href: 'customers.html'
                },
                { 
                    id: 'users', 
                    icon: 'user', 
                    label: 'Người dùng', 
                    href: 'users.html'
                },
                { 
                    id: 'documents', 
                    icon: 'file-text', 
                    label: 'Tài liệu', 
                    href: 'documents.html'
                },
                { 
                    id: 'posts', 
                    icon: 'edit', 
                    label: 'Bài viết', 
                    href: 'posts.html'
                }
            ]
        },
        {
            title: 'Hệ thống',
            items: [
                { 
                    id: 'tickets', 
                    icon: 'headphones', 
                    label: 'Tickets', 
                    href: 'tickets.html',
                    badge: 5
                },
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
    'users': '<path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>',
    'user': '<path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>',
    'file-text': '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>',
    'edit': '<path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>',
    'headphones': '<path stroke-linecap="round" stroke-linejoin="round" d="M3 18v-6a9 9 0 0118 0v6M3 18a3 3 0 003 3h0a3 3 0 003-3v-3a3 3 0 00-3-3h0a3 3 0 00-3 3v3zm18 0a3 3 0 01-3 3h0a3 3 0 01-3-3v-3a3 3 0 013-3h0a3 3 0 013 3v3z"/>',
    'settings': '<path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>'
};

function getIcon(name) {
    return `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">${icons[name] || icons['file-text']}</svg>`;
}

// Render menu
function renderAdminMenu(currentPage) {
    const navMenu = document.getElementById('navMenu');
    if (!navMenu) return;

    navMenu.innerHTML = adminConfig.menu.map(section => `
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
    adminConfig.menu.forEach(section => {
        section.items.forEach(item => allItems.push(item));
    });

    mobileNav.innerHTML = allItems.slice(0, 5).map(item => {
        const isActive = currentPage === item.id || window.location.pathname.includes(item.href);
        return `
            <a href="${item.href}" class="mobile-nav-item ${isActive ? 'active' : ''}">
                ${getIcon(item.icon)}
                <span>${item.label}</span>
            </a>
        `;
    }).join('');
}

// Update user info
function updateAdminInfo() {
    const adminData = JSON.parse(localStorage.getItem('nhutin_admin') || '{"name":"Admin","email":"admin@nhutin.vn"}');
    
    const userNameEl = document.getElementById('userName');
    const userEmailEl = document.getElementById('userEmail');
    const userAvatarEl = document.getElementById('userAvatar');
    
    if (userNameEl) userNameEl.textContent = adminData.name || 'Admin';
    if (userEmailEl) userEmailEl.textContent = adminData.email || '';
    if (userAvatarEl && !userAvatarEl.src.includes('pravatar')) {
        userAvatarEl.src = 'https://i.pravatar.cc/150?img=8';
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
    window.location.href = 'login.html';
}

// Initialize admin navigation
function initAdminNav(currentPage) {
    renderAdminMenu(currentPage);
    renderMobileNav(currentPage);
    updateAdminInfo();
    
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

// Auto-initialize on DOMContentLoaded
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        const path = window.location.pathname;
        let currentPage = 'dashboard';
        if (path.includes('customers')) currentPage = 'customers';
        else if (path.includes('users')) currentPage = 'users';
        else if (path.includes('documents')) currentPage = 'documents';
        else if (path.includes('posts')) currentPage = 'posts';
        else if (path.includes('tickets')) currentPage = 'tickets';
        else if (path.includes('settings')) currentPage = 'settings';
        
        initAdminNav(currentPage);
    });
} else {
    const path = window.location.pathname;
    let currentPage = 'dashboard';
    if (path.includes('customers')) currentPage = 'customers';
    else if (path.includes('users')) currentPage = 'users';
    else if (path.includes('documents')) currentPage = 'documents';
    else if (path.includes('posts')) currentPage = 'posts';
    else if (path.includes('tickets')) currentPage = 'tickets';
    else if (path.includes('settings')) currentPage = 'settings';
    
    initAdminNav(currentPage);
}




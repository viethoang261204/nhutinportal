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
        }
    ]
};

// Icon SVGs
const icons = {
    'home': '<path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>',
    'file-text': '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>',
    'headphones': '<path stroke-linecap="round" stroke-linejoin="round" d="M3 18v-6a9 9 0 0118 0v6M3 18a3 3 0 003 3h0a3 3 0 003-3v-3a3 3 0 00-3-3h0a3 3 0 00-3 3v3zm18 0a3 3 0 01-3 3h0a3 3 0 01-3-3v-3a3 3 0 013-3h0a3 3 0 013 3v3z"/>',
    'user': '<path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>'
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

// Update user info
function updateUserInfo() {
    const userData = JSON.parse(localStorage.getItem('nhutin_user') || '{"name":"Khách hàng","email":"customer@example.com"}');
    
    const userNameEl = document.getElementById('userName');
    const userEmailEl = document.getElementById('userEmail');
    const userAvatarEl = document.getElementById('userAvatar');
    
    if (userNameEl) userNameEl.textContent = userData.name || 'Khách hàng';
    if (userEmailEl) userEmailEl.textContent = userData.email || '';
    if (userAvatarEl && !userAvatarEl.src.includes('pravatar')) {
        userAvatarEl.src = 'https://i.pravatar.cc/150?img=3';
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
    window.location.href = 'login.html';
}

// Initialize portal navigation
function initPortalNav(currentPage) {
    renderPortalMenu(currentPage);
    renderMobileNav(currentPage);
    updateUserInfo();
    
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
        // Auto-detect current page from URL
        const path = window.location.pathname;
        let currentPage = 'dashboard';
        if (path.includes('documents')) currentPage = 'documents';
        else if (path.includes('tickets')) currentPage = 'tickets';
        else if (path.includes('support')) currentPage = 'support';
        
        initPortalNav(currentPage);
    });
} else {
    // Already loaded
    const path = window.location.pathname;
    let currentPage = 'dashboard';
    if (path.includes('documents')) currentPage = 'documents';
    else if (path.includes('tickets')) currentPage = 'tickets';
    else if (path.includes('support')) currentPage = 'support';
    
    initPortalNav(currentPage);
}


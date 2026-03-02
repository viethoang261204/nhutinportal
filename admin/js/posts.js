(() => {
    const apiUrl = './api/posts.php';
    const defaultThumbnail = 'https://images.unsplash.com/photo-1586528116311-ad8dd3c8310d?w=800&h=400&fit=crop';

    const state = {
        posts: [],
        page: 1,
        limit: 10,
        total: 0,
        totalPages: 1,
        search: '',
        status: '',
        category: '',
        categories: [],
        editingPostId: null,
        deletingPostId: null,
        loading: false,
    };

    const dom = {
        postsGrid: document.getElementById('postsGrid'),
        postsSubtitle: document.getElementById('postsSubtitle'),
        addPostBtn: document.getElementById('addPostBtn'),
        postSearch: document.getElementById('postSearch'),
        statusFilter: document.getElementById('statusFilter'),
        categoryFilter: document.getElementById('categoryFilter'),
        paginationInfo: document.getElementById('paginationInfo'),
        limitSelect: document.getElementById('limitSelect'),
        prevPageBtn: document.getElementById('prevPageBtn'),
        nextPageBtn: document.getElementById('nextPageBtn'),
        pageInfo: document.getElementById('pageInfo'),

        postFormModal: document.getElementById('postFormModal'),
        postModalTitle: document.getElementById('postModalTitle'),
        postForm: document.getElementById('postForm'),
        postFormError: document.getElementById('postFormError'),
        postId: document.getElementById('postId'),
        postTitle: document.getElementById('postTitle'),
        postCategory: document.getElementById('postCategory'),
        postStatus: document.getElementById('postStatus'),
        postThumbnail: document.getElementById('postThumbnail'),
        postThumbnailFile: document.getElementById('postThumbnailFile'),
        chooseThumbnailBtn: document.getElementById('chooseThumbnailBtn'),
        clearThumbnailBtn: document.getElementById('clearThumbnailBtn'),
        postThumbnailPreview: document.getElementById('postThumbnailPreview'),
        postExcerpt: document.getElementById('postExcerpt'),
        postContent: document.getElementById('postContent'),
        closePostModalBtn: document.getElementById('closePostModalBtn'),
        cancelPostBtn: document.getElementById('cancelPostBtn'),

        deletePostModal: document.getElementById('deletePostModal'),
        deletePostText: document.getElementById('deletePostText'),
        cancelDeletePostBtn: document.getElementById('cancelDeletePostBtn'),
        confirmDeletePostBtn: document.getElementById('confirmDeletePostBtn'),

        postToast: document.getElementById('postToast'),
    };

    function escapeHtml(value) {
        const text = String(value ?? '');
        return text
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');
    }

    function formatDate(dateString) {
        if (!dateString) return '--';
        const date = new Date(dateString.replace(' ', 'T'));
        if (Number.isNaN(date.getTime())) return '--';
        return date.toLocaleDateString('vi-VN');
    }

    function statusLabel(status) {
        return status === 'published' ? 'Published' : 'Draft';
    }

    function showToast(message, isError = false) {
        if (!dom.postToast) return;
        dom.postToast.textContent = message || '';
        dom.postToast.classList.toggle('error', Boolean(isError));
        dom.postToast.classList.add('show');
        window.setTimeout(() => {
            dom.postToast.classList.remove('show');
        }, 2500);
    }

    function setFormError(message) {
        if (!dom.postFormError) return;
        dom.postFormError.textContent = message || '';
        dom.postFormError.classList.toggle('show', Boolean(message));
    }

    function setThumbnailPreview(url) {
        const value = String(url || '').trim();
        dom.postThumbnailPreview.src = value || defaultThumbnail;
    }

    function updateSubtitle() {
        if (dom.postsSubtitle) {
            dom.postsSubtitle.textContent = `Tổng số: ${state.total} bài viết`;
        }
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

    function renderCategoryFilter() {
        const selected = state.category;
        const options = ['<option value="">Tất cả danh mục</option>'];
        state.categories.forEach((category) => {
            const isSelected = selected === category ? ' selected' : '';
            options.push(`<option value="${escapeHtml(category)}"${isSelected}>${escapeHtml(category)}</option>`);
        });
        dom.categoryFilter.innerHTML = options.join('');
    }

    function renderPosts() {
        if (state.loading) {
            dom.postsGrid.innerHTML = '<div class="empty-card">Đang tải dữ liệu...</div>';
            return;
        }

        if (!state.posts.length) {
            dom.postsGrid.innerHTML = '<div class="empty-card">Không có bài viết nào phù hợp.</div>';
            return;
        }

        dom.postsGrid.innerHTML = state.posts.map((post) => {
            const thumbnail = post.thumbnail_url || defaultThumbnail;
            return `
                <div class="post-card">
                    <img src="${escapeHtml(thumbnail)}" alt="${escapeHtml(post.title)}" class="post-thumbnail">
                    <div class="post-content">
                        <div class="post-header">
                            <span class="post-category">${escapeHtml(post.category || 'Chưa phân loại')}</span>
                            <span class="post-status ${escapeHtml(post.status)}">${statusLabel(post.status)}</span>
                        </div>
                        <h3 class="post-title">${escapeHtml(post.title)}</h3>
                        <p class="post-excerpt">${escapeHtml(post.excerpt || 'Chưa có mô tả')}</p>
                        <div class="post-meta">
                            <div class="post-meta-item">
                                <svg viewBox="0 0 24 24"><path d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                ${escapeHtml(formatDate(post.published_at || post.created_at))}
                            </div>
                            <div class="post-meta-item">
                                <svg viewBox="0 0 24 24"><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                ${Number(post.view_count || 0).toLocaleString('vi-VN')} lượt xem
                            </div>
                        </div>
                        <div class="post-actions">
                            <button class="post-btn btn-edit" data-action="edit" data-id="${post.id}" type="button">Chỉnh sửa</button>
                            <button class="post-btn btn-delete" data-action="delete" data-id="${post.id}" type="button">Xóa</button>
                        </div>
                    </div>
                </div>
            `;
        }).join('');
    }

    function buildQuery() {
        const params = new URLSearchParams();
        params.set('page', String(state.page));
        params.set('limit', String(state.limit));
        if (state.search) params.set('search', state.search);
        if (state.status) params.set('status', state.status);
        if (state.category) params.set('category', state.category);
        return params.toString();
    }

    async function requestJson(url, options = {}) {
        const response = await fetch(url, {
            credentials: 'same-origin',
            ...options,
        });
        const text = await response.text();
        let json;
        try {
            json = text ? JSON.parse(text) : {};
        } catch (error) {
            throw new Error('Phản hồi từ server không hợp lệ.');
        }
        if (!response.ok || !json.success) {
            throw new Error(json.message || 'Có lỗi xảy ra.');
        }
        return json;
    }

    async function loadPosts() {
        state.loading = true;
        renderPosts();
        try {
            const json = await requestJson(`${apiUrl}?${buildQuery()}`);
            const data = Array.isArray(json.data) ? json.data : [];
            state.posts = data;
            state.total = Number(json.pagination?.total || data.length);
            state.totalPages = Math.max(1, Number(json.pagination?.total_pages || 1));

            const categorySet = new Set(state.categories);
            data.forEach((post) => {
                if (post.category) categorySet.add(String(post.category));
            });
            state.categories = Array.from(categorySet).sort((a, b) => a.localeCompare(b, 'vi'));
            renderCategoryFilter();
        } catch (error) {
            state.posts = [];
            showToast(error.message || 'Không thể tải bài viết.', true);
        } finally {
            state.loading = false;
            renderPosts();
            updatePagination();
            updateSubtitle();
        }
    }

    function openFormModal(post = null) {
        state.editingPostId = post ? Number(post.id) : null;
        setFormError('');
        dom.postModalTitle.textContent = post ? 'Chỉnh sửa bài viết' : 'Thêm bài viết';
        dom.postId.value = post ? String(post.id) : '';
        dom.postTitle.value = post?.title || '';
        dom.postCategory.value = post?.category || '';
        dom.postStatus.value = post?.status || 'published';
        dom.postThumbnail.value = post?.thumbnail_url || '';
        dom.postExcerpt.value = post?.excerpt || '';
        dom.postContent.value = post?.content || '';
        setThumbnailPreview(dom.postThumbnail.value);
        if (dom.postThumbnailFile) dom.postThumbnailFile.value = '';
        dom.postFormModal.classList.add('show');
    }

    function closeFormModal() {
        dom.postFormModal.classList.remove('show');
        state.editingPostId = null;
        dom.postForm.reset();
        setFormError('');
        setThumbnailPreview('');
    }

    function openDeleteModal(post) {
        state.deletingPostId = Number(post.id);
        dom.deletePostText.textContent = `Bạn có chắc chắn muốn xóa bài viết "${post.title}" không?`;
        dom.deletePostModal.classList.add('show');
    }

    function closeDeleteModal() {
        state.deletingPostId = null;
        dom.deletePostModal.classList.remove('show');
    }

    function collectFormPayload() {
        return {
            id: Number(dom.postId.value || 0),
            title: dom.postTitle.value.trim(),
            category: dom.postCategory.value.trim(),
            status: dom.postStatus.value,
            thumbnail_url: dom.postThumbnail.value.trim(),
            excerpt: dom.postExcerpt.value.trim(),
            content: dom.postContent.value.trim(),
        };
    }

    async function uploadThumbnailFile(file) {
        const formData = new FormData();
        formData.append('thumbnail', file);
        const response = await fetch(`${apiUrl}?action=upload_thumbnail`, {
            method: 'POST',
            credentials: 'same-origin',
            body: formData,
        });

        let payload;
        try {
            payload = await response.json();
        } catch (error) {
            throw new Error('Upload ảnh thất bại, phản hồi không hợp lệ.');
        }

        if (!response.ok || !payload.success) {
            throw new Error(payload.message || 'Không thể tải ảnh lên.');
        }

        return String(payload.thumbnail_url || '');
    }

    async function submitForm(event) {
        event.preventDefault();
        setFormError('');
        const payload = collectFormPayload();

        if (!payload.title) {
            setFormError('Vui lòng nhập tiêu đề bài viết.');
            dom.postTitle.focus();
            return;
        }
        if (!payload.category) {
            setFormError('Vui lòng nhập danh mục bài viết.');
            dom.postCategory.focus();
            return;
        }

        const isEdit = payload.id > 0;
        try {
            await requestJson(apiUrl, {
                method: isEdit ? 'PUT' : 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload),
            });
            showToast(isEdit ? 'Cập nhật bài viết thành công.' : 'Tạo bài viết thành công.');
            closeFormModal();
            await loadPosts();
        } catch (error) {
            setFormError(error.message || 'Không thể lưu bài viết.');
        }
    }

    async function confirmDeletePost() {
        if (!state.deletingPostId) return;
        try {
            await requestJson(apiUrl, {
                method: 'DELETE',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: state.deletingPostId }),
            });
            showToast('Đã xóa bài viết.');
            closeDeleteModal();
            if (state.page > 1 && state.posts.length === 1) {
                state.page -= 1;
            }
            await loadPosts();
        } catch (error) {
            showToast(error.message || 'Không thể xóa bài viết.', true);
        }
    }

    async function handlePostActionClick(event) {
        const target = event.target.closest('[data-action]');
        if (!target) return;
        const id = Number(target.getAttribute('data-id') || 0);
        if (!id) return;
        const post = state.posts.find((item) => Number(item.id) === id);
        if (!post) return;

        const action = target.getAttribute('data-action');
        if (action === 'edit') {
            openFormModal(post);
            return;
        }
        if (action === 'delete') {
            openDeleteModal(post);
        }
    }

    function debounce(callback, delayMs = 350) {
        let timer = 0;
        return (...args) => {
            window.clearTimeout(timer);
            timer = window.setTimeout(() => callback(...args), delayMs);
        };
    }

    function initEvents() {
        dom.addPostBtn.addEventListener('click', () => openFormModal(null));
        dom.closePostModalBtn.addEventListener('click', closeFormModal);
        dom.cancelPostBtn.addEventListener('click', closeFormModal);
        dom.postFormModal.addEventListener('click', (event) => {
            if (event.target === dom.postFormModal) closeFormModal();
        });
        dom.postForm.addEventListener('submit', submitForm);
        dom.chooseThumbnailBtn.addEventListener('click', () => dom.postThumbnailFile.click());
        dom.clearThumbnailBtn.addEventListener('click', () => {
            dom.postThumbnail.value = '';
            if (dom.postThumbnailFile) dom.postThumbnailFile.value = '';
            setThumbnailPreview(defaultThumbnail);
        });
        dom.postThumbnailFile.addEventListener('change', async () => {
            const file = dom.postThumbnailFile.files && dom.postThumbnailFile.files[0];
            if (!file) return;
            setFormError('');
            try {
                const thumbnailUrl = await uploadThumbnailFile(file);
                dom.postThumbnail.value = thumbnailUrl;
                setThumbnailPreview(thumbnailUrl);
                showToast('Tải ảnh thumbnail thành công.');
            } catch (error) {
                dom.postThumbnail.value = '';
                setThumbnailPreview(defaultThumbnail);
                setFormError(error.message || 'Không thể tải ảnh lên.');
            }
        });

        dom.postsGrid.addEventListener('click', handlePostActionClick);

        dom.cancelDeletePostBtn.addEventListener('click', closeDeleteModal);
        dom.deletePostModal.addEventListener('click', (event) => {
            if (event.target === dom.deletePostModal) closeDeleteModal();
        });
        dom.confirmDeletePostBtn.addEventListener('click', confirmDeletePost);

        dom.postSearch.addEventListener('input', debounce(async (event) => {
            state.search = event.target.value.trim();
            state.page = 1;
            await loadPosts();
        }, 400));

        dom.statusFilter.addEventListener('change', async (event) => {
            state.status = String(event.target.value || '');
            state.page = 1;
            await loadPosts();
        });

        dom.categoryFilter.addEventListener('change', async (event) => {
            state.category = String(event.target.value || '');
            state.page = 1;
            await loadPosts();
        });

        if (dom.limitSelect) {
            state.limit = Math.max(1, parseInt(dom.limitSelect.value, 10)) || 10;
            dom.limitSelect.addEventListener('change', async () => {
                state.limit = Math.max(1, parseInt(dom.limitSelect.value, 10)) || 10;
                state.page = 1;
                await loadPosts();
            });
        }

        dom.prevPageBtn.addEventListener('click', async () => {
            if (state.page <= 1) return;
            state.page -= 1;
            await loadPosts();
        });

        dom.nextPageBtn.addEventListener('click', async () => {
            if (state.page >= state.totalPages) return;
            state.page += 1;
            await loadPosts();
        });
    }

    async function init() {
        initEvents();
        setThumbnailPreview('');
        await loadPosts();
    }

    void init();
})();

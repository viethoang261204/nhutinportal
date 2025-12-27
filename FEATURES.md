# NHUTIN - Tài liệu Tính năng Hệ thống

## 📋 Tổng quan Phân quyền

| Loại người dùng | Mô tả | Quyền truy cập |
|----------------|-------|---------------|
| **Người dùng thường** | Khách vãng lai truy cập website | Chỉ đọc thông tin công khai |
| **Portal - Khách hàng** | Khách hàng đã đăng ký tài khoản | Xem tài liệu riêng, tạo ticket, liên hệ CSKH |
| **Admin** | Quản trị viên hệ thống | Full quyền: CRUD khách hàng, users, tài liệu, tickets, settings |

---

## 🌐 WEBSITE CÔNG KHAI (Người dùng thường)

### 📄 Danh sách Trang

| Tên trang | URL | Mô tả | Tính năng chính |
|-----------|-----|-------|-----------------|
| **Trang chủ** | `index.html` | Landing page | • Banner lớn với gradient xanh<br>• 4 feature cards (layout 1-3)<br>• Giới thiệu sản phẩm (2 cards)<br>• Stats (4 số liệu)<br>• Video intro<br>• Blog preview (3 bài) |
| **Giới thiệu** | `about.html` | Về công ty NHUTIN | • Lịch sử công ty<br>• Đội ngũ<br>• Giá trị cốt lõi |
| **Sản phẩm** | `products.html` | Danh mục sản phẩm | • Nhiên liệu sinh khối bã điều<br>• Sàn trượt tự đổ (Walking Floor)<br>• Thông số kỹ thuật<br>• Hình ảnh sản phẩm |
| **Tin tức** | `news.html` | Blog & cập nhật | • Kiến thức ngành<br>• Tin tức công ty<br>• Hướng dẫn sử dụng |
| **Liên hệ** | `contact.html` | Thông tin liên hệ | • Form liên hệ<br>• Google Maps<br>• Thông tin công ty |

### 🎨 Tính năng UI/UX

| Tính năng | Chi tiết |
|-----------|----------|
| **Navigation** | • Navbar fixed top với blur effect<br>• Logo + menu links<br>• CTA button "Liên hệ"<br>• Hamburger menu responsive |
| **Banner chính** | • Background gradient xanh (#0b3d35)<br>• Tiêu đề lớn "Nhu Tin corp"<br>• Subtitle mô tả công ty<br>• 2 CTA buttons<br>• Layout: 1 card lớn trái + 3 cards nhỏ phải<br>• Icons SVG + hover effects |
| **Animations** | • Reveal on scroll (data-reveal)<br>• Hover transforms<br>• Gradient backgrounds<br>• Blur effects |
| **Responsive** | • Desktop (>980px)<br>• Tablet (768-980px)<br>• Mobile (<768px)<br>• Mobile drawer menu |
| **Footer** | • 3 cột thông tin<br>• Logo + links<br>• Copyright |

---

## 🔐 PORTAL KHÁCH HÀNG

### 📄 Danh sách Trang

| Tên trang | URL | Quyền truy cập | Tính năng chính |
|-----------|-----|----------------|-----------------|
| **Login** | `portal/login.html` | Public | • Form đăng nhập<br>• Email/Mã KH + Password<br>• Remember me<br>• Forgot password<br>• 2-column layout với features |
| **Register** | `portal/register.html` | Public | • Form đăng ký khách hàng mới<br>• Thông tin công ty<br>• Email verification |
| **Dashboard** | `portal/dashboard.html` | Authenticated | • Thống kê cá nhân (4 stats)<br>• Thông tin khách hàng<br>• CSKH phụ trách<br>• Quick actions |
| **Tài liệu** | `portal/documents.html` | Authenticated | • Danh sách tài liệu cá nhân<br>• Filter theo loại (Invoice, Packing, Certificate, BOL)<br>• Search & filter<br>• Download/View files<br>• Grid layout với icons |
| **Tickets** | `portal/tickets.html` | Authenticated | • Tạo ticket mới<br>• Xem danh sách tickets<br>• Filter theo trạng thái (Open, Progress, Resolved)<br>• Theo dõi tiến độ xử lý<br>• Priority badges<br>• Comments/replies |
| **Liên hệ tư vấn** | `portal/support.html` | Authenticated | • Thông tin CSKH phụ trách<br>• Avatar + contact info<br>• Call/Email/Zalo buttons<br>• FAQ section<br>• Form yêu cầu tư vấn |

### 🎯 Menu Portal (Giống nhau cho TẤT CẢ khách hàng)

```
📊 Tổng quan
  └── Dashboard

📁 Tài liệu & Hỗ trợ
  ├── 📄 Tài liệu
  ├── 🎫 Ticket / Hỗ trợ
  └── 👤 Liên hệ tư vấn
```

### 📊 Dashboard Stats

| Stat | Mô tả | Icon |
|------|-------|------|
| Tài liệu | Số lượng tài liệu khả dụng | 📄 |
| Ticket đang mở | Tickets chưa giải quyết | 🎫 |
| Ticket đã giải quyết | Lịch sử tickets | ✅ |
| CSKH hỗ trợ | Số nhân viên phụ trác | 👤 |

### 🎨 Theme Portal

| Element | Color Code | Mô tả |
|---------|-----------|-------|
| Background | `#E8F3EC` | Xanh lá nhạt |
| Primary | `#2D4A3E` | Xanh lá đậm |
| Sidebar Gradient | `#2D4A3E → #243D33` | Gradient xanh lá |
| Hover/Focus | `rgba(45,74,62,0.1)` | Xanh lá mờ |
| Cards | `#FFFFFF` | Trắng với shadow |
| Border Radius | `10-16px` | Bo góc mềm mại |

---

## 👨‍💼 ADMIN PANEL

### 📄 Danh sách Trang

| Tên trang | URL | Quyền truy cập | Tính năng chính |
|-----------|-----|----------------|-----------------|
| **Admin Login** | `admin/login.html` | Public | • Form đăng nhập admin<br>• 2FA option<br>• Security warning<br>• Admin badge<br>• Gradient xanh dương |
| **Dashboard** | `admin/dashboard.html` | Admin only | • 4 stats cards (Customers, Documents, Tickets, Users)<br>• Trend indicators (↑↓)<br>• Bảng khách hàng mới nhất<br>• Quick actions<br>• 2-column layout |
| **Quản lý Khách hàng** | `admin/customers.html` | Admin only | • Table view với pagination<br>• Search & multi-filters<br>• Avatar + thông tin chi tiết<br>• Status badges<br>• Actions: View/Edit/Delete<br>• Bulk actions |
| **Quản lý Users** | `admin/users.html` | Admin only | • Card grid layout<br>• User avatars<br>• Role badges (Admin, Support, Sales)<br>• Email & phone display<br>• Active/Inactive status<br>• Edit/Settings actions |
| **Quản lý Tài liệu** | `admin/documents.html` | Admin only | • Upload tài liệu mới<br>• Phân loại theo type<br>• Assign cho khách hàng<br>• Download/View/Delete<br>• File icons theo loại<br>• Search & filter |
| **Quản lý Tickets** | `admin/tickets.html` | Admin only | • Danh sách tickets từ khách hàng<br>• Stats (Open/Progress/Resolved)<br>• Tabs filter theo status<br>• Priority badges (High/Medium/Low)<br>• Assign cho staff<br>• Reply & resolve tickets |
| **Cài đặt** | `admin/settings.html` | Admin only | • Cài đặt chung (Thông tin công ty)<br>• System config (Email notif, Auto-assign)<br>• Security (2FA, Session timeout)<br>• Danger zone (Reset, Delete DB) |

### 🎯 Menu Admin

```
📊 Tổng quan
  └── Dashboard

👥 Quản lý
  ├── Khách hàng (248 KH)
  ├── Người dùng (15 users)
  └── Tài liệu (1,456 files)

⚙️ Hệ thống
  ├── Tickets (🔴 5 pending)
  └── Cài đặt
```

### 📊 Dashboard Admin - Stats Chi tiết

| Stat Card | Giá trị mẫu | Trend | Màu | Ý nghĩa |
|-----------|-------------|-------|-----|---------|
| **Tổng khách hàng** | 248 | ↑ 12% | Xanh dương | Số lượng khách hàng active |
| **Tài liệu** | 1,456 | ↑ 8% | Xanh lá | Tổng files trong hệ thống |
| **Tickets đang mở** | 23 | ↓ 5 | Cam | Tickets chưa giải quyết |
| **Users hoạt động** | 15 | ↑ 3 | Tím | Staff đang online |

### 🎨 Theme Admin

| Element | Color Code | Mô tả |
|---------|-----------|-------|
| Background | `#EFF6FF` | Xanh dương rất nhạt |
| Primary | `#1E40AF` | Xanh dương đậm |
| Secondary | `#3B82F6` | Xanh dương sáng |
| Sidebar Gradient | `#1E40AF → #1E3A8A` | Gradient xanh dương đậm |
| Hover/Focus | `rgba(30,64,175,0.1)` | Xanh dương mờ |
| Success | `#059669` | Xanh lá (resolved) |
| Warning | `#EA580C` | Cam (pending) |
| Danger | `#DC2626` | Đỏ (delete, high priority) |
| Cards | `#FFFFFF` | Trắng với shadow |

---

## 🔧 TECHNICAL STACK

### Frontend

| Technology | Version | Usage |
|------------|---------|-------|
| **HTML5** | - | Structure |
| **CSS3** | - | Styling (No framework, pure CSS) |
| **JavaScript** | ES6+ | Interactive features |
| **Google Fonts** | - | Be Vietnam Pro font family |

### File Organization

| Type | Location | Purpose |
|------|----------|---------|
| **Public Website** | `/` (root) | Trang công khai cho mọi người |
| **Customer Portal** | `/portal/` | Khu vực khách hàng đã đăng ký |
| **Admin Panel** | `/admin/` | Khu vực quản trị viên |
| **Shared Components** | `/components/` | Navbar, Footer chung |
| **Assets** | `/img/`, `/css/`, `/js/` | Images, styles, scripts |

---

## 📊 SO SÁNH TÍNH NĂNG

### Bảng So Sánh Chi Tiết

| Tính năng | Người dùng thường | Portal Khách hàng | Admin |
|-----------|-------------------|-------------------|-------|
| **Xem thông tin công ty** | ✅ Full access | ✅ Full access | ✅ Full access |
| **Xem sản phẩm/dịch vụ** | ✅ Full access | ✅ Full access | ✅ Full access |
| **Liên hệ qua form** | ✅ Public form | ✅ Private form + Assigned CSKH | ✅ View all contacts |
| **Đăng ký tài khoản** | ✅ Có thể đăng ký | ➖ Đã có tài khoản | ❌ Admin tạo sẵn |
| **Đăng nhập** | ❌ Không cần | ✅ Bắt buộc | ✅ Bắt buộc (Admin credentials) |
| **Dashboard** | ❌ Không có | ✅ Personal dashboard | ✅ System-wide dashboard |
| **Xem tài liệu** | ❌ Không có | ✅ Chỉ tài liệu của mình | ✅ Tất cả tài liệu |
| **Tải tài liệu** | ❌ Không có | ✅ Download tài liệu riêng | ✅ Download/Upload/Delete bất kỳ |
| **Tạo ticket** | ❌ Không có | ✅ Tạo & theo dõi | ✅ View/Assign/Resolve tất cả |
| **Xem ticket** | ❌ Không có | ✅ Chỉ ticket của mình | ✅ Tất cả tickets |
| **Reply ticket** | ❌ Không có | ✅ Comment trên ticket | ✅ Reply & resolve |
| **CSKH phụ trách** | ❌ Thông tin chung | ✅ CSKH cá nhân + liên hệ trực tiếp | ✅ Assign CSKH cho khách hàng |
| **Quản lý khách hàng** | ❌ | ❌ | ✅ CRUD customers |
| **Quản lý users** | ❌ | ❌ | ✅ CRUD users |
| **Cài đặt hệ thống** | ❌ | ❌ | ✅ Full system settings |
| **View analytics** | ❌ | ⚠️ Personal stats only | ✅ System-wide analytics |

---

## 🎯 TÍNH NĂNG CHI TIẾT THEO ROLE

### 1️⃣ NGƯỜI DÙNG THƯỜNG (Website Công khai)

#### Trang chủ (index.html)

| Section | Nội dung | Tương tác |
|---------|----------|-----------|
| **Hero Banner** | • Gradient background xanh đậm<br>• Tiêu đề "Nhu Tin corp"<br>• Mô tả công ty<br>• 2 CTA buttons | • Click "Xem sản phẩm" → products.html<br>• Click "Liên hệ ngay" → contact.html |
| **Feature Cards** | • 1 card lớn bên trái: "Tại sao chọn chúng tôi?"<br>• 3 cards nhỏ bên phải:<br>&nbsp;&nbsp;- Tự động hóa xuống hàng<br>&nbsp;&nbsp;- Sinh khối bã điều<br>&nbsp;&nbsp;- Đồng hành kỹ thuật | • Hover effects<br>• Icons animation<br>• Responsive layout |
| **Sản phẩm** | • 2 product cards:<br>&nbsp;&nbsp;1. Nhiên liệu sinh khối bã điều<br>&nbsp;&nbsp;2. Sàn trượt tự đổ (Walking Floor)<br>• Hình ảnh + mô tả + features list | • Click "Xem chi tiết" → products.html<br>• Click "Website" → external links |
| **Stats** | • 50% - Tiết kiệm chi phí<br>• 2010 - Năm thành lập<br>• 5 ngày - Lắp đặt<br>• 24/7 - Hỗ trợ | • Counter animation<br>• Scroll reveal |
| **Video** | • Video intro autoplay<br>• Mô tả quy trình | • Click CTA → contact.html, about.html |
| **Blog Preview** | • 3 bài viết mới nhất<br>• Thumbnail + meta + excerpt | • Click → news.html |

#### Các trang khác

| Trang | Sections | Actions Available |
|-------|----------|-------------------|
| **about.html** | • Hero<br>• Về NHUTIN<br>• Timeline<br>• Team | • Scroll<br>• Read content |
| **products.html** | • Danh mục sản phẩm<br>• Chi tiết kỹ thuật<br>• Hình ảnh<br>• Pricing | • Xem chi tiết<br>• Contact for quote |
| **news.html** | • Blog listing<br>• Categories<br>• Search | • Read articles<br>• Filter by category |
| **contact.html** | • Contact form<br>• Google Maps<br>• Company info | • Submit form<br>• View map |

---

### 2️⃣ PORTAL KHÁCH HÀNG

#### Authentication

| Feature | Chi tiết |
|---------|----------|
| **Đăng nhập** | • Email hoặc Mã KH<br>• Password<br>• Remember me checkbox<br>• Forgot password link<br>• ❌ Đã bỏ phân loại khách hàng |
| **Đăng ký** | • Form đăng ký mới<br>• Email verification<br>• Admin approval (optional) |
| **Session** | • Auto-save to localStorage<br>• Persistent login |

#### Dashboard Features

| Component | Dữ liệu hiển thị | Tương tác |
|-----------|------------------|-----------|
| **Welcome Header** | • Chào tên khách hàng<br>• Subtitle về portal | - |
| **Stats Cards** | 1. Tài liệu (12)<br>2. Ticket đang mở (2)<br>3. Ticket đã giải quyết (8)<br>4. CSKH hỗ trợ (1) | • Click vào stat → navigate to page |
| **Thông tin KH** | • Logo công ty<br>• Tên công ty<br>• CSKH phụ trách<br>• Email & Hotline<br>• Trạng thái | • Static display |
| **Quick Actions** | • Tạo ticket mới<br>• Xem tài liệu<br>• Liên hệ tư vấn<br>• Thông tin CSKH | • Click → navigate |
| **CSKH Card** | • Avatar nhân viên<br>• Tên + SĐT<br>• Call/Email buttons | • Click Call/Email → action |

#### Tài liệu (Documents)

| Feature | Chi tiết | Quyền |
|---------|----------|-------|
| **View** | • Grid layout tài liệu<br>• Icons theo loại file<br>• Thumbnail preview | ✅ Chỉ tài liệu của mình |
| **Filter** | • Tabs: All, Invoice, Packing List, Certificate, BOL<br>• Search box<br>• Date range filter | ✅ |
| **Download** | • Download PDF/Excel/Word<br>• View online | ✅ Unlimited |
| **Upload** | ❌ Không thể upload | ❌ Chỉ admin upload |
| **Delete** | ❌ Không thể xóa | ❌ Chỉ admin xóa |

#### Tickets / Hỗ trợ

| Feature | Chi tiết | Quyền |
|---------|----------|-------|
| **Tạo ticket** | • Form tạo mới<br>• Chọn loại (Technical/General/Billing)<br>• Priority level<br>• Attach files<br>• Detailed description | ✅ Unlimited |
| **View tickets** | • List view<br>• Filter tabs (Open/Progress/Resolved/Closed)<br>• Ticket ID + Title<br>• Status badges<br>• Timeline | ✅ Chỉ tickets của mình |
| **Update ticket** | • Add comments<br>• Upload attachments<br>• Update priority | ✅ |
| **Close ticket** | • Mark as resolved (request) | ✅ (Admin confirm) |
| **Assign** | ❌ Không thể assign | ❌ Chỉ admin assign |

#### Liên hệ tư vấn (Support)

| Component | Nội dung | Tương tác |
|-----------|----------|-----------|
| **CSKH Card** | • Avatar (120x120)<br>• Tên nhân viên<br>• Role & Department<br>• Status (Online/Offline)<br>• Phone + Email + Zalo | • Click Call → tel:<br>• Click Email → mailto:<br>• Click Zalo → open app |
| **Contact Methods** | • 📞 Điện thoại<br>• ✉️ Email<br>• 💬 Zalo<br>• 📍 Địa chỉ | • Quick actions |
| **FAQ** | • Câu hỏi thường gặp<br>• Collapsible items | • Expand/Collapse |
| **Request Form** | • Yêu cầu tư vấn<br>• Chọn dịch vụ quan tâm | • Submit → notify CSKH |

---

### 3️⃣ ADMIN PANEL

#### Dashboard Features

| Component | Dữ liệu | Actions |
|-----------|---------|---------|
| **Stats Overview** | • 248 Customers (↑12%)<br>• 1,456 Documents (↑8%)<br>• 23 Open tickets (↓5)<br>• 15 Active users (↑3) | • Click stat → detail page |
| **Recent Customers Table** | • 10 khách hàng mới nhất<br>• Avatar + Name + Email<br>• Status badges<br>• Created date | • Click row → customer detail |
| **Quick Actions** | • Quản lý khách hàng<br>• Quản lý users<br>• Xử lý tickets<br>• Cài đặt hệ thống | • Navigate to pages |

#### Quản lý Khách hàng (Customers)

| Feature | Chi tiết | Actions |
|---------|----------|---------|
| **List View** | • Table với pagination<br>• 10/25/50/100 per page<br>• Total count | • Sort by column |
| **Search** | • By name/email/phone/ID<br>• Real-time search | ✅ |
| **Filter** | • By status (Active/Pending/Inactive)<br>• By type (Cá nhân/Doanh nghiệp)<br>• By date range | ✅ |
| **Add Customer** | • Form tạo khách hàng mới<br>• Thông tin công ty<br>• Contact details<br>• Assign CSKH | ✅ |
| **Edit Customer** | • Update thông tin<br>• Change status<br>• Reassign CSKH | ✅ |
| **Delete Customer** | • Soft delete<br>• Confirmation modal | ✅ (with warning) |
| **View Details** | • Full profile<br>• Activity history<br>• Documents list<br>• Tickets history | ✅ |
| **Export** | • Export to Excel/CSV<br>• Filtered data | ✅ |

#### Quản lý Users (Staff/Admin)

| Feature | Chi tiết | Actions |
|---------|----------|---------|
| **Grid View** | • Card layout<br>• Avatar + Name + Role<br>• Contact info<br>• Status badge | • Click card → edit |
| **Add User** | • Create new admin/staff<br>• Set role & permissions<br>• Email + Password<br>• Avatar upload | ✅ |
| **Edit User** | • Update info<br>• Change role<br>• Reset password<br>• Enable/Disable account | ✅ |
| **Roles** | • Super Admin (full access)<br>• Admin (most access)<br>• Customer Support (tickets, customers)<br>• Sales Manager (customers, documents) | ✅ Set permissions |
| **Permissions** | • Granular permissions per module<br>• Can view/edit/delete | ✅ Configure |

#### Quản lý Tài liệu (Documents)

| Feature | Chi tiết | Actions |
|---------|----------|---------|
| **Upload** | • Single/Multiple files<br>• PDF, Excel, Word, Images<br>• Max 50MB per file | ✅ |
| **Categorize** | • Invoice<br>• Packing List<br>• Certificate<br>• Bill of Lading<br>• Technical Docs<br>• Other | ✅ Set category |
| **Assign** | • Assign to customer(s)<br>• Set visibility | ✅ |
| **Edit** | • Rename<br>• Change category<br>• Update description | ✅ |
| **Delete** | • Permanent delete<br>• Confirmation required | ✅ |
| **Preview** | • PDF viewer<br>• Image preview | ✅ |
| **Download** | • Original file<br>• Bulk download | ✅ |
| **Search** | • By filename<br>• By customer<br>• By date | ✅ |

#### Quản lý Tickets

| Feature | Chi tiết | Actions |
|---------|----------|---------|
| **View All** | • Tất cả tickets từ khách hàng<br>• Filter tabs (All/Open/Progress/Resolved)<br>• Stats overview | ✅ |
| **Ticket Details** | • Full conversation<br>• Attachments<br>• History timeline<br>• Customer info | ✅ View |
| **Assign** | • Assign to staff<br>• Reassign | ✅ |
| **Reply** | • Add comment/solution<br>• Internal notes<br>• Attach files | ✅ |
| **Change Status** | • Open → In Progress → Resolved → Closed<br>• Reopen if needed | ✅ |
| **Priority** | • Set/Change priority (High/Medium/Low)<br>• SLA tracking | ✅ |
| **Categories** | • Technical Support<br>• General Inquiry<br>• Billing<br>• Documents | ✅ Categorize |
| **Search** | • By ticket ID<br>• By customer<br>• By keyword | ✅ |
| **Export** | • Export ticket report<br>• Analytics | ✅ |

#### Cài đặt Hệ thống (Settings)

| Section | Settings | Actions |
|---------|----------|---------|
| **Cài đặt chung** | • Tên công ty<br>• Email/Phone/Địa chỉ<br>• Logo upload<br>• Timezone | ✅ Update |
| **System Config** | • Email notifications ON/OFF<br>• Auto-assign tickets ON/OFF<br>• Customer self-registration ON/OFF<br>• Maintenance mode ON/OFF | ✅ Toggle switches |
| **Security** | • Two-factor authentication (2FA)<br>• Session timeout (30 min)<br>• Password policy<br>• IP whitelist | ✅ Configure |
| **Admin Password** | • Change admin password<br>• Password strength meter | ✅ Update |
| **Danger Zone** | • ⚠️ Delete all data<br>• ⚠️ Reset system<br>• ⚠️ Factory reset | ✅ (with multiple confirms) |

---

## 🔐 PHÂN QUYỀN CHI TIẾT

### Portal Khách hàng

| Module | Create | Read | Update | Delete |
|--------|--------|------|--------|--------|
| **Tài liệu** | ❌ | ✅ Own only | ❌ | ❌ |
| **Tickets** | ✅ | ✅ Own only | ✅ Own only | ❌ |
| **Profile** | ❌ | ✅ Own only | ✅ Own only | ❌ |
| **CSKH Info** | ❌ | ✅ Assigned only | ❌ | ❌ |

### Admin Panel

| Module | Create | Read | Update | Delete |
|--------|--------|------|--------|--------|
| **Customers** | ✅ | ✅ All | ✅ All | ✅ All |
| **Users** | ✅ | ✅ All | ✅ All | ✅ All |
| **Documents** | ✅ | ✅ All | ✅ All | ✅ All |
| **Tickets** | ✅ | ✅ All | ✅ All | ✅ All |
| **Settings** | - | ✅ | ✅ | - |

---

## 🎨 DESIGN SYSTEM

### Color Palette

| Context | Primary | Secondary | Background | Usage |
|---------|---------|-----------|------------|-------|
| **Website** | `#0b3d35` Xanh đậm | `#22C55E` Xanh lá | `#f6fbff` → `#f4fff9` | Public pages |
| **Portal** | `#2D4A3E` Xanh lá đậm | `#22C55E` | `#E8F3EC` | Customer area |
| **Admin** | `#1E40AF` Xanh dương | `#3B82F6` | `#EFF6FF` | Admin area |

### Typography

| Element | Font Size | Font Weight | Usage |
|---------|-----------|-------------|-------|
| **H1** | 28-42px | 700-900 | Page titles |
| **H2** | 24-38px | 700 | Section titles |
| **H3** | 16-18px | 600-700 | Card titles |
| **Body** | 14px | 400 | Regular text |
| **Small** | 12-13px | 500 | Meta, labels |

### Components

| Component | Specs | Variants |
|-----------|-------|----------|
| **Button** | • Padding: 10-16px<br>• Border radius: 10-12px<br>• Font weight: 600-700 | • Primary (gradient)<br>• Secondary (outline)<br>• Danger (red) |
| **Card** | • Border radius: 14-20px<br>• Padding: 20-40px<br>• Shadow: 0 2px 12px rgba(0,0,0,0.04) | • Default<br>• Padded<br>• Solid |
| **Input** | • Height: 44px<br>• Border: 1.5px<br>• Border radius: 10-12px<br>• Focus: shadow + border color | • Text<br>• Email<br>• Password<br>• Select<br>• Textarea |
| **Badge** | • Padding: 4-6px 10-14px<br>• Border radius: 12-20px<br>• Font size: 11-12px<br>• Font weight: 700 | • Status<br>• Count<br>• Tag |
| **Avatar** | • Sizes: 32px, 40px, 56px, 120px<br>• Border radius: 8-24px | • Small<br>• Medium<br>• Large |

---

## 📱 RESPONSIVE DESIGN

### Breakpoints

| Device | Width | Layout Changes |
|--------|-------|----------------|
| **Desktop** | > 980px | • Full layout<br>• Sidebar visible<br>• Multi-column grids |
| **Tablet** | 768px - 980px | • Sidebar toggleable<br>• 2-column grids<br>• Stacked sections |
| **Mobile** | < 768px | • Hamburger menu<br>• 1-column layout<br>• Bottom nav (portal)<br>• Touch-optimized |

### Mobile Optimizations

| Area | Optimization |
|------|--------------|
| **Navigation** | • Drawer menu<br>• Bottom navigation (portal)<br>• Overlay backdrop |
| **Forms** | • Full-width inputs<br>• Larger touch targets (44px min)<br>• Native keyboard |
| **Tables** | • Horizontal scroll<br>• Card view (alternative) |
| **Images** | • Lazy loading<br>• Responsive sizes |

---

## 🚀 TỔNG KẾT

### Website Công khai
✅ 5 trang chính
✅ Responsive hoàn toàn
✅ Animations & effects
✅ SEO-friendly

### Portal Khách hàng  
✅ 4 trang chức năng (Dashboard, Tài liệu, Tickets, Support)
✅ Authentication system
✅ Personalized experience
✅ ❌ KHÔNG phân loại khách hàng (menu giống nhau)

### Admin Panel
✅ 7 trang quản trị
✅ Full CRUD operations
✅ Stats & analytics
✅ Security features
✅ Màu sắc riêng biệt (xanh dương)

---

**Tổng số trang:** 16 pages
**Tổng số components:** ~30 reusable components
**Supported languages:** Tiếng Việt
**Browser support:** Modern browsers (Chrome, Firefox, Safari, Edge)


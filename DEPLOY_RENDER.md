# Hướng dẫn deploy NHUTIN Portal lên Render

## Chuẩn bị

### 1. Đẩy code lên GitHub

```bash
cd nhutinportal
git init
git add .
git commit -m "Initial commit"
git branch -M main
git remote add origin https://github.com/YOUR_USERNAME/nhutinportal.git
git push -u origin main
```

Thay `YOUR_USERNAME` bằng username GitHub của bạn.

---

### 2. Tạo MySQL Database trên Render

1. Vào [Render Dashboard](https://dashboard.render.com) → **New +** → tìm **MySQL** (hoặc **Deploy MySQL** / dùng [render.com/deploy-docker/mysql](https://render.com/deploy-docker/mysql))
2. Deploy từ repo [render-examples/mysql](https://github.com/render-examples/mysql) hoặc dùng MySQL Docker:
   - **Name**: `nhutin-db`
   - **Environment Variables**: `MYSQL_DATABASE`, `MYSQL_USER`, `MYSQL_PASSWORD`, `MYSQL_ROOT_PASSWORD`
   - Gắn **Persistent Disk** tại `/var/lib/mysql` (tối thiểu 10GB)
3. **Create** và đợi deploy
4. Trong Dashboard, mở MySQL service → **Info** → lấy **Internal URL** dạng:
   ```
   mysql://user:password@nhutin-db:3306/nhutin_portal
   ```
5. Host cho PHP sẽ là: **tên service** (vd: `nhutin-db`) – Render dùng private networking

**Hoặc dùng PlanetScale (MySQL free):**
- Đăng ký [PlanetScale](https://planetscale.com) → tạo database
- Lấy host, user, password từ **Connect** → **Connect with: PHP**
- Dùng các giá trị này cho Environment Variables

---

### 3. Tạo Web Service (PHP) trên Render

1. **New +** → **Web Service**
2. Kết nối repo GitHub:
   - Chọn **Build and deploy from a Git repository**
   - Connect GitHub (nếu chưa) → chọn repo `nhutinportal`
3. Cấu hình:
   - **Name**: `nhutin-portal`
   - **Region**: Singapore hoặc Oregon (cùng region với DB)
   - **Branch**: `main`
   - **Root Directory**: để trống (project ở root)
   - **Runtime**: **Docker**
   - **Dockerfile Path**: `Dockerfile` (mặc định)
4. **Instance Type**: Free
5. **Environment Variables** – thêm (phải trùng với MySQL đã tạo):

   | Key          | Value                                      |
   |--------------|---------------------------------------------|
   | `DB_HOST`    | Tên MySQL service (vd: `nhutin-db`) hoặc host từ PlanetScale |
   | `DB_PORT`    | `3306`                                     |
   | `DB_NAME`    | `nhutin_portal` (trùng `MYSQL_DATABASE`)    |
   | `DB_USER`    | User từ MySQL                               |
   | `DB_PASSWORD`| Password từ MySQL                          |

   > **Lưu ý**: PHP Web Service và MySQL phải cùng 1 **Workspace** trên Render thì mới dùng được internal hostname.

6. **Create Web Service**
7. Đợi build và deploy (khoảng 5–10 phút)

---

### 4. Import database schema

Sau khi MySQL chạy:

1. Export schema từ MySQL local:
   ```bash
   mysqldump -u root -p --no-data nhutin_portal > schema.sql
   ```

2. Import lên Render MySQL (dùng connection string từ bước 2):
   ```bash
   mysql -h HOST -P 3306 -u USER -p nhutin_portal < schema.sql
   ```

Hoặc dùng MySQL client (DBeaver, HeidiSQL...) để import `schema.sql`.

3. Tạo admin user (chạy trong MySQL):
   ```sql
   INSERT INTO users (email, password_hash, role, name, ...) 
   VALUES ('admin@nhutin.vn', 'hash_cua_mat_khau', 'admin', 'Admin', ...);
   ```
   (Điều chỉnh theo cấu trúc bảng `users` trong DB của bạn.)

---

### 5. Kiểm tra sau deploy

- Trang chủ: `https://nhutin-portal.onrender.com`
- Admin: `https://nhutin-portal.onrender.com/admin/`
- Portal: `https://nhutin-portal.onrender.com/portal/`

---

### 6. Gắn tên miền (tùy chọn)

1. Vào Web Service → **Settings** → **Custom Domains**
2. Thêm domain (vd: `portal.nhutincompany.com`)
3. Thêm CNAME trỏ tới `nhutin-portal.onrender.com` trong DNS provider

---

## Lưu ý

- **Free tier**: service sẽ sleep sau 15 phút không có request, lần request đầu tiên có thể chậm.
- **Session**: Render không share disk giữa các instance, session lưu file có thể mất khi restart. Có thể chuyển sang lưu session trong database.
- **MySQL trên Render**: Kiểm tra [Render Docs – MySQL](https://render.com/docs/deploy-mysql). Nếu chỉ có PostgreSQL, cân nhắc dùng PlanetScale (MySQL) hoặc chuyển schema sang PostgreSQL.

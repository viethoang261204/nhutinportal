-- NHUTIN Portal schema for PlanetScale (Vitess-safe)
-- Không dùng CREATE DATABASE / USE
-- Không dùng FOREIGN KEY / TRIGGER / PROCEDURE / VIEW

SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS admins (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(255) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  avatar_url VARCHAR(500) NULL,
  role VARCHAR(50) NOT NULL DEFAULT 'admin',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS admin_login_attempts (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  identifier CHAR(64) NOT NULL,
  is_success TINYINT(1) NOT NULL DEFAULT 0,
  attempted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_identifier_time (identifier, attempted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS customer_types (
  id INT AUTO_INCREMENT PRIMARY KEY,
  code VARCHAR(50) NOT NULL UNIQUE,
  name VARCHAR(100) NOT NULL,
  description TEXT NULL,
  is_active TINYINT(1) DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS customers (
  id INT AUTO_INCREMENT PRIMARY KEY,
  customer_code VARCHAR(50) NULL,
  company_name VARCHAR(255) NOT NULL,
  tax_code VARCHAR(50) NULL,
  customer_type_id INT NOT NULL DEFAULT 1,
  address TEXT NULL,
  phone VARCHAR(20) NULL,
  email VARCHAR(255) NULL,
  contact_person VARCHAR(100) NULL,
  position VARCHAR(100) NULL,
  logo_url VARCHAR(500) NULL,
  status ENUM('pending','active','inactive','suspended') NOT NULL DEFAULT 'pending',
  assigned_staff_id INT NULL,
  notes TEXT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_customers_status (status),
  INDEX idx_customers_type (customer_type_id),
  INDEX idx_customers_created (created_at),
  UNIQUE KEY uq_customers_email (email),
  UNIQUE KEY uq_customers_code (customer_code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(100) NOT NULL UNIQUE,
  email VARCHAR(255) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  full_name VARCHAR(100) NULL,
  phone VARCHAR(20) NULL,
  avatar_url VARCHAR(500) NULL,
  role ENUM('admin','staff','customer') DEFAULT 'customer',
  customer_id INT NULL,
  is_active TINYINT(1) DEFAULT 1,
  last_login_at TIMESTAMP NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_users_role (role),
  INDEX idx_users_customer (customer_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS support_staff (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  staff_code VARCHAR(50) UNIQUE,
  department VARCHAR(100) NULL,
  position VARCHAR(100) NULL,
  phone VARCHAR(20) NULL,
  email VARCHAR(255) NULL,
  is_active TINYINT(1) DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_support_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS document_types (
  id INT AUTO_INCREMENT PRIMARY KEY,
  code VARCHAR(50) UNIQUE NOT NULL,
  name VARCHAR(100) NOT NULL,
  description TEXT NULL,
  icon_class VARCHAR(100) NULL,
  is_active TINYINT(1) DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS documents (
  id INT AUTO_INCREMENT PRIMARY KEY,
  document_code VARCHAR(50) UNIQUE NOT NULL,
  title VARCHAR(255) NOT NULL,
  document_type_id INT NOT NULL,
  order_id INT NULL,
  folder_id INT NULL,
  customer_id INT NOT NULL,
  file_path VARCHAR(500) NOT NULL,
  file_name VARCHAR(255) NOT NULL,
  file_size BIGINT NULL,
  mime_type VARCHAR(100) NULL,
  status ENUM('draft','published','archived') DEFAULT 'draft',
  metadata JSON NULL,
  created_by INT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_documents_status (status),
  INDEX idx_documents_customer (customer_id),
  INDEX idx_documents_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS document_customers (
  id INT AUTO_INCREMENT PRIMARY KEY,
  document_id INT NOT NULL,
  customer_id INT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_document_customer (document_id, customer_id),
  INDEX idx_document (document_id),
  INDEX idx_customer (customer_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS tickets (
  id INT AUTO_INCREMENT PRIMARY KEY,
  ticket_code VARCHAR(50) NOT NULL UNIQUE,
  title VARCHAR(255) NOT NULL,
  description TEXT NULL,
  customer_id INT NULL,
  status ENUM('open','progress','resolved') NOT NULL DEFAULT 'open',
  priority ENUM('high','medium','low') NOT NULL DEFAULT 'medium',
  assigned_to INT NULL,
  created_by INT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_tickets_status (status),
  INDEX idx_tickets_priority (priority),
  INDEX idx_tickets_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS ticket_replies (
  id INT AUTO_INCREMENT PRIMARY KEY,
  ticket_id INT NOT NULL,
  author_type ENUM('admin','staff','customer') NOT NULL DEFAULT 'admin',
  author_id INT NULL,
  message TEXT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_ticket_replies_ticket (ticket_id),
  INDEX idx_ticket_replies_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS posts (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(255) NOT NULL,
  slug VARCHAR(255) NOT NULL UNIQUE,
  category VARCHAR(100) NOT NULL,
  excerpt TEXT NULL,
  content MEDIUMTEXT NULL,
  thumbnail_url VARCHAR(500) NULL,
  status ENUM('published','draft') NOT NULL DEFAULT 'draft',
  view_count INT NOT NULL DEFAULT 0,
  published_at TIMESTAMP NULL,
  created_by INT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_posts_status (status),
  INDEX idx_posts_category (category),
  INDEX idx_posts_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS activity_logs (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  user_name VARCHAR(100) NULL,
  user_role VARCHAR(20) NULL,
  action VARCHAR(100) NOT NULL,
  entity_type VARCHAR(50) NOT NULL,
  entity_id INT NULL,
  details TEXT NULL,
  description TEXT NULL,
  ip_address VARCHAR(45) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_activity_created (created_at),
  INDEX idx_activity_entity (entity_type, entity_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS system_settings (
  id INT AUTO_INCREMENT PRIMARY KEY,
  setting_key VARCHAR(100) UNIQUE NOT NULL,
  setting_value TEXT NULL,
  setting_type VARCHAR(50) DEFAULT 'string',
  description TEXT NULL,
  is_public TINYINT(1) DEFAULT 0,
  updated_by INT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_setting_key (setting_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed cơ bản
INSERT INTO customer_types (id, code, name, description, is_active)
VALUES
  (1, 'business', 'Doanh nghiệp', 'Khách hàng doanh nghiệp', 1),
  (2, 'individual', 'Cá nhân', 'Khách hàng cá nhân', 1)
ON DUPLICATE KEY UPDATE
  name = VALUES(name),
  description = VALUES(description),
  is_active = VALUES(is_active);

INSERT INTO document_types (code, name, description, icon_class, is_active)
VALUES
  ('invoice', 'Hóa đơn', 'Commercial Invoice', 'invoice', 1),
  ('packing', 'Packing List', 'Danh sách đóng gói', 'packing', 1),
  ('certificate', 'Chứng nhận', 'Certificate', 'certificate', 1),
  ('bill', 'Bill of Lading', 'Vận đơn', 'bill', 1)
ON DUPLICATE KEY UPDATE
  name = VALUES(name),
  description = VALUES(description),
  icon_class = VALUES(icon_class),
  is_active = VALUES(is_active);

-- Admin mặc định: admin@gmail.com / 123456
-- (lần login đầu API sẽ tự hash lại nếu đang là plain text)
INSERT INTO admins (name, email, password, role)
VALUES ('Administrator', 'admin@gmail.com', '123456', 'admin')
ON DUPLICATE KEY UPDATE
  name = VALUES(name),
  password = VALUES(password),
  role = VALUES(role);

INSERT INTO system_settings (setting_key, setting_value, setting_type, description, is_public)
VALUES
  ('site_name', 'NHUTIN Portal', 'string', 'Tên website', 1),
  ('max_file_size', '20971520', 'number', 'Kích thước file tối đa (bytes)', 0),
  ('allowed_file_types', 'pdf,doc,docx,xls,xlsx,jpg,jpeg,png,webp,gif', 'string', 'Loại file được phép upload', 0)
ON DUPLICATE KEY UPDATE
  setting_value = VALUES(setting_value),
  setting_type = VALUES(setting_type),
  description = VALUES(description),
  is_public = VALUES(is_public);

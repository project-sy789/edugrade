# คู่มือติดตั้ง EduGrade บน Web Hosting

## 📋 ความต้องการของ Hosting

### ✅ ต้องมี:
- PHP 8.0 หรือสูงกว่า
- SQLite3 Extension
- mod_rewrite (Apache) หรือ URL Rewrite (Nginx)
- ความจุอย่างน้อย 50 MB

### 🏢 Hosting ที่แนะนำ:
- **Hostinger** (รองรับ PHP 8.x, SQLite)
- **000webhost** (ฟรี, รองรับ PHP 8.x)
- **InfinityFree** (ฟรี, รองรับ PHP 8.x)
- **Heroku** (ฟรี, รองรับ PHP)
- **Railway** (ฟรี, รองรับ PHP)

---

## 🚀 วิธีติดตั้งบน Shared Hosting (cPanel)

### ขั้นตอนที่ 1: อัปโหลดไฟล์

**วิธีที่ 1: ใช้ Git (แนะนำ)**

1. เข้า cPanel → **Terminal** หรือ **SSH Access**
2. ไปที่โฟลเดอร์ public_html:
```bash
cd public_html
```

3. Clone โปรเจค:
```bash
git clone https://github.com/project-sy789/edugrade.git
cd edugrade
```

**วิธีที่ 2: ใช้ File Manager**

1. ดาวน์โหลดโปรเจคเป็น ZIP:
   - ไปที่ https://github.com/project-sy789/edugrade
   - คลิก **Code** → **Download ZIP**

2. เข้า cPanel → **File Manager**
3. ไปที่ `public_html`
4. อัปโหลดไฟล์ ZIP
5. คลิกขวา → **Extract**
6. เปลี่ยนชื่อโฟลเดอร์เป็น `edugrade`

---

### ขั้นตอนที่ 2: ตั้งค่า Document Root

**ถ้าต้องการให้เป็นหน้าหลักของเว็บ:**

1. เข้า cPanel → **Domains** → **Domains**
2. คลิก **Manage** ที่โดเมนของคุณ
3. แก้ไข **Document Root** เป็น:
```
public_html/edugrade/public
```
4. **Save**

**หรือเข้าผ่าน subdirectory:**
- URL: `https://yourdomain.com/edugrade/public`

---

### ขั้นตอนที่ 3: ตั้งค่าสิทธิ์โฟลเดอร์

เข้า **Terminal** หรือ **SSH** แล้วรัน:

```bash
cd public_html/edugrade

# ตั้งค่าสิทธิ์
chmod 755 database
chmod 755 uploads
chmod 755 uploads/logos
chmod 755 sessions
chmod 755 logs

# สร้างไฟล์ .gitkeep
touch uploads/.gitkeep
touch uploads/logos/.gitkeep
touch sessions/.gitkeep
touch logs/.gitkeep
```

---

### ขั้นตอนที่ 4: รันสคริปต์ติดตั้ง

**วิธีที่ 1: ผ่าน Terminal/SSH**

```bash
cd public_html/edugrade
php install.php
```

**วิธีที่ 2: ผ่าน Web Browser**

1. สร้างไฟล์ `web-install.php` ในโฟลเดอร์ `public`:

```php
<?php
// Redirect to install script
chdir('..');
require 'install.php';
```

2. เข้า: `https://yourdomain.com/web-install.php`
3. ติดตั้งจะทำงานอัตโนมัติ
4. **ลบไฟล์ `web-install.php` ทันทีหลังติดตั้ง!**

---

### ขั้นตอนที่ 5: ตั้งค่า .htaccess (สำคัญ!)

ตรวจสอบว่าไฟล์ `.htaccess` ในโฟลเดอร์ `public` มีเนื้อหานี้:

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteBase /
    
    # Redirect to public folder if accessing root
    RewriteCond %{REQUEST_URI} !^/public/
    RewriteRule ^(.*)$ public/$1 [L]
</IfModule>

# Security
<FilesMatch "\.(htaccess|htpasswd|ini|log|sh|sql)$">
    Order Allow,Deny
    Deny from all
</FilesMatch>

# PHP Settings
php_value upload_max_filesize 10M
php_value post_max_size 10M
php_value max_execution_time 300
php_value max_input_time 300
```

---

### ขั้นตอนที่ 6: ทดสอบระบบ

1. เปิดเบราว์เซอร์ไปที่:
   - `https://yourdomain.com` (ถ้าตั้ง Document Root)
   - หรือ `https://yourdomain.com/edugrade/public`

2. Login ด้วย:
   - Username: `admin`
   - Password: `password`

3. **เปลี่ยนรหัสผ่านทันที!**

---

## 🔧 การแก้ปัญหาที่พบบ่อย

### ❌ Error: "Internal Server Error"

**แก้ไข:**
1. เช็ค PHP version:
```bash
php -v
```
ต้องเป็น 8.0 ขึ้นไป

2. เช็ค error log:
```bash
tail -f logs/error.log
```

3. ตรวจสอบ `.htaccess` ว่าถูกต้อง

---

### ❌ Error: "SQLite3 not found"

**แก้ไข:**

1. เข้า cPanel → **Select PHP Version**
2. เปิด Extension: `sqlite3`
3. **Save**

---

### ❌ Error: "Permission denied"

**แก้ไข:**
```bash
chmod 755 database uploads sessions logs
chmod 644 database/*.db
```

---

### ❌ หน้าเว็บแสดงโค้ด PHP

**แก้ไข:**

1. ตรวจสอบว่า PHP ทำงาน:
   - สร้างไฟล์ `info.php`:
   ```php
   <?php phpinfo(); ?>
   ```
   - เข้า `https://yourdomain.com/info.php`
   - ถ้าเห็นข้อมูล PHP = OK
   - **ลบไฟล์ทันที!**

2. ตรวจสอบ Document Root ว่าชี้ไปที่ `public` folder

---

## 🌐 การติดตั้งบน VPS (Ubuntu/Debian)

### ขั้นตอนที่ 1: ติดตั้ง Dependencies

```bash
# Update system
sudo apt update && sudo apt upgrade -y

# ติดตั้ง PHP และ Extensions
sudo apt install -y php8.1 php8.1-fpm php8.1-sqlite3 php8.1-mbstring php8.1-xml

# ติดตั้ง Nginx
sudo apt install -y nginx

# ติดตั้ง Git
sudo apt install -y git
```

---

### ขั้นตอนที่ 2: Clone โปรเจค

```bash
cd /var/www
sudo git clone https://github.com/project-sy789/edugrade.git
cd edugrade
```

---

### ขั้นตอนที่ 3: ตั้งค่าสิทธิ์

```bash
sudo chown -R www-data:www-data /var/www/edugrade
sudo chmod -R 755 /var/www/edugrade
sudo chmod 755 database uploads sessions logs
```

---

### ขั้นตอนที่ 4: ตั้งค่า Nginx

สร้างไฟล์ `/etc/nginx/sites-available/edugrade`:

```nginx
server {
    listen 80;
    server_name yourdomain.com;
    root /var/www/edugrade/public;
    index index.php index.html;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.ht {
        deny all;
    }
}
```

Enable site:
```bash
sudo ln -s /etc/nginx/sites-available/edugrade /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
```

---

### ขั้นตอนที่ 5: รันสคริปต์ติดตั้ง

```bash
cd /var/www/edugrade
sudo php install.php
```

---

### ขั้นตอนที่ 6: ติดตั้ง SSL (Let's Encrypt)

```bash
sudo apt install -y certbot python3-certbot-nginx
sudo certbot --nginx -d yourdomain.com
```

---

## 📱 การติดตั้งบน Heroku

### ขั้นตอนที่ 1: เตรียมไฟล์

สร้างไฟล์ `Procfile`:
```
web: vendor/bin/heroku-php-apache2 public/
```

สร้างไฟล์ `composer.json` (ถ้ายังไม่มี):
```json
{
    "require": {
        "php": "^8.0"
    }
}
```

---

### ขั้นตอนที่ 2: Deploy

```bash
# Login Heroku
heroku login

# สร้าง app
heroku create your-app-name

# Push
git push heroku main

# เปิดเว็บ
heroku open
```

---

## 🔐 ความปลอดภัย

### ✅ สิ่งที่ต้องทำหลังติดตั้ง:

1. **เปลี่ยนรหัสผ่าน admin ทันที**
2. **ลบไฟล์ติดตั้ง:**
   ```bash
   rm install.php
   rm web-install.php
   ```
3. **ตั้งค่า HTTPS** (SSL Certificate)
4. **Backup database เป็นประจำ**
5. **อัปเดทระบบเป็นประจำ**

---

## 📞 ต้องการความช่วยเหลือ?

- GitHub Issues: https://github.com/project-sy789/edugrade/issues
- Documentation: README.md

---

**เรียบร้อยแล้ว! ระบบพร้อมใช้งานบน hosting** 🎉

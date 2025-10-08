# XAMPP Build – IT Ticketing System (PHP + MySQL)

This build is pre-configured for a typical **XAMPP on Windows** setup.

## 1) Requirements
- XAMPP with PHP 8.1+ (Control Panel with **Apache** and **MySQL**)
- Ensure these PHP extensions are enabled (edit `C:\\xampp\\php\\php.ini`):
  - `extension=fileinfo`
  - `extension=mysqli` (for phpMyAdmin / MySQL client) – PDO MySQL is bundled on Windows

Restart Apache after editing `php.ini`.

## 2) Install the app
1. Extract this folder to: `C:\\xampp\\htdocs\\it-ticketing-php`
2. Confirm this exists: `C:\\xampp\\htdocs\\it-ticketing-php\\public\\index.php`

## 3) Create the database
1. Go to **http://localhost/phpmyadmin**
2. Create database `ticketing` (collation `utf8mb4_unicode_ci`)
3. Import `schema/schema.sql`

Or use CLI:
```bat
"C:\\xampp\\mysql\\bin\\mysql.exe" -u root -p -e "CREATE DATABASE IF NOT EXISTS ticketing CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
"C:\\xampp\\mysql\\bin\\mysql.exe" -u root -p ticketing < schema\\schema.sql
```

## 4) Configure environment
A `.env` for XAMPP is included. Review it at `C:\\xampp\\htdocs\\it-ticketing-php\\.env`:
```
APP_URL=http://localhost/it-ticketing-php
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ticketing
DB_USERNAME=root
DB_PASSWORD=
```
If root has a password, set `DB_PASSWORD`.

## 5) Apache rewrite / VirtualHost
This app needs URL rewriting.

### Option A – Default htdocs (simplest)
In `C:\\xampp\\apache\\conf\\httpd.conf`, ensure:
- `LoadModule rewrite_module modules/mod_rewrite.so` (default)
- For `<Directory "C:/xampp/htdocs">` set `AllowOverride All`:
```
<Directory "C:/xampp/htdocs">
    Options Indexes FollowSymLinks Includes ExecCGI
    AllowOverride All
    Require all granted
</Directory>
```

### Option B – VirtualHost (clean URL)
Append to `C:\\xampp\\apache\\conf\\extra\\httpd-vhosts.conf`:
```
<VirtualHost *:80>
    ServerName ticketing.local
    DocumentRoot "C:/xampp/htdocs/it-ticketing-php/public"
    <Directory "C:/xampp/htdocs/it-ticketing-php/public">
        AllowOverride All
        Require all granted
    </Directory>
    ErrorLog "logs/ticketing-error.log"
    CustomLog "logs/ticketing-access.log" common
</VirtualHost>
```
Add to `C:\\Windows\\System32\\drivers\\etc\\hosts`:
```
127.0.0.1  ticketing.local
```
Restart Apache and browse `http://ticketing.local/`.

## 6) PHP upload limits
If needed, in `php.ini`:
```
upload_max_filesize = 10M
post_max_size = 10M
```
Keep `.env` `MAX_UPLOAD_BYTES` aligned (default 5 MB).

## 7) First run
- Visit **http://localhost/it-ticketing-php**
- Click **Register** (creates a `requester`)
- Promote to admin in phpMyAdmin:
```sql
UPDATE users SET role='admin' WHERE email='you@example.com';
```

## Troubleshooting
- 404s: ensure `AllowOverride All` and `public/.htaccess` exists
- Errors: check `C:\\xampp\\apache\\logs\\error.log`
- Uploads: verify size limits and `public\\uploads` directory exists
- Login issues: ensure time is correct and registration succeeded

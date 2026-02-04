# SecureMyAir API (redapple-api)

PHP backend API for **SecureMyAir** ? air quality and machine monitoring. It provides authentication (JWT, 2FA via email), dashboard data (humidity, AQI, VOC), and management for customers, machines, inspectors, operators, and GAES (training) modules.

---

## Tech stack

- **PHP** (7.4+ recommended, with `mysqli`, `json`, `openssl`)
- **MySQL** (database `plc`)
- **JWT** for API auth
- **PHPMailer** for email (2FA, password reset)
- **Apache** or **Nginx** (production), or PHP built-in server (development)

---

## Prerequisites

| Requirement | Minimum |
|------------|---------|
| PHP       | 7.4+    |
| MySQL     | 5.7+    |
| Web server| Apache 2.4+, Nginx, or PHP CLI |

**PHP extensions:** `mysqli`, `json`, `openssl`, `mbstring`

---

## Configuration

### 1. Database

Create a MySQL database named `plc` and run your schema/migrations. Then set connection details in:

**`mydbCon.php`**

```php
$dbCon = mysqli_connect(
    'localhost',   // host
    'your_user',   // MySQL username
    'your_pass',   // MySQL password
    'plc'          // database name
);
```

Use your own credentials; do not commit real passwords to version control.

### 2. Email (optional, for 2FA / reset)

If you use 2FA or password reset, configure SMTP in the login/reset scripts that use PHPMailer (e.g. `login.php`, `reset.php`).

---

## How to run

### Option A: Apache (Windows & Linux)

1. **Windows (XAMPP / WAMP / Laragon)**  
   - Install XAMPP, WAMP, or Laragon.  
   - Copy or clone this project into the web root (e.g. `C:\xampp\htdocs\api` or `C:\wamp64\www\api`).  
   - Start **Apache** and **MySQL** from the control panel.  
   - Open: `http://localhost/api/` (adjust path if you used a different folder name).

2. **Linux (Apache)**  
   - Install Apache and PHP:

   ```bash
   # Debian/Ubuntu
   sudo apt update
   sudo apt install apache2 php php-mysqli php-mbstring php-json php-xml libapache2-mod-php

   # Enable mod_rewrite if you use it
   sudo a2enmod rewrite
   sudo systemctl restart apache2
   ```

   - Put the project in the document root, e.g.:

   ```bash
   sudo cp -r /path/to/api /var/www/html/api
   # Or symlink:
   sudo ln -s /path/to/api /var/www/html/api
   ```

   - Restart Apache: `sudo systemctl restart apache2`  
   - Open: `http://localhost/api/` (or your server hostname).

3. **Virtual host (optional)**  
   - Point a vhost `DocumentRoot` to this project folder so the app is served at e.g. `http://securemyair-api.local`.

---

### Option B: Nginx on Ubuntu (deployment)

1. **Install Nginx, PHP-FPM, and MySQL:**

   ```bash
   sudo apt update
   sudo apt install nginx php-fpm php-mysql php-mysqli php-mbstring php-json php-xml mysql-server
   ```

2. **Set PHP upload limits** (to match the app’s 50M uploads). Edit the PHP-FPM pool config or a custom `php.ini`:

   ```bash
   # Find the php.ini used by PHP-FPM (e.g. PHP 8.1)
   php -i | grep "Loaded Configuration File"

   # Edit it (path may be /etc/php/8.1/fpm/php.ini)
   sudo nano /etc/php/8.1/fpm/php.ini
   ```

   Set or uncomment:

   ```ini
   upload_max_filesize = 50M
   post_max_size = 50M
   max_execution_time = 300
   memory_limit = 256M
   max_input_time = 300
   ```

   Restart PHP-FPM:

   ```bash
   sudo systemctl restart php8.1-fpm
   ```

3. **Deploy the project** (e.g. under `/var/www`):

   ```bash
   sudo mkdir -p /var/www/securemyair-api
   sudo chown $USER:$USER /var/www/securemyair-api
   # Copy or clone your project into /var/www/securemyair-api
   cp -r /path/to/api/* /var/www/securemyair-api/
   ```

4. **Create an Nginx site config:**

   ```bash
   sudo nano /etc/nginx/sites-available/securemyair-api
   ```

   Paste (adjust `server_name` and `root` if needed):

   ```nginx
   server {
       listen 80;
       server_name your-domain.com;   # or your server IP
       root /var/www/securemyair-api;
       index index.php index.html;

       client_max_body_size 50M;

       location / {
           try_files $uri $uri/ /index.php?$query_string;
       }

       location ~ \.php$ {
           include snippets/fastcgi-php.conf;
           fastcgi_pass unix:/run/php/php8.1-fpm.sock;
           fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
           include fastcgi_params;
           fastcgi_read_timeout 300;
       }

       location ~ /\.ht {
           deny all;
       }
   }
   ```

   **Note:** If your PHP version is not 8.1, change `php8.1-fpm` and the socket path (e.g. `php8.2-fpm.sock`). List sockets with: `ls /run/php/`.

5. **Enable the site and reload Nginx:**

   ```bash
   sudo ln -s /etc/nginx/sites-available/securemyair-api /etc/nginx/sites-enabled/
   sudo nginx -t
   sudo systemctl reload nginx
   ```

6. **Set permissions** so Nginx can read files and PHP-FPM can write if needed (e.g. uploads):

   ```bash
   sudo chown -R www-data:www-data /var/www/securemyair-api
   sudo chmod -R 755 /var/www/securemyair-api
   ```

7. Open **http://your-domain.com** (or your server IP). For HTTPS, add a certificate (e.g. `certbot` with Let’s Encrypt).

---

### Option C: PHP built-in server (Windows & Linux)

Good for local development without Apache.

1. **Terminal in project root** (folder containing `login.php`, `dashboard.php`, `mydbCon.php`, etc.):

   **Windows (PowerShell or CMD):**

   ```powershell
   cd D:\HSK\SecureMyAir.com\iamredapple(securemyair)\api
   php -S localhost:8080
   ```

   **Linux / macOS:**

   ```bash
   cd /path/to/api
   php -S localhost:8080
   ```

2. Open: **http://localhost:8080**

**Note:** The built-in server does not read `.htaccess`. Upload size limits are from `php.ini` (`upload_max_filesize`, `post_max_size`). For production, use Apache or Nginx + PHP-FPM.

---

## Quick reference: run commands

| OS      | Method                          | URL / note                    |
|---------|----------------------------------|-------------------------------|
| Windows | `php -S localhost:8080`         | http://localhost:8080         |
| Linux   | `php -S localhost:8080`         | http://localhost:8080         |
| Windows | Apache (XAMPP/WAMP)             | http://localhost/api          |
| Linux   | Apache in `/var/www/html/api`   | http://localhost/api          |
| Ubuntu  | Nginx + PHP-FPM                 | http://your-domain.com (see Option B above) |

---

## Main entry points

| Path / file      | Purpose                    |
|------------------|----------------------------|
| `login.php`      | Admin login (JWT, optional 2FA) |
| `reset.php`      | Password reset             |
| `dashboard.php`  | Dashboard API (machine data, ranges) |
| `client/`        | Client-facing app          |
| `gaes/`          | GAES / training            |
| `inspector/`     | Inspector workflows        |
| `operator/`      | Operator workflows         |
| `customers.php`  | Customer management        |
| `machines.php`   | Machine management         |

Protected endpoints use `auth.php` and expect a JWT in the `Authorization` header.

### Swagger (API documentation)

To browse and test the API like Swagger:

1. Open **`/swagger/`** in your browser (e.g. `http://localhost:8080/swagger/` or `https://your-droplet.com/swagger/`).
2. Use **Authorize** and paste your JWT (from `login.php`) as `Bearer <token>`.
3. Try any endpoint from the list.

The spec is in `swagger/openapi.yaml`; the UI is in `swagger/index.html` (Swagger UI from CDN).

---

## Security notes

- Change default secrets (e.g. `$SECRET_KEY` in `login.php` and `auth.php`) and do not commit them.
- Keep DB credentials in `mydbCon.php` out of version control (e.g. via `.gitignore` or env-based config).
- Use HTTPS in production.

---

## License

Proprietary ? SecureMyAir / redapple.

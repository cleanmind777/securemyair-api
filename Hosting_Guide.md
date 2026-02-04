## SecureMyAir API – Droplet Hosting Guide

This guide explains how to host the SecureMyAir API on a **DigitalOcean Droplet** running **Ubuntu + Nginx + PHP‑FPM + MySQL**.

> This document is a focused deployment guide. General tech stack and project overview are in `README.md`.

---

## Deployment options

| Option | Use case | Steps |
|--------|-----------|--------|
| **By IP only** | Quick test, no domain | Use Droplet IP in Nginx `server_name` and in browser. Skip DNS and Certbot. |
| **By domain** | Production (recommended) | Point domain DNS to Droplet IP, set `server_name` to your domain, then enable HTTPS with Certbot. |

---

## 1. Create the Droplet

- **Image**: Ubuntu 22.04 LTS or 24.04 LTS
- **Size**: Choose based on traffic (1–2 GB RAM is fine for small setups)
- **Datacenter**: Closest to your users
- **SSH**: Prefer SSH keys over passwords

After creation, note the Droplet’s **public IP** (e.g. `203.0.113.10`). If using a domain, you will point it to this IP later.

---

## 2. SSH into the Droplet

On your local machine:

```bash
ssh root@YOUR_DROPLET_IP
```

Optionally create a non‑root user:

```bash
adduser deploy
usermod -aG sudo deploy
su - deploy
```

---

## 3. Install Nginx, PHP‑FPM, and MySQL

```bash
sudo apt update
sudo apt install -y nginx php-fpm php-mysql php-mysqli php-mbstring php-json php-xml mysql-server git
```

Start and enable MySQL (on Ubuntu 24.04 the server may not start automatically):

```bash
sudo systemctl start mysql
sudo systemctl enable mysql
sudo systemctl status mysql   # should show active (running)
```

> **Note:** On Ubuntu 24.04, `mysql_secure_installation` is often not included. You can secure MySQL manually (optional): log in with `sudo mysql` and run `ALTER USER 'root'@'localhost' IDENTIFIED WITH mysql_native_password BY 'your_root_password';` then `FLUSH PRIVILEGES;`.

Create database and app user (change passwords):

```bash
sudo mysql
```

In the MySQL prompt:

```sql
CREATE DATABASE plc CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'plc_user'@'localhost' IDENTIFIED BY 'STRONG_PASSWORD_HERE';
GRANT ALL PRIVILEGES ON plc.* TO 'plc_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

---

## 4. Deploy project code

Choose a path like `/var/www/securemyair-api`:

```bash
sudo mkdir -p /var/www/securemyair-api
sudo chown $USER:$USER /var/www/securemyair-api
```

Then either **clone from git**:

```bash
cd /var/www/securemyair-api
git clone YOUR_REPO_URL .
```

or **upload files** (SCP / SFTP / rsync) from your local machine into `/var/www/securemyair-api`.

---

## 5. Configure the app (database, email)

Edit `mydbCon.php` with your Droplet database credentials:

```php
$dbCon = mysqli_connect(
    'localhost',      // host
    'plc_user',       // MySQL username
    'STRONG_PASSWORD_HERE', // MySQL password
    'plc'            // database name
);
```

Configure SMTP (if needed) in your login/reset scripts to use a real mail server (e.g. Mailgun, SendGrid, or your own SMTP).

---

## 6. Adjust PHP limits (uploads / timeouts)

Find and edit the PHP‑FPM `php.ini` (version may differ):

```bash
php -i | grep "Loaded Configuration File"
# e.g. /etc/php/8.1/fpm/php.ini

sudo nano /etc/php/8.1/fpm/php.ini
```

Set:

```ini
upload_max_filesize = 50M
post_max_size = 50M
max_execution_time = 300
memory_limit = 256M
max_input_time = 300
```

Restart PHP‑FPM:

```bash
sudo systemctl restart php8.1-fpm
```

> If using a different PHP version (8.0, 8.2, etc.), adjust the paths and service name accordingly.

---

## 7. Configure Nginx

Create a site config:

```bash
sudo nano /etc/nginx/sites-available/securemyair-api
```

**Option A – By IP only** (use your Droplet IP as `server_name`):

```nginx
server {
    listen 80;
    server_name 203.0.113.10;   # replace with your Droplet IP

    root /var/www/securemyair-api;
    index index.php index.html;

    client_max_body_size 50M;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php8.1-fpm.sock;  # adjust to your PHP version
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_read_timeout 300;
    }

    location ~ /\.ht {
        deny all;
    }
}
```

**Option B – By domain** (use your domain as `server_name`; DNS must point to the Droplet IP first):

```nginx
server {
    listen 80;
    server_name api.yourdomain.com;   # e.g. api.securemyair.com

    root /var/www/securemyair-api;
    index index.php index.html;

    client_max_body_size 50M;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php8.1-fpm.sock;  # adjust to your PHP version
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_read_timeout 300;
    }

    location ~ /\.ht {
        deny all;
    }
}
```

Enable the site and reload Nginx:

```bash
sudo ln -s /etc/nginx/sites-available/securemyair-api /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

---

## 8. File permissions

Allow Nginx/PHP‑FPM (`www-data`) to read the app and write to any upload/log directories:

```bash
sudo chown -R www-data:www-data /var/www/securemyair-api
sudo chmod -R 755 /var/www/securemyair-api
```

If you have specific upload folders, you can give them more permissive write rights if needed.

---

## 9. Using a domain (DNS + HTTPS)

Use this when you want to serve the API at a domain (e.g. `api.securemyair.com`) with HTTPS.

### 9.1 Point the domain to the Droplet

In your domain registrar or DNS provider (DigitalOcean DNS, Cloudflare, etc.):

1. Add an **A record**:
   - **Name**: `api` (for `api.yourdomain.com`) or `@` (for `yourdomain.com`)
   - **Value**: your Droplet’s public IP
   - **TTL**: 300 or default

2. Wait for DNS to propagate (a few minutes up to 48 hours). Check with:
   ```bash
   dig api.yourdomain.com +short
   ```
   You should see your Droplet IP.

### 9.2 Set Nginx `server_name` to the domain

In `/etc/nginx/sites-available/securemyair-api`, set:

```nginx
server_name api.yourdomain.com;
```

Then:

```bash
sudo nginx -t
sudo systemctl reload nginx
```

### 9.3 Enable HTTPS with Certbot (Let’s Encrypt)

Install Certbot and get a certificate (replace `api.yourdomain.com` with your domain):

```bash
sudo apt install -y certbot python3-certbot-nginx
sudo certbot --nginx -d api.yourdomain.com
```

Follow the prompts (email, agree to terms). Certbot will configure Nginx for HTTPS and set up automatic renewal.

Test in browser: **https://api.yourdomain.com**

---

## 10. Quick checklist

- [ ] Droplet created (Ubuntu 22.04+), SSH access working  
- [ ] Nginx, PHP‑FPM, MySQL installed; MySQL service started and enabled  
- [ ] Database `plc` and user `plc_user` created; `mydbCon.php` updated  
- [ ] Project files in `/var/www/securemyair-api`  
- [ ] PHP limits set (`50M` upload, etc.), PHP‑FPM restarted  
- [ ] Nginx site enabled (`server_name` = IP or domain), config tested, Nginx reloaded  
- [ ] Permissions set for `www-data`  
- [ ] **If using a domain:** DNS A record points to Droplet IP; Certbot HTTPS configured  

**When done:**

| Case | URL |
|------|-----|
| By IP only | `http://YOUR_DROPLET_IP` |
| By domain (HTTP) | `http://api.yourdomain.com` |
| By domain (HTTPS) | `https://api.yourdomain.com` |


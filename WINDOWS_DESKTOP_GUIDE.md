# Run SecureMyAir API on Windows Desktop (Testing)

Quick guide to run this API on your Windows PC for local testing.

---

## What you need

| Requirement | Purpose |
|------------|---------|
| **PHP 7.4+** | Run the API (extensions: `mysqli`, `json`, `openssl`, `mbstring`) |
| **MySQL 5.7+** | Database `plc` |

You can install them separately, or use a stack (recommended for testing):

- **XAMPP** – Apache + PHP + MySQL in one installer  
- **WAMP** – Same idea, Windows-only  
- **Laragon** – Lightweight, includes PHP + MySQL (optional Apache)

---

## Option 1: XAMPP / WAMP / Laragon (easiest for testing)

### Step 1: Install the stack

1. Download and install **XAMPP** from https://www.apachefriends.org/  
   (or WAMP from https://www.wampserver.com/ or Laragon from https://laragon.org/)
2. During/after install, ensure **Apache** and **MySQL** are available.

### Step 2: Put the project in the web root

- **XAMPP:** Copy the project folder into `C:\xampp\htdocs\`  
  - Example: `C:\xampp\htdocs\securemyair-api\`  
  - So you have `C:\xampp\htdocs\securemyair-api\login.php`, `mydbCon.php`, etc.
- **WAMP:** Use `C:\wamp64\www\securemyair-api\` (path may vary by WAMP version).
- **Laragon:** Use `C:\laragon\www\securemyair-api\` (or add project there).

If you prefer to keep the project at `D:\HSK\securemyair-api`:

- Create a **symbolic link** from the web root to your project (run **Command Prompt or PowerShell as Administrator**):

  ```powershell
  mklink /D "C:\xampp\htdocs\securemyair-api" "D:\HSK\securemyair-api"
  ```

  Then in the browser you’ll use: `http://localhost/securemyair-api/`

### Step 3: Start Apache and MySQL

- Open the **XAMPP / WAMP / Laragon** control panel.
- Start **Apache** and **MySQL**.

### Step 4: Create the database and user (MySQL)

1. Open **phpMyAdmin**: `http://localhost/phpmyadmin` (XAMPP/WAMP) or use Laragon’s “MySQL” menu.
2. Create a database named **`plc`** (collation: `utf8mb4_unicode_ci`).
3. Create a user (e.g. `plc_user`) with password and grant **All privileges** on database `plc`.

Or from command line (MySQL client in XAMPP is in `C:\xampp\mysql\bin\mysql.exe`):

```sql
CREATE DATABASE plc CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'plc_user'@'localhost' IDENTIFIED BY 'your_password';
GRANT ALL PRIVILEGES ON plc.* TO 'plc_user'@'localhost';
FLUSH PRIVILEGES;
```

### Step 5: Configure the API (database connection)

The project uses **environment variables** with fallbacks in `mydbCon.php`:

- `DB_HOST` → default `localhost`
- `DB_USER` → default `plc_user`
- `DB_PASS` → default `''`
- `DB_NAME` → default `plc`

**Option A – Set environment variables (recommended)**  
In PowerShell (current session only):

```powershell
$env:DB_HOST = "localhost"
$env:DB_USER = "plc_user"
$env:DB_PASS = "your_password"
$env:DB_NAME = "plc"
```

Then start Apache from that same PowerShell if you use PHP with Apache, or use Option 2 below.

**Option B – Edit `mydbCon.php` for testing**  
Temporarily hardcode your credentials (do **not** commit real passwords):

```php
$dbHost = getenv('DB_HOST') ?: 'localhost';
$dbUser = getenv('DB_USER') ?: 'plc_user';
$dbPass = getenv('DB_PASS') ?: 'your_password';  // set your MySQL password
$dbName = getenv('DB_NAME') ?: 'plc';
```

(Or replace the `getenv(...) ?: '...'` fallbacks with your actual values.)

### Step 6: Run your schema/migrations

If you have SQL scripts or migrations for the `plc` database, run them (e.g. in phpMyAdmin or via `mysql` command line) so tables exist.

### Step 7: Open in browser

- If project is in `htdocs\securemyair-api`:  
  **http://localhost/securemyair-api/**
- Try login: **http://localhost/securemyair-api/login.php**
- API docs: **http://localhost/securemyair-api/swagger/**

---

## Option 2: PHP built-in server (no Apache, good for quick API testing)

Use this when you only want to run the PHP app from the command line and don’t need Apache.

### Step 1: Install PHP and MySQL

- **PHP:** Install PHP 7.4+ and add it to your PATH (e.g. from https://windows.php.net/download/ or via Chocolatey: `choco install php`).
- **MySQL:** Install MySQL Server (or use XAMPP/WAMP/Laragon and start only MySQL).

Verify PHP:

```powershell
php -v
```

### Step 2: Create database and user

Same as Option 1, Step 4: create database `plc` and user `plc_user` with a password.

### Step 3: Configure database

Set environment variables in PowerShell, then run PHP from the **same** window:

```powershell
$env:DB_HOST = "localhost"
$env:DB_USER = "plc_user"
$env:DB_PASS = "your_password"
$env:DB_NAME = "plc"
```

Or set the password (and optionally other values) in `mydbCon.php` as in Option 1, Step 5.

### Step 4: Start the built-in server from project root

```powershell
cd D:\HSK\securemyair-api
php -S localhost:8080
```

Keep this window open. You should see something like:

```
Development Server (http://localhost:8080) started
```

### Step 5: Open in browser

- **http://localhost:8080**
- Login: **http://localhost:8080/login.php**
- Swagger: **http://localhost:8080/swagger/**

**Note:** The PHP built-in server does **not** read `.htaccess`. For production or full Apache behavior, use Option 1 (XAMPP/WAMP/Laragon).

---

## Quick checklist (Windows Desktop)

- [ ] PHP 7.4+ installed (`php -v`) with extensions: mysqli, json, openssl, mbstring  
- [ ] MySQL installed and running  
- [ ] Database `plc` created  
- [ ] User `plc_user` (or your choice) created with access to `plc`  
- [ ] `mydbCon.php` or env vars `DB_*` set with correct password  
- [ ] Schema/migrations run on `plc`  
- [ ] **Option 1:** Project under web root, Apache + MySQL started → `http://localhost/securemyair-api/`  
- [ ] **Option 2:** `cd D:\HSK\securemyair-api` then `php -S localhost:8080` → `http://localhost:8080`

---

## Troubleshooting

| Problem | What to check |
|--------|----------------|
| “Page not found” / 404 | URL path (e.g. `/securemyair-api/` vs `/`), document root, or that you’re in the correct folder when using `php -S`. |
| “Connection refused” / DB error | MySQL is running; host is `localhost`; user/password and database name in `mydbCon.php` or env match MySQL. |
| Blank page or 500 | PHP errors: enable display in `php.ini` for testing (`display_errors = On`), or check Apache/PHP error logs. |
| Extensions missing | In `php.ini`, enable `extension=mysqli`, etc. Restart Apache or the built-in server. |

---

## Summary

| Goal | Action |
|------|--------|
| Easiest full stack on Windows | Install XAMPP → put project in `htdocs` (or symlink from `D:\HSK\securemyair-api`) → create DB `plc` and user → set DB password in env or `mydbCon.php` → start Apache + MySQL → open `http://localhost/securemyair-api/`. |
| No Apache, only API test | Install PHP + MySQL → create `plc` and user → set DB in env or `mydbCon.php` → `cd D:\HSK\securemyair-api` → `php -S localhost:8080` → open `http://localhost:8080`. |

For more details (e.g. Linux, Nginx, production), see **README.md** and **Hosting_Guide.md**.

# Collection Notice System

A PHP-based Collection Notice System for managing borrower notices, generating printable notice previews, and creating PDF copies. The system uses **Supabase PostgreSQL** as the database and can run locally with **XAMPP** or online using **Render + Docker**.

## Features

- Secure login with session-based authentication
- Borrower search and autofill
- Branch-based dashboard behavior
- Notice generation with amortization details
- Printable A4 notice preview
- PDF export
- Supabase PostgreSQL connection via PDO
- Works locally and on Render

## Tech Stack

- PHP 8.2
- Apache
- Supabase PostgreSQL
- PDO + `pdo_pgsql`
- Bootstrap 4.6
- JavaScript
- html2canvas
- jsPDF
- Docker (for Render deployment)

## Project Structure

```text
collection-notice/
├── assets/
│   ├── img/
│   └── signatures/
├── config/
│   ├── app.php
│   ├── auth.php
│   └── db.php
├── dashboard.php
├── index.php
├── logout.php
├── save_notice.php
├── search_borrower.php
├── .htaccess
└── Dockerfile
```

## Local Setup with XAMPP

### 1. Place the project in htdocs

Copy the project folder to:

```text
C:\xampp\htdocs\collection-notice
```

### 2. Start Apache

Open XAMPP Control Panel and start **Apache**.

### 3. Configure Apache for `.htaccess`

Make sure Apache has:

- `mod_rewrite` enabled
- `AllowOverride All`

### 4. Root redirect

Create this file:

`C:\xampp\htdocs\index.php`

```php
<?php
header("Location: /collection-notice/index.php");
exit;
```

### 5. Open the app

Use one of these URLs:

```text
http://127.0.0.1/collection-notice/index.php
```

or

```text
http://collectionnotice.local/collection-notice/index.php
```

## Optional Local Host Alias

To avoid browser issues with `localhost`, add this line to your Windows hosts file:

`C:\Windows\System32\drivers\etc\hosts`

```text
127.0.0.1 collectionnotice.local
```

Then open:

```text
http://collectionnotice.local/collection-notice/index.php
```

## Database Configuration

The app uses environment variables for database settings.

### `config/db.php`

```php
<?php
$host = getenv('DB_HOST');
$port = getenv('DB_PORT') ?: '6543';
$db   = getenv('DB_NAME') ?: 'postgres';
$user = getenv('DB_USER');
$pass = getenv('DB_PASS');

$dsn = "pgsql:host=$host;port=$port;dbname=$db;sslmode=require";

try {
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
```

## App Base URL Handling

The project supports both local and online deployment.

### `config/app.php`

```php
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$host = $_SERVER['HTTP_HOST'] ?? '';
$isLocal =
    stripos($host, 'localhost') !== false ||
    stripos($host, '127.0.0.1') !== false ||
    stripos($host, 'collectionnotice.local') !== false;

define('APP_BASE', $isLocal ? '/collection-notice' : '');

if (!function_exists('base_url')) {
    function base_url(): string {
        return APP_BASE;
    }
}

if (!function_exists('url')) {
    function url(string $path = ''): string {
        $path = ltrim($path, '/');
        return rtrim(APP_BASE, '/') . ($path !== '' ? '/' . $path : '/');
    }
}

if (!function_exists('redirect')) {
    function redirect(string $path): void {
        header('Location: ' . url($path));
        exit;
    }
}

if (!function_exists('h')) {
    function h($s): string {
        return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
    }
}
```

## `.htaccess`

Use this version for safe clean URLs:

```apache
Options -Indexes
DirectoryIndex index.php index.html index.htm
RewriteEngine On

# Serve existing real files normally
RewriteCond %{REQUEST_FILENAME} -f
RewriteRule ^ - [L]

# Rewrite clean URLs to matching .php files
RewriteCond %{REQUEST_FILENAME}.php -f
RewriteRule ^(.+?)/?$ $1.php [L,QSA]

# Redirect direct .php requests to clean URLs, except index.php
RewriteCond %{THE_REQUEST} \s/+(.+?)\.php(\s|\?) [NC]
RewriteCond %1 !^index$ [NC]
RewriteRule ^ %1 [R=301,L]
```

## Deploying to Render for Free

### 1. Push the project to GitHub

Make sure the repository includes:

- `Dockerfile`
- `.htaccess`
- `config/app.php`
- `config/db.php`

### 2. Create a Render Web Service

In Render:

- Click **New**
- Choose **Web Service**
- Connect your GitHub repository
- Select the repo
- Use **Docker** environment
- Choose the **Free** instance type

### 3. Add environment variables in Render

Set these values in your Render service:

- `DB_HOST`
- `DB_PORT`
- `DB_NAME`
- `DB_USER`
- `DB_PASS`

Use your **Supabase pooler** credentials.

### 4. Deploy

Once deployment finishes, Render gives you a URL like:

```text
https://your-service.onrender.com
```

The app will run from the root online, so `APP_BASE` becomes empty automatically.

## Dockerfile

```dockerfile
FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
    libpq-dev \
    ca-certificates \
 && docker-php-ext-install pdo pdo_pgsql \
 && a2enmod rewrite \
 && rm -rf /var/lib/apt/lists/*

RUN printf '%s\n' \
    '<Directory /var/www/html>' \
    '    AllowOverride All' \
    '    Require all granted' \
    '    DirectoryIndex index.php index.html index.htm' \
    '</Directory>' \
    > /etc/apache2/conf-available/app.conf \
 && a2enconf app

COPY . /var/www/html/
RUN chown -R www-data:www-data /var/www/html

EXPOSE 80
```

## Important Notes

- Localhost browser issues may be caused by browser history or autocomplete. If that happens, use `127.0.0.1` or a custom local hostname.
- Render free services spin down after inactivity and wake up on the next request.
- Use Supabase **pooler** settings, not direct connection settings, for the best compatibility.
- Avoid using Windows file paths in redirects, links, or JavaScript URLs.

## Recommended Local URLs

```text
http://127.0.0.1/collection-notice/index.php
http://127.0.0.1/collection-notice/dashboard.php
```

## Recommended Online URL

```text
https://your-service.onrender.com
```

## License

This project is for internal or educational use unless otherwise specified.

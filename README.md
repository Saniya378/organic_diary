# Organic Dairy Marketplace (Gilgit-Baltistan)

A high-performance, multi-vendor organic dairy e-commerce platform built with **Laravel 11**, **MySQL**, **Bootstrap 5**, and **Vanilla SCSS/JavaScript (via Vite)**. Designed specifically for the high-altitude valleys of Gilgit-Baltistan, Pakistan (Hunza, Skardu, Ghizer, Astore, Nagar, Diamer, Gilgit).

---

## 📋 System Requirements

- **PHP**: `^8.2` or `^8.3` (Extensions: `pdo_mysql`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `curl`, `fileinfo`)
- **Composer**: `^2.5`
- **Database**: MySQL `8.0+` or MariaDB `10.4+`
- **Node.js & NPM**: Node `18+` / `20+` & NPM `9+`
- **Web Server**: Nginx or Apache (with URL rewriting enabled)

---

## 🚀 Installation & Setup

### 1. Clone & Install Dependencies
```bash
# Clone the repository
git clone <repository_url> organic-dairy
cd organic-dairy

# Install PHP dependencies
composer install

# Install Frontend dependencies
npm install
```

### 2. Environment Configuration (`.env`)
Copy the example environment file and configure your database and application keys:

```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env` with your local database credentials:
```ini
APP_NAME="Organic Dairy"
APP_ENV=local
APP_KEY=base64:...
APP_DEBUG=true
APP_TIMEZONE="Asia/Karachi"
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=organic_diary
DB_USERNAME=root
DB_PASSWORD=

# Session, Cache & Queue
CACHE_STORE=database
QUEUE_CONNECTION=sync
SESSION_DRIVER=database

# Mail Configuration (log for development or SMTP for production)
MAIL_MAILER=log
MAIL_FROM_ADDRESS="salam@organicdairy.test"
MAIL_FROM_NAME="${APP_NAME}"

# Payment Configuration (mock or cod)
PAYMENT_GATEWAY_DRIVER=mock
```

### 3. Database Migration & Comprehensive Seeding
Run migrations and load full demo catalogs, real GB farm sellers, organic categories, products with variants, and sample multi-vendor orders:

```bash
php artisan migrate:fresh --seed
```

### 4. Storage Link & Asset Build
```bash
# Symlink public storage for uploaded product images and farm photos
php artisan storage:link

# Compile frontend assets with Vite
npm run build

# Or run Vite development server with hot-reload
npm run dev
```

### 5. Launch Local Server
```bash
php artisan serve
```
Access the application in your browser at `http://localhost:8000`.

---

## 👥 Demo Accounts

The database seeder provisions predefined accounts across all 3 user roles:

| Role | Email | Password | Description |
|---|---|---|---|
| **Platform Admin** | `admin@organicdairy.test` | `Admin@12345` | Full access to Admin Command Center, seller/product verification, users, orders, settings, and CSV financial reports. |
| **Approved Seller** | `hunza@organicdairy.test` | `Seller@12345` | **Hunza Valley Farm** (Live approved products, active inventory, order fulfillment progression). |
| **Approved Seller** | `skardu@organicdairy.test` | `Seller@12345` | **Skardu Farmers Co-op** (Yak dairy, desi butter, live shop catalog). |
| **Approved Seller** | `ghizer@organicdairy.test` | `Seller@12345` | **Ghizer Organic Dairy** (Goat milk, fresh paneer, artisan cheeses). |
| **Pending Seller** | `astore@organicdairy.test` | `Seller@12345` | **Astore Valley Farm** (Pending verification banner, cannot publish live products until admin approval). |
| **Buyer** | `buyer@organicdairy.test` | `Buyer@12345` | Ali Khan (Gilgit addresses, active orders, live 30s tracking timeline, verified review eligibility). |
| **Buyer** | `fatima@organicdairy.test` | `Buyer@12345` | Fatima Zahra (Skardu addresses). |

---

## ⏰ Background Scheduler & Automation

The application includes an automated cron schedule that cancels unpaid online payment orders (`pending_payment`) older than 30 minutes and restores reserved stock:

### Run the Scheduler Locally
```bash
php artisan schedule:work
```

### Run Manually on Demand
```bash
php artisan orders:cancel-unpaid
```

### Production Crontab Setup
Add the standard Laravel crontab entry on your server:
```cron
* * * * * cd /var/www/organic-dairy && php artisan schedule:run >> /dev/null 2>&1
```

---

## 🧪 Running Automated Tests

A comprehensive **73-test suite (396 assertions)** covers all core domains:

```bash
# Run all feature and unit tests
php artisan test
```

### Test Suite Structure:
- **`MarketplaceCoreWorkflowTest`**: Role redirects, unapproved seller publishing blocks, cart stock limits, atomic multi-vendor checkout split, mock payment failure status, single-step status progression, seller isolation (`403`), and verified delivery review gates.
- **`AdminPanelTest`**: KPI calculations, seller approval/rejection with emails, product verification, order cancellation with stock restoration, user suspension, categories CRUD, global settings update, payments ledger, and streamed CSV reports.
- **`SellerPanelTest`**: Seller KPI cards, Chart.js weekly sales, inline AJAX stock updates, single-step fulfillment progression with buyer emails, and profile management.
- **`CheckoutTest`**: Single-transaction database locking, multi-vendor order group creation, COD auto-stock reduction, and simulated payment gateway webhooks.
- **`BuyerOrdersTest`**: Buyer order listing, OrderPolicy isolation, live 30s polling endpoint, and buyer-side cancellation rules.
- **`CartTest`**: Database cart merge from guest `localStorage` on login, AJAX add-to-cart, quantity caps against live stock, and stepper calculation.
- **`ReviewsAndPolishTest`**: Delivery-gated product ratings, password reset token lifecycle, and custom branded 404 page.
- **`AuthAndProfileTest`**: Custom split-screen auth, rate-limiting (5 attempts/min), password rules (min 8 chars with letter & number), and address book CRUD.

---

## 💡 Architectural Decisions & Assumptions

1. **Multi-Vendor Checkout Split**:
   - Items in a unified cart are split into distinct `orders` per fulfilling seller under a single parent `order_group`.
   - Flat delivery fee from platform settings (default `Rs 150`) is applied per fulfilling vendor.
   - Platform commission (default `5%`) is automatically calculated on each seller's item subtotal.

2. **Atomic Inventory Reservation**:
   - Stock reduction occurs inside a single database transaction with row-level locking (`lockForUpdate()`) to prevent race conditions during high demand.
   - When an order is cancelled by the buyer or admin, stock is restored to both the parent product and the specific variant.

3. **Fulfillment State Machine**:
   - Orders strictly advance sequentially: `placed` $\rightarrow$ `confirmed` $\rightarrow$ `packing` $\rightarrow$ `in_transit` (requires rider name & phone) $\rightarrow$ `delivered`.
   - Cash on Delivery (COD) payments auto-settle to `paid` status when the seller marks the order `delivered`.

4. **Review Verification Gate**:
   - Reviews and 1–5 star ratings are permitted **only for verified buyers who have received that product in a `delivered` order**.
   - One review is enforced per product per delivered order.

5. **Design System & WCAG AA Contrast**:
   - Curated color tokens: Primary Green (`#2F5D3A`), Dark Forest (`#1F3A27`), Himalayan Gold (`#E0A93B`), Dark Gold (`#8F5E05` for AA contrast), Cream Background (`#FAF7F0`), and Muted Text (`#535E55` with $>5.5:1$ contrast ratio).
   - Fluid responsiveness optimized for 375px mobile viewports without horizontal scrollbars.

---

## 🌐 Production Deployment & Nginx Notes

### 1. Nginx Server Block Configuration
```nginx
server {
    listen 80;
    listen [::]:80;
    server_name organicdairy.pk www.organicdairy.pk;
    root /var/www/organic-dairy/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";
    add_header X-XSS-Protection "1; mode=block";

    index index.php index.html;
    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

### 2. Production Optimization Checklist
```bash
# Set environment
APP_ENV=production
APP_DEBUG=false

# Cache configuration, routes, and blade views
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Build optimized frontend bundle
npm run build

# Ensure correct file permissions
sudo chown -R www-data:www-data /var/www/organic-dairy/storage /var/www/organic-dairy/bootstrap/cache
sudo chmod -R 775 /var/www/organic-dairy/storage /var/www/organic-dairy/bootstrap/cache
```

### 3. Queue Worker with Supervisor (Optional for Async Mail)
```ini
[program:organic-dairy-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/organic-dairy/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/organic-dairy/storage/logs/worker.log
stopwaitsecs=3600
```

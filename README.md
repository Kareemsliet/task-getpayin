# Flash-Sale Checkout System

A Laravel checkout system for flash sales that prevents overselling through inventory holds and handles concurrent requests safely.

## Features

- ✅ Reserve items with 2-minute holds
- ✅ Prevent stock overselling with database locks
- ✅ Process payment webhooks with idempotency
- ✅ Auto-cleanup expired holds with background jobs

## Quick Setup

### 1. Clone & Install

```bash
git clone <repository-url>
cd task-getpayin
composer install
```

### 2. Configure Environment

```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env` and set your database:
```env
DB_DATABASE=getPayIn_task
DB_USERNAME=root
DB_PASSWORD=your_password
```

### 3. Setup Database

```bash
php artisan migrate
php artisan db:seed
```

**Option B - Separate terminals:**
```bash
# Terminal 1: Web server
php artisan serve

# Terminal 2: Queue worker
php artisan queue:listen

## Run Tests

```bash
composer run test
```

Or run specific tests:
```bash
php artisan test --filter HoldCreationTest
php artisan test --filter ProccessExpiredHoldsTest
php artisan test --filter ProccessWebhookPaymentTest
```

## API Endpoints

```http
GET  /api/products/{id}          # View product & stock
POST /api/holds                  # Reserve items (2 min hold)
POST /api/orders                 # Create order from hold
POST /api/payments/webhook       # Payment gateway callback
```

## Tech Stack

- **Framework:** Laravel 12.x
- **Testing:** Pest PHP
- **Database:** MySQL
- **Queue:** Database driver

---

**Project**: GetPayIn Interview Task
**README.md**
```markdown
# bKash Wallet Integration System

A robust wallet system built with Laravel 12 + Vue.js 3, featuring bKash Tokenized Checkout integration.

## Features

- Secure user authentication
- bKash account linking (Agreement-based)
- Add money with atomic transactions
- Transaction history with pagination
- PDF statement generation (Gotenberg)
- Multi-language support (English/Bangla)
- Redis-based payment locking (prevent double submission)
- Partial refund support

## Tech Stack

- **Backend**: Laravel 12 (PHP 8.2)
- **Frontend**: Vue.js 3 + Tailwind CSS
- **Database**: MySQL 8.0
- **Cache/Locks**: Redis 7
- **PDF Generation**: Gotenberg
- **Payment Gateway**: bKash Tokenized Checkout

## Architecture Decisions

### 1. Monolithic Architecture
Chose Laravel + Vue.js monolith over separate frontend because:
- Laravel Sanctum (Session / Token-based)
- Easier deployment
- Better for this project scope

### 2. Redis for Atomic Locks
Used Redis `SETNX` to prevent race conditions during payment:
- Prevents double-submission when user clicks "Pay" multiple times
- 120-second lock expiry
- Automatic cleanup after transaction

### 3. Encrypted Agreement Tokens
bKash agreement tokens are encrypted in database using Laravel's Crypt facade:
- Protects sensitive payment credentials
- Automatic encryption/decryption via model accessors

### 4. Gotenberg for PDF (NOT DomPDF)
Using Gotenberg microservice instead of DomPDF:
- Better HTML/CSS rendering
- Handles complex layouts
- Faster performance
- Dockerized service
```

```bash
## Installation

### Prerequisites
- PHP 8.2+
- Composer
- Node.js 18+
- MySQL 8.0+
- Docker & Docker Compose
```

### Step 1: Clone Repository
```bash
git clone https://github.com/Nasim25/wallet-system.git
cd bkash-wallet
```

### Step 2: Install Dependencies
```bash
composer install
npm install
```

### Step 3: Environment Setup
```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env`:
```env
DB_DATABASE=bkash_wallet
DB_USERNAME=root
DB_PASSWORD=your_password

BKASH_BASE_URL=https://tokenized.sandbox.bka.sh/v1.2.0-beta
BKASH_APP_KEY=your_app_key
BKASH_APP_SECRET=your_app_secret
BKASH_USERNAME=your_username
BKASH_PASSWORD=your_password

GOTENBERG_URL=http://localhost:3000
```

### Step 4: Database Migration
```bash
php artisan migrate
```

### Step 5: Start Docker Services
```bash
docker-compose up -d
```

### Step 6: Build Frontend
```bash
npm run dev
```

### Step 7: Start Laravel
```bash
php artisan serve
```

Visit: `http://localhost:8000`


## Author

Md Nasim Uddin (nasimuddin1140@gmail.com)


# JuicePOS / Liosync POS

A comprehensive, multi-tenant Point of Sale (POS) and inventory management system designed for retail and F&B businesses. Built with Laravel, Filament, and Tailwind CSS.

## 🚀 Features

### Core Features
- **Multi-Tenancy**: Superadmin > Company > Branch hierarchy.
- **Point of Sale (POS)**: Fast, full-screen POS interface with cart management, barcode search, and receipt printing.
- **Inventory Management**: Track stock, handle purchase orders (restocking), and manage product recipes (BOM).
- **Reporting**: Sales, stock, and financial reports with export capabilities.

### Subscription System
- **Company Subscriptions**: Companies purchase plans to activate features/branches.
- **Bank Transfer Payment**: Secure manual payment flow with proof upload.
- **Admin Approval**: Superadmins verify and approve subscription payments to activate services.

## 🛠️ Tech Stack
- **Framework**: Laravel 12.0 (PHP 8.2+)
- **Frontend**: Blade, Alpine.js, Tailwind CSS
- **Admin Panel**: Filament PHP
- **Database**: MariaDB/MySQL

## 📦 Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/yourusername/liosync-pos.git
   cd liosync-pos
   ```

2. **Install Dependencies**
   ```bash
   composer install
   npm install && npm run build
   ```

3. **Environment Setup**
   ```bash
   cp .env.example .env
   php artisan key:generate
   # Configure database settings in .env
   ```

4. **Database Migration & Seeding**
   ```bash
   php artisan migrate --seed
   # This will seed default roles, permissions, and demo users
   ```

## 📚 Documentation
- [PRD.md](PRD.md) - Product Requirements & Features
- [CREDENTIALS.txt](CREDENTIALS.txt) - List of demo users and hierarchy

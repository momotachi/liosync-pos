# Product Requirements Document (PRD) - LioSync POS

## 1. Introduction
**LioSync POS** is a multi-tenant Point of Sale (POS) and business management system designed to serve multiple companies, each with multiple branches. It supports a hierarchy of roles to manage operations from a super-admin level down to individual cashiers.

## 2. User Roles & Permissions
The system defines the following specific roles using `Spatie\Permission`:

### 2.1. Superadmin (Platform Owner)
*   **Scope:** Entire System.
*   **Responsibilities:**
    *   Manage Companies (Tenants).
    *   Manage Subscriptions & Billing.
    *   Oversee Platform Health & Global Settings.
*   **Key Features:**
    *   Global Dashboard.
    *   Company Management (Create, Suspend, Delete).
    *   Subscription Plans Management.

### 2.2. Company Admin (Tenant Owner)
*   **Scope:** Single Company (All Branches).
*   **Responsibilities:**
    *   Manage Branches.
    *   Manage Staff (Branch Admins, Stock Admins, Cashiers).
    *   Manage Products & Pricing (Global or Per-Branch).
    *   View Consolidated Reports.
*   **Key Features:**
    *   Company Dashboard.
    *   Branch Management.
    *   Product Catalog Management.
    *   Staff User Management.

### 2.3. Branch Admin (Manager)
*   **Scope:** Single Branch.
*   **Responsibilities:**
    *   Oversee Branch Operations.
    *   Manage Branch Inventory (if separate).
    *   View Branch Reports.
*   **Key Features:**
    *   Branch Dashboard.
    *   Daily Sales Reports.
    *   Shift Management.

### 2.4. Stock Admin
*   **Scope:** Inventory Management (Company or Branch level).
*   **Responsibilities:**
    *   Manage Stock Levels.
    *   Handle Purchase Orders (Restocking).
    *   Manage Suppliers & Materials.
*   **Key Features:**
    *   Stock Alerts.
    *   Inventory Adjustment.
    *   BOM (Bill of Materials) Management (for recipe-based deduction).

### 2.5. Cashier
*   **Scope:** POS Terminal.
*   **Responsibilities:**
    *   Process Sales Orders.
    *   Kitchen Printing.
    *   process Payments (Cash, QR, Card).
*   **Key Features:**
    *   POS Interface (Touch-friendly).
    *   Receipt Printing.
    *   Order Queue Management.

## 3. Functional Requirements

### 3.1. Authentication & Security
*   **Web Auth:** Standard Login/Logout with Role-based redirection.
*   **Mobile Auth:** `mobile.auth` middleware for API-token based authentication via WebView.
*   **Sessions:** Secure session management with activity tracking.

### 3.2. POS System
*   **Interface:** Grid-based product selection, Cart view.
*   **Orders:** Create, Pending (Hold), Cancel, Checkout.
*   **Payments:** Multiple methods supported.
*   **Receipts:** Thermal printer support (via browser print or raw commands).

### 3.3. Inventory Management
*   **Products:** Simple products & Composite products (Recipes/BOM).
*   **Stock:** Real-time deduction upon sale.
*   **Alerts:** Low stock notifications.

### 3.4. Reporting
*   **Sales:** Daily, Weekly, Monthly revenue.
*   **Products:** Best/Worst selling items.
*   **Staff:** Performance tracking.

## 4. Technical Constraints
*   **Framework:** Laravel (PHP).
*   **Database:** MySQL/PostgreSQL.
*   **Frontend:** Blade Templates + Vanilla JS / Alpine.js (Lightweight).
*   **Mobile:** Hybrid App (WebView wrapper with Native Bridge).

## 5. Mobile Bridge Features
*   The application supports a JavaScript bridge for:
    *   Native Printing (Bluetooth/USB).
    *   Native Scanning (Camera/Hardware).
    *   Push Notifications (FCM).

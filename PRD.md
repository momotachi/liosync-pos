# Product Requirements Document (PRD) - JuicePOS / Cashier System

## 1. Project Overview
**Project Name:** JuicePOS (Cashier System)
**Type:** Web-based Point of Sales (POS) & Inventory Management System
**Architecture:** Multi-Tenant (Superadmin > Company > Branch)

### Executive Summary
This project is a comprehensive Point of Sale (POS) and inventory management system designed for retail and F&B businesses. It supports a hierarchical structure allowing Superadmins to manage multiple companies, Company Admins to manage branches, and Branch Managers/Cashiers to handle daily operations.

## 2. Technology Stack
*   **Framework:** Laravel 12.0 (PHP 8.2+)
*   **Frontend:** Blade Templates, Alpine.js, Tailwind CSS
*   **Admin Panel:** Filament
*   **Database:** MariaDB/MySQL
*   **PDF Generation:** barryvdh/laravel-dompdf
*   **Role Management:** spatie/laravel-permission

## 3. User Roles & Permissions

| Role | Responsibility | Key Features Access |
| :--- | :--- | :--- |
| **Superadmin** | System Owner | Manage Companies, Subscription Plans, Global Payments, Switch Context. |
| **Company Admin** | Business Owner | Manage Branches, Company Users, Company Subscriptions, Dashboard. |
| **Branch Admin** | Store Manager | Manage Items (Products/Raw Materials), Reports, Stock, Settings, Branch Users. |
| **Cashier** | Front-line Staff | POS Interface, Process Orders, Print Receipts. |

## 4. Key Features

### 4.1 Point of Sale (POS)
*   **Transactions:** Process sales with efficient cart management.
*   **Payment Methods:** Support for Cash, QRIS, and Debit payments.
*   **Receipts:** Thermal receipt printing (Normal, Kitchen, Table).
*   **Pending Orders:** Ability to save and resume orders (e.g., for restaurant tables).
*   **Search:** Quick product lookup by name or barcode.

### 4.2 Inventory & Product Management
*   **Unified Items:** Management of Products (sellable) and Raw Materials (consumable).
*   **BOM (Bill of Materials):** Define recipes for products to auto-deduct raw materials upon sale.
*   **Stock Management:**
    *   Stock Adjustments (Lost/Damaged).
    *   Restocking (Purchase Orders).
    *   Low Stock Alerts.
*   **Categories:** Organize items into categories.

### 4.3 Reporting & Analytics
*   **Sales Reports:** transaction history, revenue analysis.
*   **Inventory Reports:** Stock levels, valuation, movement history.
*   **Export:** Capability to export reports to PDF and Excel/CSV.
*   **Dashboard:** Visual metrics for daily performance (Revenue, Orders, Top Items).

### 4.4 Multi-Tenancy & Subscriptions
*   **Hierarchy:** Clear separation of data between companies and branches.
*   **Subscriptions:** Companies purchase plans to activate branches/features.
*   **Context Switching:** Superadmins can "login as" company admins for support.

## 5. System Flows

### 5.1 High-Level User Journey
```mermaid
flowchart TD
    START([User Starts]) --> AUTH{Has Session?}

    AUTH -->|No| LOGIN[Login Page<br/>/login]
    LOGIN --> CREDENTIALS[Enter Email + Password]
    CREDENTIALS --> VALIDATE{Credentials Valid?}
    VALIDATE -->|No| LOGIN
    VALIDATE -->|Yes| CREATE_SESSION[Create Session]
    CREATE_SESSION --> HOME
    
    AUTH -->|Yes| HOME([Home Page<br/>/])
    
    HOME --> ROLE{User Role}
    
    ROLE -->|Cashier| POS[POS Page<br/>/pos]
    ROLE -->|Superadmin| S_DASH[Super Dashboard<br/>/superadmin]
    ROLE -->|Company Admin| C_DASH[Company Dashboard<br/>/company]
    ROLE -->|Branch Admin| B_DASH[Branch Dashboard<br/>/branch]
    
    POS --> POS_FLOW[POS Transaction Flow]
    
    B_DASH --> NAV_ITEMS[Items Management]
    B_DASH --> NAV_REPORTS[Reports]
    B_DASH --> NAV_SETTINGS[Settings]
    
    C_DASH --> C_BRANCHES[Manage Branches]
    C_DASH --> C_USERS[Manage Users]
    
    S_DASH --> S_COMPANIES[Manage Companies]
    S_DASH --> S_PLANS[Subscription Plans]

    style LOGIN fill:#fff3e0
    style POS fill:#e1f5fe
    style B_DASH fill:#c8e6c9
    style C_DASH fill:#d1c4e9
    style S_DASH fill:#ffccbc
```

### 5.2 POS Transaction Process
```mermaid
sequenceDiagram
    actor Cashier
    participant UI as POS Interface
    participant Server
    participant DB as Database

    Cashier->>UI: Add Items to Cart
    Cashier->>UI: Click Checkout
    UI->>Cashier: Show Payment Modal
    Cashier->>UI: Select Payment (Cash/QRIS) & Confirm
    UI->>Server: POST /checkout (Order Data)
    Server->>Server: Validate Stock (BOM check)
    alt Stock Available
        Server->>DB: Create Order & OrderItems
        Server->>DB: Deduct Stock (Raw Materials)
        Server-->>UI: Success Response
        UI->>Cashier: Show Success & Print Receipt
    else Insufficient Stock
        Server-->>UI: Error Message
        UI->>Cashier: Alert "Insufficient Stock"
    end
```

### 5.3 Inventory Restock Flow
```mermaid
flowchart LR
    Start([Manager]) --> View[View Low Stock]
    View --> Action[Click Restock]
    Action --> Modal[Input Qty & Cost]
    Modal --> Submit[Submit POST]
    Submit --> UpdateDB[Update Stock Level]
    UpdateDB --> Record[Record Transaction History]
    Record --> End([Stock Updated])
```

## 6. Detailed Requirements

### 6.1 Authentication
*   **Login:** Email/Password based.
*   **Redirects:**
    *   Cashiers -> `/pos`
    *   Admins -> Respective Dashboards
*   **Security:** Role-based access control (RBAC) middleware.

### 6.2 POS Interface
*   **Layout:** Full-screen optimized.
*   **Left Panel:** Product Grid with Categories.
*   **Right Panel:** Cart summary, Customer selection, Calculations (Tax, Total).
*   **Shortcuts:** Keyboard support for efficient operation (Search, Checkout).

### 6.3 Administration
*   **CRUD Operations:** Standard tables with searching, filtering, and pagination for all resources (Items, Users, Branches).
*   **Validation:** Server-side validation for all inputs (e.g., negative stock prevention).
*   **Audit:** Track who created/modified records.

## 7. Future Roadmap
*   **Kitchen Display System (KDS):** Digital screen for kitchen staff.
*   **Customer Loyalty:** Points and rewards system.
*   **Offline Mode:** PWA support for handling network interruptions.

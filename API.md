# LioSync API Requirements

**Version:** 1.0.0
**Target:** Mobile/Tablet Application (Flutter)
**Base URL:** `https://liosync.hasgroup.id/api/v1`

---

## Authentication & Authorization

### Authentication Method
- **Method:** Laravel Sanctum (Bearer Token)
- **Header:** `Authorization: Bearer {token}`

### Endpoints
#### 1. Login
- `POST /api/v1/auth/login`
- Body: `email`, `password`, `device_name`

#### 2. Logout
- `POST /api/v1/auth/logout`

#### 3. Get Current User
- `GET /api/v1/auth/me`

---

## items (Products & Raw Materials)

### Endpoints
#### 5. List Items
- `GET /api/v1/items`
- Params: `page`, `per_page`, `search`, `category_id`, `is_sales`, `is_purchase`

#### 6. Get Item Detail
- `GET /api/v1/items/{id}`

#### 7. Create Item
- `POST /api/v1/items`

#### 8. Update Item
- `PUT /api/v1/items/{id}`

#### 9. Delete Item
- `DELETE /api/v1/items/{id}`

---

## Categories

### Endpoints
#### 10. List Categories
- `GET /api/v1/categories`

---

## Orders / POS

### Endpoints
#### 11. Create Order (Checkout)
- `POST /api/v1/orders`
- Body: `customer_name`, `payment_method`, `items: [{item_id, quantity}]`

#### 12. List Orders
- `GET /api/v1/orders`

#### 13. Get Order Detail
- `GET /api/v1/orders/{id}`

#### 14. Update Order Status
- `PUT /api/v1/orders/{id}/status`

#### 15. Cancel Order
- `POST /api/v1/orders/{id}/cancel`

---

## Stock / Inventory

### Endpoints
#### 16. Stock Transactions History
- `GET /api/v1/stock-transactions`

#### 17. Restock Item
- `POST /api/v1/stock/restock`

#### 18. Adjust Stock
- `POST /api/v1/stock/adjust`

#### 19. Get Low Stock Items
- `GET /api/v1/stock/low-stock`

---

## Reports

### Endpoints
#### 20. Sales Report
- `GET /api/v1/reports/sales`

#### 21. Inventory Report
- `GET /api/v1/reports/inventory`

#### 22. Export Report
- `POST /api/v1/reports/export`

---

## Settings

### Endpoints
#### 23. Get App Settings
- `GET /api/v1/settings`

---

## Users

### Endpoints
#### 24. List Users
- `GET /api/v1/users`

#### 25. Create User
- `POST /api/v1/users`

---

## Print

### Endpoints
#### 26. Generate Receipt
- `GET /api/v1/orders/{id}/receipt`

# AL BARSHA Invoice API

Laravel API project for invoice and staff management using Sanctum authentication.

## Stack

- Laravel 12 (latest supported by local PHP)
- Sanctum (token-based API auth)
- MySQL (configured in `.env.example`)
- DomPDF for invoice PDF generation
- API Resources + Form Requests
- Service + Repository layers for clean architecture

## Setup

1. Install dependencies:
   - `composer install`
2. Configure `.env` and set MySQL credentials.
3. Generate app key:
   - `php artisan key:generate`
4. Link storage:
   - `php artisan storage:link`
5. Run migrations and seeders:
   - `php artisan migrate --seed`
6. Start server:
   - `php artisan serve`

## Authentication

- `POST /api/auth/login`
- `POST /api/auth/logout` (requires `Authorization: Bearer <token>`)

Login request:

```json
{
  "email": "admin@albarsha.com",
  "password": "password123"
}
```

Login response:

```json
{
  "message": "Login successful.",
  "token": "1|....",
  "user": {
    "id": 1,
    "name": "Admin User",
    "email": "admin@albarsha.com"
  }
}
```

## Main APIs

- Staff CRUD: `/api/staff`
- Customer CRUD: `/api/customers`
- Invoice:
  - `POST /api/invoices`
  - `GET /api/invoices` with filters: `invoice_number`, `customer_name`, `date`
  - `GET /api/invoices/{invoice}`
  - `GET /api/invoices/{invoice}/pdf`

## Create Invoice Example

Request:

```json
{
  "customer_name": "John",
  "date": "2026-03-25",
  "staff_id": 1,
  "items": [
    {
      "product_name": "Typing Work",
      "quantity": 2,
      "unit_price": 50
    },
    {
      "product_name": "Printing",
      "quantity": 5,
      "unit_price": 10
    }
  ]
}
```

Response shape:

```json
{
  "data": {
    "id": 1,
    "invoice_number": "INV-20260325-0001",
    "customer_name": "John",
    "date": "2026-03-25",
    "staff_id": 1,
    "total_amount": 150,
    "pdf_url": "http://localhost:8000/api/invoices/1/pdf",
    "staff": {
      "id": 1,
      "name": "Ali Hassan"
    },
    "items": [
      {
        "product_name": "Typing Work",
        "quantity": 2,
        "unit_price": 50,
        "total_price": 100
      }
    ]
  }
}
```

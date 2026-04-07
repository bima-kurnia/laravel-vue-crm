# CRM

A multi-tenant CRM web application with a Laravel API backend and a Vue 3 frontend.

## Tech stack

| Layer | Technology |
|---|---|
| Backend | Laravel (latest), PHP 8.2+ |
| Authentication | Laravel Sanctum — Bearer token, stateless |
| Frontend | Vue 3, Vite, Vue Router, Pinia, PrimeVue (Aura) |
| Database | PostgreSQL |
| Architecture | Shared database, shared tables, `tenant_id` isolation |

## Project structure

```
crm/
├── backend/    # Laravel API
└── frontend/   # Vue 3 SPA
```

## Prerequisites

- PHP 8.2+ with the `pdo_pgsql` extension enabled
- Composer
- Node.js 18+
- PostgreSQL 14+

---

## Backend setup
```bash
cd backend

# Install dependencies
composer install

# Copy environment file and configure it
cp .env.example .env

# Generate application key
php artisan key:generate
```

Open `backend/.env` and set your database credentials:
```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=crm
DB_USERNAME=your_db_user
DB_PASSWORD=your_db_password
```

Then run migrations:
```bash
php artisan migrate
```

Start the development server:
```bash
php artisan serve
```

The API will be available at `http://localhost:8000`.

---

## Frontend setup
```bash
cd frontend

# Install dependencies
npm install

# Copy environment file
cp .env.example .env
```

Open `frontend/.env` and set the API URL:
```env
VITE_API_BASE_URL=http://localhost:8000/api
```

Start the development server:
```bash
npm run dev
```

The app will be available at `http://localhost:5173`.

---

## API overview

All protected routes require a `Bearer` token in the `Authorization` header.

| Method | Endpoint | Description |
|---|---|---|
| `POST` | `/api/register` | Create a new tenant and owner account |
| `POST` | `/api/auth/login` | Authenticate and receive a token |
| `POST` | `/api/auth/logout` | Revoke the current token |
| `GET` | `/api/auth/me` | Get the authenticated user |
| `GET` | `/api/customers` | List customers (filterable, paginated) |
| `POST` | `/api/customers` | Create a customer |
| `GET` | `/api/customers/{id}` | Get a customer |
| `PATCH` | `/api/customers/{id}` | Update a customer |
| `DELETE` | `/api/customers/{id}` | Soft delete a customer |
| `GET` | `/api/deals` | List deals (filterable, paginated) |
| `POST` | `/api/deals` | Create a deal |
| `PATCH` | `/api/deals/{id}/stage` | Move a deal to a new pipeline stage |
| `GET` | `/api/deals/pipeline` | Pipeline summary by stage |
| `GET` | `/api/activities` | Activity log (filterable, paginated) |
| `GET` | `/api/activities/subject/{type}/{id}` | Activity feed for a subject |
| `GET` | `/api/activities/deals/{id}/stage-history` | Stage history for a deal |

## Multi-tenancy

Tenant isolation is enforced at two layers:

- **Global Eloquent scope** — a `BelongsToTenant` trait automatically appends `WHERE tenant_id = ?` to every query on scoped models.
- **Service layer** — every mutating operation re-validates ownership. Cross-tenant access returns `404`, never `403`, to avoid confirming record existence.

`tenant_id` is never exposed in API responses or accessible from the frontend.

## Roles

| Role | Permissions |
|---|---|
| `owner` | Full access including force-delete and tenant management |
| `admin` | Create, update, soft-delete, restore |
| `member` | Create and update only |

## License

Private — all rights reserved.
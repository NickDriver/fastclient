# FastClient CRM - Development Guide

## Project Overview
FastClient is a lightweight CRM system built with PHP 8.4, PostgreSQL, HTMX, and Tailwind CSS. It uses a custom MVC architecture without Composer dependencies.

## Tech Stack
- **Backend**: PHP 8.4 (no Composer, custom autoloader)
- **Database**: PostgreSQL
- **Frontend**: HTMX for interactivity, Tailwind CSS v4 for styling
- **Build Tools**: Bun for JS/CSS bundling

## Project Structure
```
src/
├── Auth/           # Authentication (Auth.php, Middleware.php)
├── Controllers/    # Request handlers
├── Database/       # Connection.php + migrations/*.sql
├── Models/         # Customer, CustomerNote, User
├── Views/          # PHP templates with layouts
├── Router.php      # Custom routing
└── bootstrap.php   # Autoloader and env loading

public/             # Web root (index.php entry point)
config/             # app.php, database.php
resources/          # Source CSS/TS files
```

## Key Commands
```bash
# Development
bun run dev          # Start dev server with hot reload
bun run build        # Build production assets

# Database
php migrate.php      # Run pending migrations

# Production deployment
sudo ./install.sh    # Automated Debian 12 installation
```

## Database Migrations
Migrations are SQL files in `src/Database/migrations/` with naming convention `NNN_description.sql`. Run via `php migrate.php`.

## Routing
Routes defined in `public/index.php`. Protected routes use `Middleware::class` for auth.

Pattern: `$router->get('/path/{id}', [Controller::class, 'method'])`

## Views
- Layouts: `src/Views/layouts/` (app.php for authenticated, auth.php for login)
- Partials: Use `include` with relative paths
- HTMX: Use `hx-*` attributes for dynamic updates

## Models
Active Record pattern. Key methods:
- `Model::all()`, `Model::find($id)`, `Model::create($data)`
- Instance methods: `$model->update($data)`, `$model->delete()`

## Environment
Copy `.env.example` to `.env`. Required variables:
- `APP_URL`, `APP_ENV`, `APP_DEBUG`
- `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`

## Code Style
- PHP 8.4 features: typed properties, named arguments, match expressions
- Strict types enabled (`declare(strict_types=1)`)
- PSR-4 style namespacing under `App\`

## Testing Locally
```bash
php -S localhost:8000 -t public  # Built-in PHP server
bun run dev                       # Full dev environment with live reload
```

## Common Tasks

### Adding a New Route
1. Add route in `public/index.php`
2. Create/update controller method
3. Create view if needed

### Adding a Migration
1. Create `src/Database/migrations/NNN_description.sql`
2. Run `php migrate.php`

### Adding a Model
1. Create class in `src/Models/`
2. Implement static `hydrate()`, `all()`, `find()`, `create()` methods
3. Add instance `update()`, `delete()` methods

## Customer Statuses
- `new` - New lead
- `contacted` - Initial contact made
- `callback` - Scheduled callback
- `follow_up` - Requires follow-up

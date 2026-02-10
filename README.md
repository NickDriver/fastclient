# FastClient CRM

A lightweight, fast Customer Relationship Management system built with modern PHP and HTMX.

## Features

- **Customer Management** - Add, edit, view, and delete customer records
- **Status Tracking** - Track customers through stages: New, Contacted, Callback, Follow Up
- **Notes System** - Add timestamped notes to customer records
- **CSV Import/Export** - Bulk import customers from CSV, export data for reporting
- **Search & Filter** - Quick search across customer fields, filter by status
- **Responsive UI** - Clean, modern interface that works on desktop and mobile
- **User Authentication** - Secure login with session-based authentication

## Tech Stack

- **PHP 8.4** - Modern PHP with strict typing (no Composer required)
- **PostgreSQL** - Reliable relational database
- **HTMX** - Dynamic interactions without complex JavaScript
- **Tailwind CSS v4** - Utility-first styling
- **Bun** - Fast JavaScript runtime for asset building

## Requirements

- PHP 8.4 with extensions: pdo_pgsql, mbstring
- PostgreSQL 12+
- Bun runtime
- Nginx (for production)

## Quick Start (Development)

1. Clone the repository
2. Copy environment file:
   ```bash
   cp .env.example .env
   ```
3. Configure database credentials in `.env`
4. Install dependencies and build assets:
   ```bash
   bun install
   bun run build
   ```
5. Run migrations:
   ```bash
   php migrate.php
   ```
6. Start development server:
   ```bash
   bun run dev
   ```
7. Visit `http://localhost:8000`

## Production Deployment (Debian 12)

Run the automated installation script:

```bash
sudo ./install.sh
```

The script will:
- Install PHP 8.4 and required extensions
- Install Bun runtime
- Configure PostgreSQL database
- Set up Nginx with SSL (via Certbot)
- Build assets and run migrations

## Project Structure

```
fastclient/
├── public/              # Web root
│   ├── index.php        # Application entry point
│   └── assets/          # Compiled CSS/JS
├── src/
│   ├── Auth/            # Authentication system
│   ├── Controllers/     # Request handlers
│   ├── Database/        # Connection + migrations
│   ├── Models/          # Data models (Customer, User, etc.)
│   ├── Views/           # PHP templates
│   ├── Router.php       # URL routing
│   └── bootstrap.php    # Autoloader
├── config/              # Configuration files
├── resources/           # Source CSS/TypeScript
├── install.sh           # Production installer
└── migrate.php          # Database migration runner
```

## Customer Fields

| Field | Description |
|-------|-------------|
| Name | Customer/company name |
| Email | Contact email |
| Phone | Contact phone number |
| Website | Company website (optional) |
| City | City location |
| State | State/region |
| Industry | Business industry (optional) |
| Status | Current pipeline status |

## CSV Import Format

The CSV importer accepts files with the following columns:
- name, email, phone, website, city, state, industry

First row should be headers. Email is optional.

## Environment Variables

| Variable | Description | Default |
|----------|-------------|---------|
| APP_NAME | Application name | FastClient |
| APP_ENV | Environment (development/production) | development |
| APP_DEBUG | Enable debug mode | true |
| APP_URL | Application URL | http://localhost:8000 |
| DB_HOST | Database host | localhost |
| DB_PORT | Database port | 5432 |
| DB_DATABASE | Database name | fastclient |
| DB_USERNAME | Database user | - |
| DB_PASSWORD | Database password | - |

## License

MIT

# Support Portal

Support Portal is a web-based application for managing customer support requests. It is built with **Laravel 12**, **Livewire 3**, and **Tailwind CSS**, providing a modern stack for building responsive, reactive interfaces.

## Requirements

- PHP 8.2+
- Node.js 18+
- Composer
- npm

## Getting Started

1. Install PHP dependencies: `composer install`
2. Install JavaScript dependencies: `npm install`
3. Copy the example environment: `cp .env.example .env` and adjust settings
4. Generate an application key: `php artisan key:generate`
5. Run database migrations: `php artisan migrate`
6. Start development servers: `php artisan serve` and `npm run dev`

## Project Structure

The repository is organized as follows:

- `app/` – Application code including models, controllers, Livewire components, policies, services, console commands and traits.
- `bootstrap/` – Framework bootstrapping and cached files.
- `config/` – Configuration files for the framework and third‑party packages.
- `database/` – Database migrations, seeders and model factories.
- `public/` – Front controller (`index.php`) and publicly accessible assets.
- `resources/` – Frontend resources.
  - `views/` – Blade templates for the UI.
  - `js/` and `css/` – Source assets compiled via Vite.
- `routes/` – Route definitions for web and API endpoints.
- `storage/` – Compiled templates, file uploads and logs.
- `tests/` – Feature and unit test suites.
- `DATABASE_MIGRATION_GUIDE.md` – Notes on database setup and migrations.
- `STYLING_GUIDE.md` – Frontend styling conventions.
- `USER_ROLE_MANAGEMENT.md` – Documentation for role and permission management.

## Contributing

Pull requests are welcome. Please ensure code style is maintained and tests are updated.

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).


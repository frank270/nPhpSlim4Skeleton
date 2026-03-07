# Repository Guidelines

## Project Structure & Module Organization
- Source: `app/` (Actions, Routes, Middleware, Models, Templates, Utils). Example: actions end with `*Action.php`, models end with `*Model.php`.
- Entry point: `public/index.php`; static assets in `public/` (built JS under `public/js/opanel`).
- Frontend (admin): `resources/react-opanel` (Vite + React).
- Database: Doctrine Migrations in `migrations/` with config in `migrations.php` and `migrations-db.php`.
- Docs: `docs/`; Docker and infra in `docker/`; logs in `logs/`.

## Build, Test, and Development Commands
- Install PHP deps: `composer install`
- Run locally (PHP dev server): `php -S localhost:8080 -t public`
- Docker up: `docker compose up -d` (PHP, Nginx, MySQL, Redis; app at `https://ndev.local:8243`).
- React admin dev: `cd resources/react-opanel && npm run dev`
- React admin build: `cd resources/react-opanel && npm run build` (outputs to `public/js/opanel`).
- Migrations status: `docker compose exec app php vendor/bin/doctrine-migrations status --configuration=/var/www/migrations.php --db-configuration=/var/www/migrations-db.php`
- Migrate: `docker compose exec app php vendor/bin/doctrine-migrations migrate --configuration=/var/www/migrations.php --db-configuration=/var/www/migrations-db.php`

## Coding Style & Naming Conventions
- PHP: Follow PSR-12, 4-space indentation, `declare(strict_types=1);`. Classes `StudlyCase`, methods/vars `camelCase`.
- Actions: `XxxAction.php`; Models: `XxxModel.php`; route files `snake_case` (e.g., `opanel_users.php`).
- Twig templates: `snake-case.twig` when possible; keep small, reusable blocks.
- JS/React: ESLint configured (`npm run lint`). Prefer functional components and hooks; files `PascalCase.jsx` for components.
- **IMPORTANT**: Opanel React apps MUST use `application/x-www-form-urlencoded` (via `URLSearchParams`) for all POST/PUT requests. Do NOT use JSON.

## Testing Guidelines
- PHP tests are not yet configured. Recommended: PHPUnit with tests in `tests/` mirroring `app/` namespaces.
- For React, add Vitest/RTL tests in `resources/react-opanel/src/**/__tests__` and wire to `npm test`.
- Aim for coverage on actions, middleware, and critical utilities. Include sample requests/responses.

## Commit & Pull Request Guidelines
- Commit style: Conventional Commits (`feat:`, `fix:`, `chore:`, `docs:`) as seen in history.
- Include concise scope and what/why; link issues (`Closes #123`).
- PRs must include: purpose, screenshots/GIFs for UI, migration notes (if touching DB), steps to test, and docs updates (e.g., `docs/` or README).
- Ensure `npm run build` (admin) and migrations run cleanly in Docker before requesting review.

## Security & Configuration Tips
- Never commit secrets; use `.env` (see `.env.sample` / `.env.docker.sample`).
- Local domains require hosts entries and SSL via `mkcert` (see README).
- Logs write to `logs/`; avoid sensitive data in logs and sanitize user input.


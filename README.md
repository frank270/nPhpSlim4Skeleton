# Slim 4 Skeleton with Twig & Eloquent

A lightweight Slim 4 project skeleton with Twig templating, PHP-DI container, Eloquent ORM, and Dotenv support.

**Perfect for building fast, clean, and flexible PHP applications.**

---

## Features

- 🛠️ Slim 4 (Minimal and fast)
- 🎨 Twig 3 Template Engine
- 📦 PHP-DI (Dependency Injection Container)
- 🗄️ Illuminate Eloquent ORM (Database ORM)
- 🔐 Dotenv (.env configuration support)
- 🛎️ Slim Flash Message (Session flash message)
- 📝 Monolog (Logging)
- 🎯 PSR-7 HTTP Messages (via slim/psr7)

---

## Installation

```bash
composer create-project youraccount/your-slim4-skeleton project-name
```

Or clone the repository:

```bash
git clone https://github.com/youraccount/your-slim4-skeleton.git
cd your-slim4-skeleton
composer install
```

---

## Project Structure

```
app/
  src/          → PHP Source files (Actions, Controllers)
  templates/    → Twig templates
  settings.php  → Application settings
  database.php  → Database connection setup (Eloquent)
  dependencies.php → Container dependencies
  middleware.php → Global middlewares
  routes.php    → Route definitions

cache/
  twig/         → Twig cache

log/
  app.log       → Application logs

public/
  index.php     → Entry point
  .htaccess     → Apache rewrite rules
```

---

## Environment Configuration

Copy `.env.example` and create your own `.env` file:

```bash
cp .env.example .env
```

Set your environment variables:

```dotenv
APP_ENV=local
APP_DEBUG=true

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

---

## Basic Usage

### Define a Route

In `app/routes.php`:

```php
$app->get('/hello/{name}', App\HomeAction::class);
```

### Create an Action

In `app/src/HomeAction.php`:

```php
public function __invoke(Request $request, Response $response, array $args): Response
{
    $name = $args['name'] ?? 'Guest';
    return $this->view->render($response, 'hello.twig', ['name' => $name]);
}
```

### Create a Twig Template

In `app/templates/hello.twig`:

```twig
<h1>Hello, {{ name }}!</h1>
```

---

## Requirements

- PHP 8.0+
- Composer
- Web Server (Apache / Nginx)

---

## License

This project is open-sourced under the [MIT license](LICENSE).

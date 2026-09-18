<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

In addition, [Laracasts](https://laracasts.com) contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

You can also watch bite-sized lessons with real-world projects on [Laravel Learn](https://laravel.com/learn), where you will be guided through building a Laravel application from scratch while learning PHP fundamentals.

## Agentic Development

Laravel's predictable structure and conventions make it ideal for AI coding agents like Claude Code, Cursor, and GitHub Copilot. Install [Laravel Boost](https://laravel.com/docs/ai) to supercharge your AI workflow:

```bash
composer require laravel/boost --dev

php artisan boost:install
```

Boost provides your agent 15+ tools and skills that help agents build Laravel applications while following best practices.

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## Deploying to Google Cloud Run

This project can be deployed to Google Cloud Run using the included `Dockerfile` and `cloudbuild.yaml`.

Steps:

1. Install and authenticate the Google Cloud SDK.
2. Create a new Google Cloud project and enable Cloud Run and Cloud Build.
3. Build and push the container image:

```bash
gcloud builds submit --config cloudbuild.yaml --substitutions=_IMAGE_NAME=laravel-app
```

4. Deploy to Cloud Run:

```bash
gcloud run deploy laravel-app \
  --image gcr.io/$GOOGLE_CLOUD_PROJECT/laravel-app:latest \
  --platform managed \
  --region us-central1 \
  --allow-unauthenticated \
  --set-env-vars "APP_URL=https://YOUR_CLOUD_RUN_URL,APP_ENV=production,APP_DEBUG=false"
```

5. Set database and other secret environment variables in the Cloud Run service settings:
   - `DB_CONNECTION=mysql`
   - `DB_HOST` / `DB_PORT`
   - `DB_DATABASE`
   - `DB_USERNAME`
   - `DB_PASSWORD`
   - `APP_KEY` (optional if not generated automatically)
   - `TECHNICIAN_ACCESS_CODE`

6. Run migrations after deployment:

```bash
gcloud run services proxy --region us-central1
# in another terminal
php artisan migrate --force
```

You should also configure a Cloud SQL instance for MySQL and supply the connection details as environment variables.

## Running locally with Docker Compose

If you want to run this application on another machine, use the included `docker-compose.yml`.

1. Copy the example environment file if you need a local `.env`:
   ```bash
   cp .env.example .env
   ```

2. Build and start the app:
   ```bash
   docker compose up --build
   ```

3. Open the app in your browser:
   ```text
   http://localhost:8080
   ```

4. To stop the app:
   ```bash
   docker compose down
   ```

## Autorun

Double-click `autorun.bat` on Windows or run `./autorun.sh` on Linux/macOS.

It will:

- copy `.env.example` to `.env` if needed
- start the app with Docker Compose if Docker is installed
- otherwise fall back to PHP built-in server if `php` is available
- run database migrations automatically

1. Copy the example environment file if you need a local `.env`:
   ```bash
   cp .env.example .env
   ```

2. Build and start the app:
   ```bash
   docker compose up --build
   ```

3. Open the app in your browser:
   ```text
   http://localhost:8080
   ```

4. To stop the app:
   ```bash
   docker compose down
   ```

## Deploying to Fly.io with GitHub

This project is ready to deploy to Fly.io using Docker and GitHub Actions.

1. Create a Fly.io account and install the CLI locally:

```bash
flyctl auth login
```

2. Create your app and choose a region. Example:

```bash
flyctl apps create clickfix-app --region sin
```

3. Create the persistent volume for SQLite data:

```bash
flyctl volumes create laravel_sqlite --region sin --size 1
```

4. Add GitHub repository secrets:

- `FLY_API_TOKEN`
- `FLY_APP_NAME`

5. Push to the `main` branch to trigger deployment automatically. The workflow is in `.github/workflows/deploy-fly.yml`.

6. If you want to deploy manually:

```bash
flyctl deploy
```

The app is configured to use SQLite in production and stores its database files under `/var/www/html/database`, which is mounted to a Fly volume.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

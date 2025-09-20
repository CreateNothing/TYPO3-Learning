# Repository Guidelines

## Project Structure & Module Organization
Symfony code lives in `src/` (controllers, entities, services) with configuration in `config/`. Doctrine migrations belong in `migrations/`; script every schema change. Twig templates and UI fragments stay in `templates/`, and shared frontend assets sit under `assets/` before the asset mapper publishes them to `public/`. Automated tests mirror production namespaces inside `tests/`.

## Build, Test, and Development Commands
Run everything through DDEV. `ddev start` boots PHP, Postgres, and Ollama; `ddev stop` tears the stack down. Install PHP dependencies with `ddev composer install`. Apply database updates via `ddev console doctrine:migrations:migrate -n`. Execute the backend test suite using `ddev exec bin/phpunit` after meaningful changes.

## Coding Style & Naming Conventions
Follow PSR-12 with four-space indentation and typed properties. PHP classes use PascalCase, methods and properties camelCase, and Doctrine columns snake_case. Twig blocks and template files are PascalCase or kebab-case depending on context; keep inline styles minimal and prefer shared rules in `assets/styles/` when possible. Services should be marked `final` unless extensibility is required.

## Testing Guidelines
Add PHPUnit coverage beside the feature namespace, e.g. `tests/Controller/RegistrationControllerTest.php`. Favor integration tests for Doctrine queries and controllers using the DDEV test database seeded by migrations. Include edge-case fixtures under `tests/Fixtures/` when tinkering with ingestion, chunking, or verification flows. Always run `ddev exec bin/phpunit` before pushing.

## Commit & Pull Request Guidelines
Write concise, imperative commit subjects (e.g., "Add registration success redirect") and group related changes. Flag migrations explicitly in commit or PR bodies. Pull requests should summarize intent, list high-impact updates, and document validation steps (`ddev exec bin/phpunit`). Provide screenshots or response samples for noteworthy UI or API changes and link Jira/GitHub issues when available.

## Security & Configuration Tips
Keep secrets in `.env.local` or Symfony secrets; never commit them. Production deployments require `APP_ENV=prod` and `APP_DEBUG=0`. Document any new environment variables in `README` or `Todo.md`, and extend `.ddev/docker-compose.*.yaml` when introducing services so teammates inherit the same stack.

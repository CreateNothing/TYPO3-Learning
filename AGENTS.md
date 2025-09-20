# Repository Guidelines

## Project Structure & Module Organization
- `src/` holds Symfony PHP code. Domain logic lives under `Entity/`, `Repository/`, service helpers under `Service/`, and HTTP entry points under `Controller/`.
- `templates/` contains Twig views for any UI we add later; `assets/` includes JS/CSS managed by the Symfony asset mapper.
- `migrations/` stores Doctrine migrations, including the pgvector setup for `doc_chunk`.
- `tests/` hosts PHPUnit suites; add mirrors of `src/` namespaces here. Configuration and routing live in `config/`.

## Environment & Services
- Use DDEV (`.ddev/`) for local orchestration. The `ollama` service supplies embeddings and shares the model cache via the named volume.
- Environment defaults are in `.env`; DDEV overrides (`.ddev/config.yaml`) keep dev mode active while production defaults stay secure.

## Build, Test, and Development Commands
- `ddev start` – boot the full stack (web, Postgres, Ollama).
- `ddev composer install` – install/update PHP dependencies.
- `ddev console doctrine:migrations:migrate` – apply schema updates.
- `ddev console app:embedding:update --limit=50` – backfill missing embeddings via Ollama.
- `ddev exec bin/phpunit` – run automated tests.

## Coding Style & Naming Conventions
- Follow PSR-12 (4-space indentation, typed properties, `strict_types=1` when practical). Keep Symfony service classes `final` unless extension is required.
- Use PascalCase for PHP classes, camelCase for methods/properties, snake_case for database columns matching migrations.
- Twig templates should remain kebab-case (e.g., `security/login.html.twig`).

## Testing Guidelines
- Write PHPUnit tests under `tests/` with class names ending in `Test`. Mirror the `src/` namespace to keep autoloading simple.
- Prefer integration tests for Doctrine queries (use the test DB) and unit tests for services like `OllamaEmbeddingClient`.
- Ensure migrations run in a clean state before asserting DB changes (`ddev exec bin/phpunit --testsuite=integration`).

## Commit & Pull Request Guidelines
- Commit messages follow the existing imperative style (e.g., “Add DocChunk vector schema and Ollama client”).
- Each PR should describe intent, list key changes, and reference issues when applicable. Include testing notes (`ddev exec bin/phpunit`) and screenshots for API/UI updates when relevant.

## Security & Configuration Tips
- Never commit `.env.local` or per-user secrets. Use Symfony secrets for production credentials.
- When adding new services, document any required env vars and extend `.ddev/config.yaml` so teammates inherit the same wiring.

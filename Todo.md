# TODO

1. Replace the TODO redirect inside `App\Security\AppAuthenticator::onAuthenticationSuccess()` with the route users should land on.
2. Extend the `App\\Entity\\User` entity with profile fields/roles and rerun `ddev console make:migration` before migrating again.
3. Add domain-specific PHPUnit integration tests (ingestion, search vector queries, authentication flows).
4. Evaluate Pest adoption and wire a CI job that provisions pgvector (CREATE EXTENSION vector) during test setup.

Note: We need first some more installations.

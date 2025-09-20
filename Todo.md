# TODO

1. Extend the `App\\Entity\\User` entity with profile fields/roles and rerun `ddev console make:migration` before migrating again.
2. Add domain-specific PHPUnit integration tests (ingestion, search vector queries, authentication flows).
3. Evaluate Pest adoption and wire a CI job that provisions pgvector (CREATE EXTENSION vector) during test setup.

Note: We need first some more installations.

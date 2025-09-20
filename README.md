# Learn TYPO3 Documentation Pipeline

## Prerequisites

- DDEV with Ollama and PostgreSQL running (`ddev start`).
- The `render-guides` executable from phpDocumentor installed inside the web container. Install it via Composer: `ddev exec composer global require phpdocumentor/guides-cli`.

## Environment Variables

- `GUIDES_BINARY` (default: `render-guides`): Path to the `render-guides` CLI. Override this if the binary lives at a custom filesystem location.

## Syncing Documentation

Run the documentation import and embedding pipeline:

```bash
ddev exec bin/console app:docs:sync
```

Use `--skip-embeddings` if you only need to refresh chunk metadata, or `--source` to target a single configured repository.

<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250920155819 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // base doc_chunk table from Doctrine's schema diff
        $this->addSql('CREATE TABLE doc_chunk (id SERIAL NOT NULL, source_repo VARCHAR(255) NOT NULL, doc_path VARCHAR(255) NOT NULL, version VARCHAR(255) NOT NULL, lang VARCHAR(16) NOT NULL, title VARCHAR(255) NOT NULL, anchor VARCHAR(255) DEFAULT NULL, content_md TEXT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql("COMMENT ON COLUMN doc_chunk.created_at IS '(DC2Type:datetime_immutable)'");

        // ensure pgvector extension exists before adding the embedding column
        $this->addSql('CREATE EXTENSION IF NOT EXISTS vector');

        // add embedding column with 768 dimensions to store Gemma embeddings
        $this->addSql('ALTER TABLE doc_chunk ADD embedding vector(768)');

        // create an HNSW index to accelerate similarity search on embeddings
        $this->addSql('CREATE INDEX doc_chunk_embedding_idx ON doc_chunk USING hnsw (embedding vector_cosine_ops)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE doc_chunk');
    }
}

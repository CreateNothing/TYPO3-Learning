<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250920190000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Introduce learning platform domain tables and evolve users/doc_chunks schema.';
    }

    public function up(Schema $schema): void
    {
        // users table evolution
        $this->addSql('ALTER TABLE "user" RENAME TO users');
        $this->addSql('ALTER INDEX uniq_identifier_email RENAME TO uniq_users_email');
        $this->addSql('ALTER TABLE users ADD handle VARCHAR(64) DEFAULT NULL');
        $this->addSql('ALTER TABLE users ADD avatar_url VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE users ADD created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NOW() NOT NULL');
        $this->addSql('ALTER TABLE users ADD updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql("UPDATE users SET handle = lower(regexp_replace(split_part(email, '@', 1), '[^a-z0-9_]+', '-', 'g')) WHERE handle IS NULL OR handle = ''");
        $this->addSql('ALTER TABLE users ALTER COLUMN handle SET NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX uniq_users_handle ON users (handle)');
        $this->addSql("COMMENT ON COLUMN users.created_at IS '(DC2Type:datetime_immutable)'");
        $this->addSql("COMMENT ON COLUMN users.updated_at IS '(DC2Type:datetime_immutable)'");
        $this->addSql('ALTER TABLE users ALTER COLUMN created_at DROP DEFAULT');

        // rename doc_chunk table and extend metadata
        $this->addSql('ALTER TABLE doc_chunk RENAME TO doc_chunks');
        $this->addSql('ALTER INDEX doc_chunk_embedding_idx RENAME TO doc_chunks_embedding_idx');
        $this->addSql("COMMENT ON COLUMN doc_chunks.created_at IS '(DC2Type:datetime_immutable)'");
        $this->addSql('ALTER TABLE doc_chunks ADD license VARCHAR(64) DEFAULT NULL');
        $this->addSql('ALTER TABLE doc_chunks ADD embedding_ref VARCHAR(64) DEFAULT NULL');
        $this->addSql('ALTER TABLE doc_chunks ADD payload JSON DEFAULT NULL');
        $this->addSql('CREATE INDEX doc_chunks_repo_path_idx ON doc_chunks (source_repo, doc_path)');

        // syllabus items
        $this->addSql('CREATE TABLE syllabus_items (id SERIAL NOT NULL, parent_id INT DEFAULT NULL, slug VARCHAR(128) NOT NULL, title VARCHAR(255) NOT NULL, description TEXT DEFAULT NULL, sort_order INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX uniq_syllabus_slug ON syllabus_items (slug)');
        $this->addSql('CREATE INDEX idx_syllabus_parent ON syllabus_items (parent_id)');
        $this->addSql("COMMENT ON COLUMN syllabus_items.created_at IS '(DC2Type:datetime_immutable)'");
        $this->addSql("COMMENT ON COLUMN syllabus_items.updated_at IS '(DC2Type:datetime_immutable)'");

        // questions
        $this->addSql('CREATE TABLE questions (id SERIAL NOT NULL, syllabus_item_id INT NOT NULL, type VARCHAR(16) NOT NULL, difficulty VARCHAR(32) NOT NULL, prompt TEXT NOT NULL, choices JSON DEFAULT NULL, correct JSON DEFAULT NULL, solution TEXT DEFAULT NULL, source_chunk_ids JSON NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, created_by VARCHAR(16) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx_questions_syllabus ON questions (syllabus_item_id)');
        $this->addSql('CREATE INDEX idx_questions_type ON questions (type)');
        $this->addSql("COMMENT ON COLUMN questions.created_at IS '(DC2Type:datetime_immutable)'");

        // sessions
        $this->addSql('CREATE TABLE sessions (id SERIAL NOT NULL, user_id INT NOT NULL, mode VARCHAR(16) NOT NULL, started_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, ended_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, score INT NOT NULL, streak_max INT NOT NULL, streak_current INT NOT NULL, total_time_ms INT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx_sessions_user_mode ON sessions (user_id, mode)');
        $this->addSql("COMMENT ON COLUMN sessions.started_at IS '(DC2Type:datetime_immutable)'");
        $this->addSql("COMMENT ON COLUMN sessions.ended_at IS '(DC2Type:datetime_immutable)'");

        // answers
        $this->addSql('CREATE TABLE answers (id SERIAL NOT NULL, session_id INT NOT NULL, question_id INT NOT NULL, user_answer JSON DEFAULT NULL, is_correct BOOLEAN NOT NULL, time_ms INT DEFAULT NULL, awarded_points INT DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx_answers_session ON answers (session_id)');
        $this->addSql('CREATE INDEX idx_answers_question ON answers (question_id)');
        $this->addSql("COMMENT ON COLUMN answers.created_at IS '(DC2Type:datetime_immutable)'");

        // duels
        $this->addSql('CREATE TABLE duels (id SERIAL NOT NULL, room_code VARCHAR(12) NOT NULL, status VARCHAR(16) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, started_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, ended_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, duration_s INT NOT NULL, reveal_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX uniq_duel_room_code ON duels (room_code)');
        $this->addSql("COMMENT ON COLUMN duels.created_at IS '(DC2Type:datetime_immutable)'");
        $this->addSql("COMMENT ON COLUMN duels.started_at IS '(DC2Type:datetime_immutable)'");
        $this->addSql("COMMENT ON COLUMN duels.ended_at IS '(DC2Type:datetime_immutable)'");
        $this->addSql("COMMENT ON COLUMN duels.reveal_at IS '(DC2Type:datetime_immutable)'");

        // duel participants
        $this->addSql('CREATE TABLE duel_participants (id SERIAL NOT NULL, duel_id INT NOT NULL, user_id INT NOT NULL, final_score INT DEFAULT NULL, rank_after_reveal SMALLINT DEFAULT NULL, bonus_points INT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX uniq_duel_participant ON duel_participants (duel_id, user_id)');
        $this->addSql('CREATE INDEX idx_duel_participant_duel ON duel_participants (duel_id)');

        // imports
        $this->addSql('CREATE TABLE imports (id SERIAL NOT NULL, json_schema_version VARCHAR(32) NOT NULL, file_name VARCHAR(255) NOT NULL, processed_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, stats_json JSON DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql("COMMENT ON COLUMN imports.processed_at IS '(DC2Type:datetime_immutable)'");
        $this->addSql("COMMENT ON COLUMN imports.created_at IS '(DC2Type:datetime_immutable)'");

        // foreign keys
        $this->addSql('ALTER TABLE syllabus_items ADD CONSTRAINT FK_2A676DA4727ACA70 FOREIGN KEY (parent_id) REFERENCES syllabus_items (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE questions ADD CONSTRAINT FK_8ADC54D093CB796C FOREIGN KEY (syllabus_item_id) REFERENCES syllabus_items (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE sessions ADD CONSTRAINT FK_7B50AEB9A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE answers ADD CONSTRAINT FK_50D0C606613FECDF FOREIGN KEY (session_id) REFERENCES sessions (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE answers ADD CONSTRAINT FK_50D0C6061E27F6BF FOREIGN KEY (question_id) REFERENCES questions (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE duel_participants ADD CONSTRAINT FK_4FF7A19F77B1C905 FOREIGN KEY (duel_id) REFERENCES duels (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE duel_participants ADD CONSTRAINT FK_4FF7A19FA76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // drop FKs before tables
        $this->addSql('ALTER TABLE duel_participants DROP CONSTRAINT FK_4FF7A19F77B1C905');
        $this->addSql('ALTER TABLE duel_participants DROP CONSTRAINT FK_4FF7A19FA76ED395');
        $this->addSql('ALTER TABLE answers DROP CONSTRAINT FK_50D0C606613FECDF');
        $this->addSql('ALTER TABLE answers DROP CONSTRAINT FK_50D0C6061E27F6BF');
        $this->addSql('ALTER TABLE sessions DROP CONSTRAINT FK_7B50AEB9A76ED395');
        $this->addSql('ALTER TABLE questions DROP CONSTRAINT FK_8ADC54D093CB796C');
        $this->addSql('ALTER TABLE syllabus_items DROP CONSTRAINT FK_2A676DA4727ACA70');

        // drop tables introduced in up()
        $this->addSql('DROP TABLE duel_participants');
        $this->addSql('DROP TABLE duels');
        $this->addSql('DROP TABLE answers');
        $this->addSql('DROP TABLE sessions');
        $this->addSql('DROP TABLE questions');
        $this->addSql('DROP TABLE imports');
        $this->addSql('DROP TABLE syllabus_items');

        // revert doc_chunks changes
        $this->addSql('DROP INDEX doc_chunks_repo_path_idx');
        $this->addSql('ALTER TABLE doc_chunks DROP license');
        $this->addSql('ALTER TABLE doc_chunks DROP embedding_ref');
        $this->addSql('ALTER TABLE doc_chunks DROP payload');
        $this->addSql('ALTER INDEX doc_chunks_embedding_idx RENAME TO doc_chunk_embedding_idx');
        $this->addSql('ALTER TABLE doc_chunks RENAME TO doc_chunk');

        // revert users alterations
        $this->addSql('DROP INDEX uniq_users_handle');
        $this->addSql('ALTER TABLE users DROP handle');
        $this->addSql('ALTER TABLE users DROP avatar_url');
        $this->addSql('ALTER TABLE users DROP created_at');
        $this->addSql('ALTER TABLE users DROP updated_at');
        $this->addSql('ALTER TABLE users RENAME TO "user"');
        $this->addSql('ALTER INDEX uniq_users_email RENAME TO uniq_identifier_email');
    }
}

<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260228143014 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create vault_entries table for Vault module';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE vault_entries (owner_id VARCHAR(36) NOT NULL, title VARCHAR(255) NOT NULL, encrypted_password LONGTEXT NOT NULL, login VARCHAR(255) DEFAULT NULL, url VARCHAR(2048) DEFAULT NULL, notes LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, id VARCHAR(36) NOT NULL, INDEX IDX_vault_owner (owner_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE vault_entries');
    }
}

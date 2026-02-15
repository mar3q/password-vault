<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260214171020 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create users table for Identity module';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
                CREATE TABLE users (
                    id VARCHAR(36) NOT NULL,
                    email VARCHAR(255) NOT NULL,
                    username VARCHAR(64) NOT NULL,
                    password VARCHAR(255) NOT NULL,
                    UNIQUE INDEX UNIQ_1483A5E9E7927C74 (email),
                    PRIMARY KEY (id)
                ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB
            SQL);
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1483A5E9F85E0677 ON users (username)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE users');
        $this->addSql('DROP INDEX UNIQ_1483A5E9F85E0677 ON users');
    }
}

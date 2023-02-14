<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230214152003 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Seed the global settings table with default values.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('INSERT INTO global_settings (max_robots, timestamp) VALUES (5, NOW())');

    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}

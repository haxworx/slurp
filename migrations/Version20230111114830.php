<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230111114830 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE robot_data (id INT AUTO_INCREMENT NOT NULL, bot_id INT NOT NULL, launch_id INT NOT NULL, time_stamp DATETIME NOT NULL, link_source LONGTEXT DEFAULT NULL, modified DATETIME NOT NULL, status_code INT NOT NULL, content_type VARCHAR(255) NOT NULL, headers LONGTEXT NOT NULL, url LONGTEXT NOT NULL, path LONGTEXT NOT NULL, checksum VARCHAR(255) NOT NULL, encoding VARCHAR(255) NOT NULL, length INT NOT NULL, data LONGBLOB NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE robot_data');
    }
}

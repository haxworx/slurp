<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221228150826 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE global_settings');
        $this->addSql('DROP TABLE crawl_errors');
        $this->addSql('DROP TABLE content_types');
        $this->addSql('DROP TABLE crawl_settings');
        $this->addSql('DROP TABLE crawl_data');
        $this->addSql('DROP TABLE crawl_launch');
        $this->addSql('DROP TABLE crawl_allowed_content');
        $this->addSql('DROP TABLE crawl_log');
        $this->addSql('ALTER TABLE user ADD api_key VARCHAR(255) NOT NULL, DROP api_token');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE global_settings (id INT AUTO_INCREMENT NOT NULL, time_stamp DATETIME DEFAULT NULL, in_use TINYINT(1) DEFAULT NULL, max_crawlers INT DEFAULT NULL, debug TINYINT(1) DEFAULT NULL, mqtt_host VARCHAR(128) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, mqtt_port INT DEFAULT NULL, mqtt_topic VARCHAR(8192) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE crawl_errors (id INT AUTO_INCREMENT NOT NULL, bot_id INT DEFAULT NULL, srv_time_stamp DATETIME DEFAULT CURRENT_TIMESTAMP, scan_date DATE DEFAULT NULL, scan_time_stamp DATETIME DEFAULT NULL, scan_time_zone VARCHAR(64) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, status_code INT DEFAULT NULL, url VARCHAR(4096) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, link_source VARCHAR(4096) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, description TEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, launch_id INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE content_types (content_id INT AUTO_INCREMENT NOT NULL, content_type VARCHAR(128) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, description TEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, PRIMARY KEY(content_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE crawl_settings (bot_id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, scheme VARCHAR(32) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, address VARCHAR(260) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, domain VARCHAR(253) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, agent VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, delay DOUBLE PRECISION DEFAULT NULL, ignore_query TINYINT(1) DEFAULT NULL, import_sitemaps TINYINT(1) DEFAULT NULL, retry_max INT DEFAULT NULL, start_time TIME DEFAULT NULL, end_time DATETIME DEFAULT NULL, is_running TINYINT(1) DEFAULT NULL, has_error TINYINT(1) DEFAULT NULL, container_id VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, PRIMARY KEY(bot_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE crawl_data (id INT AUTO_INCREMENT NOT NULL, bot_id INT DEFAULT NULL, srv_time_stamp DATETIME DEFAULT CURRENT_TIMESTAMP, scan_date DATE DEFAULT NULL, scan_time_stamp DATETIME DEFAULT NULL, scan_time_zone VARCHAR(64) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, domain VARCHAR(253) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, scheme VARCHAR(32) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, link_source VARCHAR(4096) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, modified DATETIME DEFAULT NULL, url VARCHAR(4096) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, status_code INT DEFAULT NULL, path TEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, query TEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, content_type VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, metadata TEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, checksum VARCHAR(32) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, encoding VARCHAR(32) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, length INT DEFAULT NULL, data MEDIUMBLOB DEFAULT NULL, launch_id INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE crawl_launch (id INT AUTO_INCREMENT NOT NULL, bot_id INT NOT NULL, start_time DATETIME DEFAULT NULL, end_time DATETIME DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE crawl_allowed_content (id INT AUTO_INCREMENT NOT NULL, bot_id INT DEFAULT NULL, content_id INT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE crawl_log (id INT AUTO_INCREMENT NOT NULL, bot_id INT DEFAULT NULL, srv_time_stamp DATETIME DEFAULT CURRENT_TIMESTAMP, scan_date DATE DEFAULT NULL, scan_time_stamp DATETIME DEFAULT NULL, crawler_name VARCHAR(32) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, hostname VARCHAR(128) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, ip_address VARCHAR(128) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, level_number INT DEFAULT NULL, level_name VARCHAR(32) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, message TEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, launch_id INT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE user ADD api_token VARCHAR(255) DEFAULT NULL, DROP api_key');
    }
}

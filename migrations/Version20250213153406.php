<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250213153406 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE deleted_accounts (id INT AUTO_INCREMENT NOT NULL, date_delete LONGTEXT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE checked_accounts DROP FOREIGN KEY FK_688C358484B489B6');
        $this->addSql('ALTER TABLE checked_accounts ADD CONSTRAINT FK_688C358484B489B6 FOREIGN KEY (checked_user_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE deleted_accounts');
        $this->addSql('ALTER TABLE checked_accounts DROP FOREIGN KEY FK_688C358484B489B6');
        $this->addSql('ALTER TABLE checked_accounts ADD CONSTRAINT FK_688C358484B489B6 FOREIGN KEY (checked_user_id) REFERENCES user (id) ON DELETE CASCADE');
    }
}

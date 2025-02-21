<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250211143208 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE checked_accounts (id INT AUTO_INCREMENT NOT NULL, admin_id INT DEFAULT NULL, checked_user_id INT DEFAULT NULL, INDEX IDX_688C3584642B8210 (admin_id), INDEX IDX_688C358484B489B6 (checked_user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE checked_accounts ADD CONSTRAINT FK_688C3584642B8210 FOREIGN KEY (admin_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE checked_accounts ADD CONSTRAINT FK_688C358484B489B6 FOREIGN KEY (checked_user_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE checked_accounts DROP FOREIGN KEY FK_688C3584642B8210');
        $this->addSql('ALTER TABLE checked_accounts DROP FOREIGN KEY FK_688C358484B489B6');
        $this->addSql('DROP TABLE checked_accounts');
    }
}

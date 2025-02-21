<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250213160056 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE checked_accounts DROP FOREIGN KEY FK_688C358484B489B6');
        $this->addSql('ALTER TABLE checked_accounts ADD CONSTRAINT FK_688C358484B489B6 FOREIGN KEY (checked_user_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE checked_accounts DROP FOREIGN KEY FK_688C358484B489B6');
        $this->addSql('ALTER TABLE checked_accounts ADD CONSTRAINT FK_688C358484B489B6 FOREIGN KEY (checked_user_id) REFERENCES user (id) ON DELETE CASCADE');
    }
}

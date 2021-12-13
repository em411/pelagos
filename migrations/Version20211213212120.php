<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211213212120 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SEQUENCE information_product_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE information_product (id INT NOT NULL, title TEXT NOT NULL, creators TEXT NOT NULL, publisher TEXT NOT NULL, external_doi TEXT DEFAULT NULL, published BOOLEAN NOT NULL, remote_resource BOOLEAN NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE information_product_research_group (information_product_id INT NOT NULL, research_group_id INT NOT NULL, PRIMARY KEY(information_product_id, research_group_id))');
        $this->addSql('CREATE INDEX IDX_D7EA393468C0853 ON information_product_research_group (information_product_id)');
        $this->addSql('CREATE INDEX IDX_D7EA3933AF8E8D ON information_product_research_group (research_group_id)');
        $this->addSql('ALTER TABLE information_product_research_group ADD CONSTRAINT FK_D7EA393468C0853 FOREIGN KEY (information_product_id) REFERENCES information_product (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE information_product_research_group ADD CONSTRAINT FK_D7EA3933AF8E8D FOREIGN KEY (research_group_id) REFERENCES research_group (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE information_product_research_group DROP CONSTRAINT FK_D7EA393468C0853');
        $this->addSql('DROP SEQUENCE information_product_id_seq CASCADE');
        $this->addSql('DROP TABLE information_product');
        $this->addSql('DROP TABLE information_product_research_group');
    }
}

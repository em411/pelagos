<?php

namespace Pelagos\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20171106191948 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE dataset_submission ADD hard_drive_delivery_info JSON DEFAULT NULL');
        $this->addSql('ALTER TABLE dataset_submission_audit ADD hard_drive_delivery_info JSON DEFAULT NULL');
        $this->addSql('COMMENT ON COLUMN metadata.geometry IS \'(DC2Type:geometry)\'');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE dataset_submission_audit DROP hard_drive_delivery_info');
        $this->addSql('COMMENT ON COLUMN metadata.geometry IS \'(DC2Type:geometry)(DC2Type:geometry)\'');
        $this->addSql('ALTER TABLE dataset_submission DROP hard_drive_delivery_info');
    }
}

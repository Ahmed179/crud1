<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210702153630 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE employee_role');
        $this->addSql('ALTER TABLE employee ADD role_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE employee ALTER phone DROP NOT NULL');
        $this->addSql('ALTER TABLE employee ALTER email SET NOT NULL');
        $this->addSql('ALTER TABLE employee ADD CONSTRAINT FK_5D9F75A1D60322AC FOREIGN KEY (role_id) REFERENCES role (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_5D9F75A1D60322AC ON employee (role_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('CREATE TABLE employee_role (employee_id INT NOT NULL, role_id INT NOT NULL, PRIMARY KEY(employee_id, role_id))');
        $this->addSql('CREATE INDEX idx_e2b0c02dd60322ac ON employee_role (role_id)');
        $this->addSql('CREATE INDEX idx_e2b0c02d8c03f15c ON employee_role (employee_id)');
        $this->addSql('ALTER TABLE employee_role ADD CONSTRAINT fk_e2b0c02d8c03f15c FOREIGN KEY (employee_id) REFERENCES employee (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE employee_role ADD CONSTRAINT fk_e2b0c02dd60322ac FOREIGN KEY (role_id) REFERENCES role (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE employee DROP CONSTRAINT FK_5D9F75A1D60322AC');
        $this->addSql('DROP INDEX IDX_5D9F75A1D60322AC');
        $this->addSql('ALTER TABLE employee DROP role_id');
        $this->addSql('ALTER TABLE employee ALTER email DROP NOT NULL');
        $this->addSql('ALTER TABLE employee ALTER phone SET NOT NULL');
    }
}

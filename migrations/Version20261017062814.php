<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261017062814 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE empleado (id INT AUTO_INCREMENT NOT NULL, nombres VARCHAR(100) NOT NULL, apellidos VARCHAR(100) NOT NULL, fecha_nacimiento DATE NOT NULL, fotografia VARCHAR(255) DEFAULT NULL, salario NUMERIC(12, 2) NOT NULL, puesto_id INT NOT NULL, tienda_id INT NOT NULL, INDEX IDX_D9D9BF525035E9DA (puesto_id), INDEX IDX_D9D9BF5219BA6D46 (tienda_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE puesto (id INT AUTO_INCREMENT NOT NULL, nombre VARCHAR(100) NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE registro_empleado (id INT AUTO_INCREMENT NOT NULL, descripcion LONGTEXT NOT NULL, tipo VARCHAR(10) NOT NULL, fecha DATE NOT NULL, empleado_id INT NOT NULL, INDEX IDX_760B359A952BE730 (empleado_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE tienda (id INT AUTO_INCREMENT NOT NULL, nombre VARCHAR(100) NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE empleado ADD CONSTRAINT FK_D9D9BF525035E9DA FOREIGN KEY (puesto_id) REFERENCES puesto (id)');
        $this->addSql('ALTER TABLE empleado ADD CONSTRAINT FK_D9D9BF5219BA6D46 FOREIGN KEY (tienda_id) REFERENCES tienda (id)');
        $this->addSql('ALTER TABLE registro_empleado ADD CONSTRAINT FK_760B359A952BE730 FOREIGN KEY (empleado_id) REFERENCES empleado (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE empleado DROP FOREIGN KEY FK_D9D9BF525035E9DA');
        $this->addSql('ALTER TABLE empleado DROP FOREIGN KEY FK_D9D9BF5219BA6D46');
        $this->addSql('ALTER TABLE registro_empleado DROP FOREIGN KEY FK_760B359A952BE730');
        $this->addSql('DROP TABLE empleado');
        $this->addSql('DROP TABLE puesto');
        $this->addSql('DROP TABLE registro_empleado');
        $this->addSql('DROP TABLE tienda');
    }
}

<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201213115309 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE forum_thread DROP FOREIGN KEY FK_298F7F522D053F64');
        $this->addSql('DROP INDEX UNIQ_298F7F522D053F64 ON forum_thread');
        $this->addSql('ALTER TABLE forum_thread ADD last_post_created_at DATETIME NOT NULL, ADD last_post_author VARCHAR(255) NOT NULL, DROP last_post_id');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE forum_thread ADD last_post_id INT DEFAULT NULL, DROP last_post_created_at, DROP last_post_author');
        $this->addSql('ALTER TABLE forum_thread ADD CONSTRAINT FK_298F7F522D053F64 FOREIGN KEY (last_post_id) REFERENCES forum_post (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_298F7F522D053F64 ON forum_thread (last_post_id)');
    }
}

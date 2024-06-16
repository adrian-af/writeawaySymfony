<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240616132131 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE reset_password_request (id INT AUTO_INCREMENT NOT NULL, user_id VARCHAR(255) NOT NULL, selector VARCHAR(20) NOT NULL, hashed_token VARCHAR(100) NOT NULL, requested_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', expires_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_7CE748AA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE reset_password_request ADD CONSTRAINT FK_7CE748AA76ED395 FOREIGN KEY (user_id) REFERENCES users (ID)');
        $this->addSql('DROP INDEX ID ON comments');
        $this->addSql('ALTER TABLE comments CHANGE userId userId VARCHAR(255) DEFAULT NULL, CHANGE storyId storyId INT DEFAULT NULL, CHANGE dateTime datetime DATETIME NOT NULL, CHANGE content content VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE comments RENAME INDEX userid TO IDX_5F9E962A64B64DCC');
        $this->addSql('ALTER TABLE comments RENAME INDEX storyid TO IDX_5F9E962A3A4FD046');
        $this->addSql('DROP INDEX ID ON genres');
        $this->addSql('ALTER TABLE genres CHANGE name name VARCHAR(255) NOT NULL');
        $this->addSql('DROP INDEX ID ON stories');
        $this->addSql('ALTER TABLE stories CHANGE userId userId VARCHAR(255) DEFAULT NULL, CHANGE title title VARCHAR(255) NOT NULL, CHANGE genreId genreId INT DEFAULT NULL, CHANGE text text VARCHAR(255) NOT NULL, CHANGE public public VARCHAR(255) NOT NULL, CHANGE datetime datetime DATETIME NOT NULL');
        $this->addSql('ALTER TABLE stories RENAME INDEX userid TO IDX_9C8B9D5F64B64DCC');
        $this->addSql('ALTER TABLE stories RENAME INDEX genreid TO IDX_9C8B9D5F4908CA01');
        $this->addSql('DROP INDEX ID ON users');
        $this->addSql('ALTER TABLE users CHANGE ID ID VARCHAR(255) NOT NULL, CHANGE email email VARCHAR(255) NOT NULL, CHANGE username username VARCHAR(255) NOT NULL, CHANGE photo photo LONGBLOB NOT NULL, CHANGE about about VARCHAR(255) NOT NULL, CHANGE role role INT NOT NULL, CHANGE forgor forgor VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE users_fav_stories MODIFY relacion_id INT NOT NULL');
        $this->addSql('ALTER TABLE users_fav_stories DROP FOREIGN KEY users_fav_stories_ibfk_1');
        $this->addSql('ALTER TABLE users_fav_stories DROP FOREIGN KEY users_fav_stories_ibfk_2');
        $this->addSql('DROP INDEX user_id ON users_fav_stories');
        $this->addSql('DROP INDEX relacion_id ON users_fav_stories');
        $this->addSql('DROP INDEX `primary` ON users_fav_stories');
        $this->addSql('ALTER TABLE users_fav_stories DROP relacion_id, CHANGE user_id user_id VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE users_fav_stories ADD CONSTRAINT FK_142EA73FA76ED395 FOREIGN KEY (user_id) REFERENCES users (ID)');
        $this->addSql('ALTER TABLE users_fav_stories ADD CONSTRAINT FK_142EA73FAA5D4036 FOREIGN KEY (story_id) REFERENCES stories (ID)');
        $this->addSql('ALTER TABLE users_fav_stories ADD PRIMARY KEY (user_id, story_id)');
        $this->addSql('ALTER TABLE users_fav_stories RENAME INDEX story_id TO IDX_142EA73FAA5D4036');
        $this->addSql('ALTER TABLE rel_story_user DROP INDEX idx_user_id, ADD INDEX IDX_F7CCBD18A76ED395 (user_id)');
        $this->addSql('ALTER TABLE rel_story_user MODIFY id INT NOT NULL');
        $this->addSql('DROP INDEX `primary` ON rel_story_user');
        $this->addSql('ALTER TABLE rel_story_user DROP id, CHANGE user_id user_id VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE rel_story_user ADD PRIMARY KEY (user_id, story_id)');
        $this->addSql('ALTER TABLE rel_story_user RENAME INDEX idx_story_id TO IDX_F7CCBD18AA5D4036');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE reset_password_request DROP FOREIGN KEY FK_7CE748AA76ED395');
        $this->addSql('DROP TABLE reset_password_request');
        $this->addSql('DROP TABLE messenger_messages');
        $this->addSql('ALTER TABLE comments CHANGE content content VARCHAR(1000) NOT NULL, CHANGE datetime dateTime DATETIME DEFAULT \'current_timestamp()\' NOT NULL, CHANGE userId userId INT NOT NULL, CHANGE storyId storyId INT NOT NULL');
        $this->addSql('CREATE INDEX ID ON comments (ID)');
        $this->addSql('ALTER TABLE comments RENAME INDEX idx_5f9e962a3a4fd046 TO storyId');
        $this->addSql('ALTER TABLE comments RENAME INDEX idx_5f9e962a64b64dcc TO userId');
        $this->addSql('ALTER TABLE genres CHANGE name name VARCHAR(40) NOT NULL');
        $this->addSql('CREATE INDEX ID ON genres (ID)');
        $this->addSql('ALTER TABLE rel_story_user DROP INDEX IDX_F7CCBD18A76ED395, ADD UNIQUE INDEX idx_user_id (user_id)');
        $this->addSql('ALTER TABLE rel_story_user ADD id INT AUTO_INCREMENT NOT NULL, CHANGE user_id user_id INT NOT NULL, DROP PRIMARY KEY, ADD PRIMARY KEY (id)');
        $this->addSql('ALTER TABLE rel_story_user RENAME INDEX idx_f7ccbd18aa5d4036 TO idx_story_id');
        $this->addSql('ALTER TABLE stories CHANGE title title VARCHAR(40) NOT NULL, CHANGE text text LONGTEXT NOT NULL, CHANGE public public TINYINT(1) DEFAULT 0 NOT NULL, CHANGE datetime datetime DATETIME DEFAULT \'current_timestamp(6)\' NOT NULL, CHANGE userId userId INT NOT NULL, CHANGE genreId genreId INT NOT NULL');
        $this->addSql('CREATE INDEX ID ON stories (ID)');
        $this->addSql('ALTER TABLE stories RENAME INDEX idx_9c8b9d5f64b64dcc TO userId');
        $this->addSql('ALTER TABLE stories RENAME INDEX idx_9c8b9d5f4908ca01 TO genreId');
        $this->addSql('ALTER TABLE users CHANGE ID ID INT AUTO_INCREMENT NOT NULL, CHANGE email email VARCHAR(50) NOT NULL, CHANGE username username VARCHAR(20) NOT NULL, CHANGE photo photo LONGBLOB DEFAULT NULL, CHANGE about about VARCHAR(400) DEFAULT \'NULL\', CHANGE role role INT DEFAULT 0 NOT NULL, CHANGE forgor forgor VARCHAR(10) DEFAULT \'\'\'0\'\'\' NOT NULL');
        $this->addSql('CREATE INDEX ID ON users (ID)');
        $this->addSql('ALTER TABLE users_fav_stories DROP FOREIGN KEY FK_142EA73FA76ED395');
        $this->addSql('ALTER TABLE users_fav_stories DROP FOREIGN KEY FK_142EA73FAA5D4036');
        $this->addSql('ALTER TABLE users_fav_stories ADD relacion_id INT AUTO_INCREMENT NOT NULL, CHANGE user_id user_id INT NOT NULL, DROP PRIMARY KEY, ADD PRIMARY KEY (relacion_id)');
        $this->addSql('ALTER TABLE users_fav_stories ADD CONSTRAINT users_fav_stories_ibfk_1 FOREIGN KEY (user_id) REFERENCES users (ID) ON UPDATE CASCADE ON DELETE CASCADE');
        $this->addSql('ALTER TABLE users_fav_stories ADD CONSTRAINT users_fav_stories_ibfk_2 FOREIGN KEY (story_id) REFERENCES stories (ID) ON UPDATE CASCADE ON DELETE CASCADE');
        $this->addSql('CREATE INDEX user_id ON users_fav_stories (user_id, story_id)');
        $this->addSql('CREATE UNIQUE INDEX relacion_id ON users_fav_stories (relacion_id)');
        $this->addSql('ALTER TABLE users_fav_stories RENAME INDEX idx_142ea73faa5d4036 TO story_id');
    }
}

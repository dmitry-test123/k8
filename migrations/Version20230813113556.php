<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230813193556 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'initial migration';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE SEQUENCE users_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql(<<<SQL
            CREATE TABLE users (
                id INT NOT NULL DEFAULT nextval('users_id_seq'),
                username VARCHAR(255) NOT NULL,
                email VARCHAR(255) NOT NULL,
                validts BIGINT NOT NULL,
                confirmed BOOLEAN NOT NULL,
                checked smallint NOT NULL,
                valid BOOLEAN NOT NULL,
                notification smallint NULL,
                PRIMARY KEY(id)
           )
SQL);

        // супермаленькие индексы для быстрых поисков без обращения в таблице
        $this->addSql('CREATE INDEX unconfirmed_valid_users_validts ON users USING btree (validts) WHERE validts > 0 AND checked=0 AND confirmed');
        $this->addSql('CREATE INDEX confirmed_valid_users_validts ON users USING btree (validts) INCLUDE (notification) WHERE validts > 0 AND checked=1 AND confirmed AND valid');

        // symfony fixtures - медленно, для тестового - пойдёт здесь
        $this->addSql(<<<SQL
            INSERT INTO users (username, email, validts, confirmed, checked, valid) 
            SELECT concat('u', g),
                   concat('u', g, '@localhost'), 
                   CASE g % 5 WHEN 0 THEN round(extract(epoch from now())) + round(random()*60*60*24*30) ELSE 0 END, -- 20% имеют подписку от 0 до 30 дней
                   random() <= 0.15, -- 15% пользователей подтверждают свой емейл
                   0, -- имейлы не проверены
                   false
            FROM generate_series(0, 5000001) g
SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP SEQUENCE users_id_seq CASCADE');
        $this->addSql('DROP TABLE users');
    }
}

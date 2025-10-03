<?php

use Tnt\Dbi\TableBuilder;
use Tnt\Dbi\Enums\TimestampFormat;

describe('TableBuilder Timestamps', function () {
    
    it('creates table with default timestamps (unix)', function () {
        $tableBuilder = new TableBuilder(false);
        $tableBuilder->table('users');
        $tableBuilder->addColumn('id', 'int')->primaryKey();
        $tableBuilder->addColumn('name', 'varchar')->length(255)->notNull();
        $tableBuilder->timestamps();
        $tableBuilder->build();
        
        $queries = $tableBuilder->getQueries();
        $allQueries = implode(' ', $queries);
        
        expect($queries[0])
            ->toContain('`created` INT UNSIGNED NOT NULL')
            ->toContain('`updated` INT UNSIGNED NOT NULL');
        
        expect($allQueries)
            ->toContain('DROP TRIGGER IF EXISTS `users_created_trigger`')
            ->toContain('DROP TRIGGER IF EXISTS `users_updated_trigger`')
            ->toContain('CREATE TRIGGER `users_created_trigger`')
            ->toContain('BEFORE INSERT ON `users`')
            ->toContain('SET NEW.`created` = UNIX_TIMESTAMP()')
            ->toContain('SET NEW.`updated` = UNIX_TIMESTAMP()')
            ->toContain('CREATE TRIGGER `users_updated_trigger`')
            ->toContain('BEFORE UPDATE ON `users`')
            ->toContain('SET NEW.`updated` = UNIX_TIMESTAMP()');
    });

    it('creates table with datetime timestamps using enum', function () {
        $tableBuilder = new TableBuilder(false);
        $tableBuilder->table('posts');
        $tableBuilder->addColumn('id', 'int')->primaryKey();
        $tableBuilder->addColumn('title', 'varchar')->length(255)->notNull();
        $tableBuilder->timestamps('created_on', 'modified_on', TimestampFormat::DATETIME);
        $tableBuilder->build();
        
        $allQueries = implode(' ', $tableBuilder->getQueries());
        
        expect($allQueries)
            ->toContain('`created_on` TIMESTAMP NOT NULL')
            ->toContain('`modified_on` TIMESTAMP NOT NULL')
            ->toContain('CREATE TRIGGER `posts_created_trigger`')
            ->toContain('SET NEW.`created_on` = CURRENT_TIMESTAMP')
            ->toContain('SET NEW.`modified_on` = CURRENT_TIMESTAMP')
            ->toContain('CREATE TRIGGER `posts_updated_trigger`')
            ->toContain('SET NEW.`modified_on` = CURRENT_TIMESTAMP');
    });

    it('adds timestamps to existing table with triggers', function () {
        $tableBuilder = new TableBuilder(true);
        $tableBuilder->table('existing_table');
        $tableBuilder->addColumn('phone', 'varchar')->length(20);
        $tableBuilder->timestamps();
        $tableBuilder->build();
        
        $allQueries = implode(' ', $tableBuilder->getQueries());
        
        expect($allQueries)
            ->toContain('ADD `created` INT UNSIGNED NOT NULL')
            ->toContain('ADD `updated` INT UNSIGNED NOT NULL')
            ->toContain('CREATE TRIGGER `existing_table_created_trigger`')
            ->toContain('BEFORE INSERT ON `existing_table`')
            ->toContain('SET NEW.`created` = UNIX_TIMESTAMP()')
            ->toContain('SET NEW.`updated` = UNIX_TIMESTAMP()')
            ->toContain('CREATE TRIGGER `existing_table_updated_trigger`')
            ->toContain('BEFORE UPDATE ON `existing_table`')
            ->toContain('SET NEW.`updated` = UNIX_TIMESTAMP()');
    });

    it('drops timestamp triggers and columns', function () {
        $tableBuilder = new TableBuilder(true);
        $tableBuilder->table('users');
        $tableBuilder->dropColumn('created');
        $tableBuilder->dropColumn('updated');
        $tableBuilder->dropTimestampTriggers();
        $tableBuilder->build();
        
        $allQueries = implode(' ', $tableBuilder->getQueries());
        
        expect($allQueries)
            ->toContain('DROP COLUMN `created`')
            ->toContain('DROP COLUMN `updated`')
            ->toContain('DROP TRIGGER IF EXISTS `users_created_trigger`')
            ->toContain('DROP TRIGGER IF EXISTS `users_updated_trigger`');
    });

    it('generates correct trigger names', function () {
        $tableBuilder = new TableBuilder(false);
        $tableBuilder->table('test_table');
        $tableBuilder->timestamps();
        $tableBuilder->build();
        
        $triggerNames = $tableBuilder->getGeneratedTriggerNames();
        
        expect($triggerNames)->toBe([
            'test_table_created_trigger',
            'test_table_updated_trigger'
        ]);
    });

    it('handles complex table with timestamps and constraints', function () {
        $tableBuilder = new TableBuilder(false);
        $tableBuilder->table('blog_posts');
        $tableBuilder->addColumn('id', 'int')->primaryKey();
        $tableBuilder->addColumn('title', 'varchar')->length(255)->notNull();
        $tableBuilder->addColumn('slug', 'varchar')->length(255)->notNull();
        $tableBuilder->addColumn('author_id', 'int')->notNull();
        $tableBuilder->timestamps();
        $tableBuilder->addUnique('slug');
        $tableBuilder->addForeignKey('author_id', 'users', 'id', 'CASCADE');
        $tableBuilder->build();
        
        $allQueries = implode(' ', $tableBuilder->getQueries());
        
        expect($allQueries)
            ->toContain('`created` INT UNSIGNED NOT NULL')
            ->toContain('`updated` INT UNSIGNED NOT NULL')
            ->toContain('CREATE TRIGGER `blog_posts_created_trigger`')
            ->toContain('CREATE TRIGGER `blog_posts_updated_trigger`')
            ->toContain('CONSTRAINT `uq_slug` UNIQUE (`slug`)')
            ->toContain('FOREIGN KEY (`author_id`) REFERENCES `users` (`id`) ON DELETE CASCADE');
    });

    it('handles mixed alter table operations with timestamps', function () {
        $tableBuilder = new TableBuilder(true);
        $tableBuilder->table('legacy_table');
        $tableBuilder->addColumn('new_field', 'varchar')->length(100);
        $tableBuilder->changeColumn('old_field')->type('text');
        $tableBuilder->dropColumn('obsolete_field');
        $tableBuilder->timestamps('date_created', 'date_updated');
        $tableBuilder->addForeignKey('category_id', 'categories', 'id');
        $tableBuilder->build();
        
        $allQueries = implode(' ', $tableBuilder->getQueries());
        
        expect($allQueries)
            ->toContain('ADD `new_field` VARCHAR(100) NOT NULL')
            ->toContain('CHANGE `old_field` `old_field` TEXT NOT NULL')
            ->toContain('DROP COLUMN `obsolete_field`')
            ->toContain('ADD `date_created` INT UNSIGNED NOT NULL')
            ->toContain('ADD `date_updated` INT UNSIGNED NOT NULL')
            ->toContain('CREATE TRIGGER `legacy_table_created_trigger`')
            ->toContain('SET NEW.`date_created` = UNIX_TIMESTAMP()')
            ->toContain('SET NEW.`date_updated` = UNIX_TIMESTAMP()')
            ->toContain('CREATE TRIGGER `legacy_table_updated_trigger`')
            ->toContain('SET NEW.`date_updated` = UNIX_TIMESTAMP()');
    });

    it('handles timestamp configuration updates', function () {
        // Step 1: Cleanup existing timestamps
        $cleanupBuilder = new TableBuilder(true);
        $cleanupBuilder->table('products');
        $cleanupBuilder->dropTimestampTriggers();
        $cleanupBuilder->dropColumn('created');
        $cleanupBuilder->dropColumn('updated');
        $cleanupBuilder->build();
        
        $cleanupQueries = implode(' ', $cleanupBuilder->getQueries());
        
        expect($cleanupQueries)
            ->toContain('DROP TRIGGER IF EXISTS `products_created_trigger`')
            ->toContain('DROP TRIGGER IF EXISTS `products_updated_trigger`')
            ->toContain('DROP COLUMN `created`')
            ->toContain('DROP COLUMN `updated`');
        
        // Step 2: Add new timestamp configuration
        $newBuilder = new TableBuilder(true);
        $newBuilder->table('products');
        $newBuilder->timestamps('creation_date', 'modification_date');
        $newBuilder->build();
        
        $newQueries = implode(' ', $newBuilder->getQueries());
        
        expect($newQueries)
            ->toContain('ADD `creation_date` INT UNSIGNED NOT NULL')
            ->toContain('ADD `modification_date` INT UNSIGNED NOT NULL')
            ->toContain('CREATE TRIGGER `products_created_trigger`')
            ->toContain('SET NEW.`creation_date` = UNIX_TIMESTAMP()')
            ->toContain('SET NEW.`modification_date` = UNIX_TIMESTAMP()')
            ->toContain('CREATE TRIGGER `products_updated_trigger`')
            ->toContain('SET NEW.`modification_date` = UNIX_TIMESTAMP()');
    });

    it('drops specific timestamp trigger by name', function () {
        $tableBuilder = new TableBuilder(true);
        $tableBuilder->table('users');
        $tableBuilder->dropTimestampTrigger('custom_trigger_name');
        $tableBuilder->build();
        
        $allQueries = implode(' ', $tableBuilder->getQueries());
        
        expect($allQueries)->toContain('DROP TRIGGER IF EXISTS `custom_trigger_name`');
    });

    it('creates table with unix timestamps using enum', function () {
        $tableBuilder = new TableBuilder(false);
        $tableBuilder->table('users');
        $tableBuilder->addColumn('id', 'int')->primaryKey();
        $tableBuilder->addColumn('name', 'varchar')->length(255)->notNull();
        $tableBuilder->timestamps('created', 'updated', TimestampFormat::UNIX);
        $tableBuilder->build();
        
        $allQueries = implode(' ', $tableBuilder->getQueries());
        
        expect($allQueries)
            ->toContain('`created` INT UNSIGNED NOT NULL')
            ->toContain('`updated` INT UNSIGNED NOT NULL')
            ->toContain('CREATE TRIGGER `users_created_trigger`')
            ->toContain('BEFORE INSERT ON `users`')
            ->toContain('SET NEW.`created` = UNIX_TIMESTAMP()')
            ->toContain('SET NEW.`updated` = UNIX_TIMESTAMP()')
            ->toContain('CREATE TRIGGER `users_updated_trigger`')
            ->toContain('BEFORE UPDATE ON `users`')
            ->toContain('SET NEW.`updated` = UNIX_TIMESTAMP()');
    });

    it('creates table with custom unix timestamp columns', function () {
        $tableBuilder = new TableBuilder(false);
        $tableBuilder->table('posts');
        $tableBuilder->addColumn('id', 'int')->primaryKey();
        $tableBuilder->addColumn('title', 'varchar')->length(255)->notNull();
        $tableBuilder->timestamps('created_at', 'updated_at', TimestampFormat::UNIX);
        $tableBuilder->build();
        
        $allQueries = implode(' ', $tableBuilder->getQueries());
        
        expect($allQueries)
            ->toContain('`created_at` INT UNSIGNED NOT NULL')
            ->toContain('`updated_at` INT UNSIGNED NOT NULL')
            ->toContain('SET NEW.`created_at` = UNIX_TIMESTAMP()')
            ->toContain('SET NEW.`updated_at` = UNIX_TIMESTAMP()');
    });

    it('adds unix timestamps to existing table with triggers', function () {
        $tableBuilder = new TableBuilder(true);
        $tableBuilder->table('existing_table');
        $tableBuilder->addColumn('phone', 'varchar')->length(20);
        $tableBuilder->timestamps('created', 'updated', TimestampFormat::UNIX);
        $tableBuilder->build();
        
        $allQueries = implode(' ', $tableBuilder->getQueries());
        
        expect($allQueries)
            ->toContain('ADD `created` INT UNSIGNED NOT NULL')
            ->toContain('ADD `updated` INT UNSIGNED NOT NULL')
            ->toContain('CREATE TRIGGER `existing_table_created_trigger`')
            ->toContain('BEFORE INSERT ON `existing_table`')
            ->toContain('BEFORE UPDATE ON `existing_table`');
    });

    it('generates correct trigger names for unix timestamps', function () {
        $tableBuilder = new TableBuilder(false);
        $tableBuilder->table('test_table');
        $tableBuilder->timestamps('created', 'updated', TimestampFormat::UNIX);
        $tableBuilder->build();
        
        $triggerNames = $tableBuilder->getGeneratedTriggerNames();
        
        expect($triggerNames)->toBe([
            'test_table_created_trigger',
            'test_table_updated_trigger'
        ]);
    });

    it('drops unix timestamp triggers', function () {
        $tableBuilder = new TableBuilder(true);
        $tableBuilder->table('users');
        $tableBuilder->dropColumn('created');
        $tableBuilder->dropColumn('updated');
        $tableBuilder->dropTimestampTriggers();
        $tableBuilder->build();
        
        $allQueries = implode(' ', $tableBuilder->getQueries());
        
        expect($allQueries)
            ->toContain('DROP COLUMN `created`')
            ->toContain('DROP COLUMN `updated`')
            ->toContain('DROP TRIGGER IF EXISTS `users_created_trigger`')
            ->toContain('DROP TRIGGER IF EXISTS `users_updated_trigger`');
    });

    it('handles complex table with unix timestamps and constraints', function () {
        $tableBuilder = new TableBuilder(false);
        $tableBuilder->table('blog_posts');
        $tableBuilder->addColumn('id', 'int')->primaryKey();
        $tableBuilder->addColumn('title', 'varchar')->length(255)->notNull();
        $tableBuilder->addColumn('slug', 'varchar')->length(255)->notNull();
        $tableBuilder->addColumn('author_id', 'int')->notNull();
        $tableBuilder->timestamps('created', 'updated', TimestampFormat::UNIX);
        $tableBuilder->addUnique('slug');
        $tableBuilder->addForeignKey('author_id', 'users', 'id', 'CASCADE');
        $tableBuilder->build();
        
        $allQueries = implode(' ', $tableBuilder->getQueries());
        
        expect($allQueries)
            ->toContain('`created` INT UNSIGNED NOT NULL')
            ->toContain('`updated` INT UNSIGNED NOT NULL')
            ->toContain('CREATE TRIGGER `blog_posts_created_trigger`')
            ->toContain('CREATE TRIGGER `blog_posts_updated_trigger`')
            ->toContain('CONSTRAINT `uq_slug` UNIQUE (`slug`)')
            ->toContain('FOREIGN KEY (`author_id`) REFERENCES `users` (`id`) ON DELETE CASCADE');
    });

    it('supports explicit datetime format', function () {
        $tableBuilder = new TableBuilder(false);
        $tableBuilder->table('legacy_table');
        $tableBuilder->addColumn('id', 'int')->primaryKey();
        $tableBuilder->timestamps('created', 'updated', TimestampFormat::DATETIME);
        $tableBuilder->build();
        
        $allQueries = implode(' ', $tableBuilder->getQueries());
        
        expect($allQueries)
            ->toContain('`created` TIMESTAMP NOT NULL')
            ->toContain('`updated` TIMESTAMP NOT NULL')
            ->toContain('CURRENT_TIMESTAMP')
            ->not->toContain('INT UNSIGNED')
            ->not->toContain('UNIX_TIMESTAMP()');
    });

});

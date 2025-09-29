<?php

use Tnt\Dbi\TableBuilder;

describe('TableBuilder Timestamps', function () {
    
    it('creates table with default timestamps', function () {
        $tableBuilder = new TableBuilder(false);
        $tableBuilder->table('users');
        $tableBuilder->addColumn('id', 'int')->primaryKey();
        $tableBuilder->addColumn('name', 'varchar')->length(255)->notNull();
        $tableBuilder->timestamps();
        $tableBuilder->build();
        
        $query = $tableBuilder->getQuery();
        
        expect($query)
            ->toContain('`created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP')
            ->toContain('`updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP')
            ->not->toContain('CREATE TRIGGER'); // No triggers for CREATE TABLE
    });

    it('creates table with custom timestamp columns', function () {
        $tableBuilder = new TableBuilder(false);
        $tableBuilder->table('posts');
        $tableBuilder->addColumn('id', 'int')->primaryKey();
        $tableBuilder->addColumn('title', 'varchar')->length(255)->notNull();
        $tableBuilder->timestamps('created_on', 'modified_on');
        $tableBuilder->build();
        
        $query = $tableBuilder->getQuery();
        
        expect($query)
            ->toContain('`created_on` TIMESTAMP DEFAULT CURRENT_TIMESTAMP')
            ->toContain('`modified_on` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP');
    });

    it('adds timestamps to existing table with triggers', function () {
        $tableBuilder = new TableBuilder(true);
        $tableBuilder->table('existing_table');
        $tableBuilder->addColumn('phone', 'varchar')->length(20);
        $tableBuilder->timestamps();
        $tableBuilder->build();
        
        $query = $tableBuilder->getQuery();
        
        expect($query)
            ->toContain('ADD `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP')
            ->toContain('ADD `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP')
            ->toContain('CREATE TRIGGER `existing_table_updated_at_trigger`')
            ->toContain('BEFORE UPDATE ON `existing_table`')
            ->toContain('SET NEW.`updated_at` = CURRENT_TIMESTAMP');
    });

    it('drops timestamp triggers and columns', function () {
        $tableBuilder = new TableBuilder(true);
        $tableBuilder->table('users');
        $tableBuilder->dropColumn('created_at');
        $tableBuilder->dropColumn('updated_at');
        $tableBuilder->dropTimestampTriggers();
        $tableBuilder->build();
        
        $query = $tableBuilder->getQuery();
        
        expect($query)
            ->toContain('DROP COLUMN `created_at`')
            ->toContain('DROP COLUMN `updated_at`')
            ->toContain('DROP TRIGGER IF EXISTS `users_updated_at_trigger`');
    });

    it('generates correct trigger names', function () {
        $tableBuilder = new TableBuilder(false);
        $tableBuilder->table('test_table');
        $tableBuilder->timestamps();
        $tableBuilder->build();
        
        $triggerNames = $tableBuilder->getGeneratedTriggerNames();
        
        expect($triggerNames)->toBe(['test_table_updated_at_trigger']);
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
        
        $query = $tableBuilder->getQuery();
        
        expect($query)
            ->toContain('`created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP')
            ->toContain('`updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP')
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
        
        $query = $tableBuilder->getQuery();
        
        expect($query)
            ->toContain('ADD `new_field` VARCHAR(100) NOT NULL')
            ->toContain('CHANGE `old_field` `old_field` TEXT NOT NULL')
            ->toContain('DROP COLUMN `obsolete_field`')
            ->toContain('ADD `date_created` TIMESTAMP DEFAULT CURRENT_TIMESTAMP')
            ->toContain('ADD `date_updated` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP')
            ->toContain('CREATE TRIGGER `legacy_table_updated_at_trigger`')
            ->toContain('SET NEW.`date_updated` = CURRENT_TIMESTAMP');
    });

    it('handles timestamp configuration updates', function () {
        // Step 1: Cleanup existing timestamps
        $cleanupBuilder = new TableBuilder(true);
        $cleanupBuilder->table('products');
        $cleanupBuilder->dropTimestampTriggers();
        $cleanupBuilder->dropColumn('created_at');
        $cleanupBuilder->dropColumn('updated_at');
        $cleanupBuilder->build();
        
        $cleanupQuery = $cleanupBuilder->getQuery();
        
        expect($cleanupQuery)
            ->toContain('DROP TRIGGER IF EXISTS `products_updated_at_trigger`')
            ->toContain('DROP COLUMN `created_at`')
            ->toContain('DROP COLUMN `updated_at`');
        
        // Step 2: Add new timestamp configuration
        $newBuilder = new TableBuilder(true);
        $newBuilder->table('products');
        $newBuilder->timestamps('creation_date', 'modification_date');
        $newBuilder->build();
        
        $newQuery = $newBuilder->getQuery();
        
        expect($newQuery)
            ->toContain('ADD `creation_date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP')
            ->toContain('ADD `modification_date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP')
            ->toContain('CREATE TRIGGER `products_updated_at_trigger`')
            ->toContain('SET NEW.`modification_date` = CURRENT_TIMESTAMP');
    });

    it('drops specific timestamp trigger by name', function () {
        $tableBuilder = new TableBuilder(true);
        $tableBuilder->table('users');
        $tableBuilder->dropTimestampTrigger('custom_trigger_name');
        $tableBuilder->build();
        
        $query = $tableBuilder->getQuery();
        
        expect($query)->toContain('DROP TRIGGER IF EXISTS `custom_trigger_name`');
    });

});
<?php

use Tnt\Dbi\TableBuilder;

describe('TableBuilder Basic Functionality', function () {
    
    it('creates basic table with columns', function () {
        $tableBuilder = new TableBuilder(false);
        $tableBuilder->table('users');
        $tableBuilder->addColumn('id', 'int')->primaryKey();
        $tableBuilder->addColumn('name', 'varchar')->length(255)->notNull();
        $tableBuilder->addColumn('email', 'varchar')->length(255)->notNull();
        $tableBuilder->build();
        
        $query = $tableBuilder->getQuery();
        
        expect($query)
            ->toContain('`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY')
            ->toContain('`name` VARCHAR(255) NOT NULL')
            ->toContain('`email` VARCHAR(255) NOT NULL');
    });

    it('handles different column types and constraints', function () {
        $tableBuilder = new TableBuilder(false);
        $tableBuilder->table('products');
        $tableBuilder->addColumn('id', 'int')->primaryKey();
        $tableBuilder->addColumn('name', 'varchar')->length(255)->notNull();
        $tableBuilder->addColumn('price', 'decimal')->length('10,2')->notNull();
        $tableBuilder->addColumn('description', 'text')->null();
        $tableBuilder->addColumn('active', 'boolean')->default(1);
        $tableBuilder->build();
        
        $query = $tableBuilder->getQuery();
        
        expect($query)
            ->toContain('`price` DECIMAL(10,2) NOT NULL')
            ->toContain('`description` TEXT NULL')
            ->toContain('`active` BOOLEAN NOT NULL DEFAULT 1');
    });

    it('creates foreign key constraints', function () {
        $tableBuilder = new TableBuilder(false);
        $tableBuilder->table('posts');
        $tableBuilder->addColumn('id', 'int')->primaryKey();
        $tableBuilder->addColumn('title', 'varchar')->length(255)->notNull();
        $tableBuilder->addColumn('user_id', 'int')->notNull();
        $tableBuilder->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $tableBuilder->build();
        
        $query = $tableBuilder->getQuery();
        
        expect($query)->toContain(
            'CONSTRAINT `fk_posts_user_id_users_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE'
        );
    });

    it('creates unique constraints', function () {
        $tableBuilder = new TableBuilder(false);
        $tableBuilder->table('users');
        $tableBuilder->addColumn('id', 'int')->primaryKey();
        $tableBuilder->addColumn('email', 'varchar')->length(255)->notNull();
        $tableBuilder->addColumn('username', 'varchar')->length(50)->notNull();
        $tableBuilder->addUnique('email');
        $tableBuilder->addUnique('username');
        $tableBuilder->build();
        
        $query = $tableBuilder->getQuery();
        
        expect($query)
            ->toContain('CONSTRAINT `uq_email` UNIQUE (`email`)')
            ->toContain('CONSTRAINT `uq_username` UNIQUE (`username`)');
    });

    it('handles alter table operations', function () {
        $tableBuilder = new TableBuilder(true);
        $tableBuilder->table('users');
        $tableBuilder->addColumn('phone', 'varchar')->length(20);
        $tableBuilder->changeColumn('name')->type('varchar')->length(300);
        $tableBuilder->dropColumn('old_field');
        $tableBuilder->addForeignKey('role_id', 'roles', 'id', 'SET NULL');
        $tableBuilder->dropForeignKeyByIdentifier('old_fk_constraint');
        $tableBuilder->build();
        
        $query = $tableBuilder->getQuery();
        
        expect($query)
            ->toContain('ADD `phone` VARCHAR(20) NOT NULL')
            ->toContain('CHANGE `name` `name` VARCHAR(300) NOT NULL')
            ->toContain('DROP COLUMN `old_field`')
            ->toContain('ADD CONSTRAINT `fk_users_role_id_roles_id` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE SET NULL')
            ->toContain('DROP INDEX `old_fk_constraint`')
            ->toContain('DROP FOREIGN KEY `old_fk_constraint`');
    });

    it('handles column defaults correctly', function () {
        $tableBuilder = new TableBuilder(false);
        $tableBuilder->table('settings');
        $tableBuilder->addColumn('id', 'int')->primaryKey();
        $tableBuilder->addColumn('name', 'varchar')->length(100)->notNull();
        $tableBuilder->addColumn('value', 'varchar')->length(255)->default('default_value');
        $tableBuilder->addColumn('is_active', 'boolean')->default(1);
        $tableBuilder->addColumn('created_at', 'timestamp')->default('CURRENT_TIMESTAMP');
        $tableBuilder->addColumn('nullable_field', 'varchar')->length(100)->default(null);
        $tableBuilder->build();
        
        $query = $tableBuilder->getQuery();
        
        expect($query)
            ->toContain("`value` VARCHAR(255) NOT NULL DEFAULT 'default_value'")
            ->toContain('`is_active` BOOLEAN NOT NULL DEFAULT 1')
            ->toContain("`created_at` TIMESTAMP NOT NULL DEFAULT 'CURRENT_TIMESTAMP'")
            ->toContain('`nullable_field` VARCHAR(100) NOT NULL DEFAULT NULL');
    });

    it('handles column renaming in alter table', function () {
        $tableBuilder = new TableBuilder(true);
        $tableBuilder->table('users');
        $tableBuilder->changeColumn('old_name')->rename('new_name', 'varchar', 255);
        $tableBuilder->build();
        
        $query = $tableBuilder->getQuery();
        
        expect($query)->toContain('CHANGE `old_name` `new_name` VARCHAR(255) NOT NULL');
    });

    it('handles multiple unique constraints', function () {
        $tableBuilder = new TableBuilder(false);
        $tableBuilder->table('products');
        $tableBuilder->addColumn('id', 'int')->primaryKey();
        $tableBuilder->addColumn('sku', 'varchar')->length(50)->notNull();
        $tableBuilder->addColumn('barcode', 'varchar')->length(100)->notNull();
        $tableBuilder->addUnique('sku');
        $tableBuilder->addUnique('barcode');
        $tableBuilder->build();
        
        $query = $tableBuilder->getQuery();
        
        expect($query)
            ->toContain('CONSTRAINT `uq_sku` UNIQUE (`sku`)')
            ->toContain('CONSTRAINT `uq_barcode` UNIQUE (`barcode`)');
    });

    it('handles dropping unique constraints in alter table', function () {
        $tableBuilder = new TableBuilder(true);
        $tableBuilder->table('users');
        $tableBuilder->dropUnique('email');
        $tableBuilder->dropUnique('username');
        $tableBuilder->build();
        
        $query = $tableBuilder->getQuery();
        
        expect($query)
            ->toContain('DROP INDEX `uq_email`')
            ->toContain('DROP INDEX `uq_username`');
    });

    it('handles foreign key with custom identifier', function () {
        $tableBuilder = new TableBuilder(false);
        $tableBuilder->table('posts');
        $tableBuilder->addColumn('id', 'int')->primaryKey();
        $tableBuilder->addColumn('author_id', 'int')->notNull();
        
        $foreignKey = $tableBuilder->addForeignKey('author_id', 'users', 'id', 'CASCADE');
        $foreignKey->identifier('custom_fk_posts_author');
        
        $tableBuilder->build();
        
        $query = $tableBuilder->getQuery();
        
        expect($query)->toContain('CONSTRAINT `custom_fk_posts_author` FOREIGN KEY (`author_id`) REFERENCES `users` (`id`) ON DELETE CASCADE');
    });

});

describe('TableBuilder id() Shorthand', function () {
    
    it('creates primary key with default parameters', function () {
        $tableBuilder = new TableBuilder(false);
        $tableBuilder->table('users');
        $tableBuilder->id();
        $tableBuilder->addColumn('name', 'varchar')->length(255)->notNull();
        $tableBuilder->build();
        
        $query = $tableBuilder->getQuery();
        
        expect($query)->toContain('`id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY');
    });

    it('creates primary key with custom name', function () {
        $tableBuilder = new TableBuilder(false);
        $tableBuilder->table('products');
        $tableBuilder->id('product_id');
        $tableBuilder->addColumn('name', 'varchar')->length(255)->notNull();
        $tableBuilder->build();
        
        $query = $tableBuilder->getQuery();
        
        expect($query)->toContain('`product_id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY');
    });

    it('creates primary key with custom type', function () {
        $tableBuilder = new TableBuilder(false);
        $tableBuilder->table('orders');
        $tableBuilder->id('order_id', 'bigint');
        $tableBuilder->addColumn('total', 'decimal')->length('10,2')->notNull();
        $tableBuilder->build();
        
        $query = $tableBuilder->getQuery();
        
        expect($query)->toContain('`order_id` BIGINT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY');
    });

    it('creates primary key with custom length', function () {
        $tableBuilder = new TableBuilder(false);
        $tableBuilder->table('items');
        $tableBuilder->id('item_id', 'int', 20);
        $tableBuilder->addColumn('description', 'text')->null();
        $tableBuilder->build();
        
        $query = $tableBuilder->getQuery();
        
        expect($query)->toContain('`item_id` INT(20) NOT NULL AUTO_INCREMENT PRIMARY KEY');
    });

    it('creates primary key without auto increment', function () {
        $tableBuilder = new TableBuilder(false);
        $tableBuilder->table('settings');
        $tableBuilder->id('setting_id', 'int', 11, false);
        $tableBuilder->addColumn('value', 'varchar')->length(255)->notNull();
        $tableBuilder->build();
        
        $query = $tableBuilder->getQuery();
        
        expect($query)
            ->toContain('`setting_id` INT(11) NOT NULL PRIMARY KEY')
            ->not->toContain('AUTO_INCREMENT');
    });

    it('maintains fluent interface', function () {
        $tableBuilder = new TableBuilder(false);
        $tableBuilder->table('users');
        
        $result = $tableBuilder->id();
        
        expect($result)->toBe($tableBuilder);
    });

    it('works in alter table context', function () {
        $tableBuilder = new TableBuilder(true);
        $tableBuilder->table('legacy_table');
        $tableBuilder->id('new_id');
        $tableBuilder->build();
        
        $query = $tableBuilder->getQuery();
        
        expect($query)->toContain('ADD `new_id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY');
    });

});
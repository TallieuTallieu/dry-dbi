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
        $tableBuilder->addForeignKey(
            'user_id',
            'users',
            'id',
            'CASCADE',
            'CASCADE'
        );
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
            ->toContain(
                'ADD CONSTRAINT `fk_users_role_id_roles_id` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE SET NULL'
            )
            ->toContain('DROP INDEX `old_fk_constraint`')
            ->toContain('DROP FOREIGN KEY `old_fk_constraint`');
    });

    it('handles column defaults correctly', function () {
        $tableBuilder = new TableBuilder(false);
        $tableBuilder->table('settings');
        $tableBuilder->addColumn('id', 'int')->primaryKey();
        $tableBuilder->addColumn('name', 'varchar')->length(100)->notNull();
        $tableBuilder
            ->addColumn('value', 'varchar')
            ->length(255)
            ->default('default_value');
        $tableBuilder->addColumn('is_active', 'boolean')->default(1);
        $tableBuilder
            ->addColumn('created_at', 'timestamp')
            ->default('CURRENT_TIMESTAMP');
        $tableBuilder
            ->addColumn('nullable_field', 'varchar')
            ->length(100)
            ->default(null);
        $tableBuilder->build();

        $query = $tableBuilder->getQuery();

        expect($query)
            ->toContain("`value` VARCHAR(255) NOT NULL DEFAULT 'default_value'")
            ->toContain('`is_active` BOOLEAN NOT NULL DEFAULT 1')
            ->toContain(
                "`created_at` TIMESTAMP NOT NULL DEFAULT 'CURRENT_TIMESTAMP'"
            )
            ->toContain('`nullable_field` VARCHAR(100) NOT NULL DEFAULT NULL');
    });

    it('handles column renaming in alter table', function () {
        $tableBuilder = new TableBuilder(true);
        $tableBuilder->table('users');
        $tableBuilder
            ->changeColumn('old_name')
            ->rename('new_name', 'varchar', 255);
        $tableBuilder->build();

        $query = $tableBuilder->getQuery();

        expect($query)->toContain(
            'CHANGE `old_name` `new_name` VARCHAR(255) NOT NULL'
        );
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

        $foreignKey = $tableBuilder->addForeignKey(
            'author_id',
            'users',
            'id',
            'CASCADE'
        );
        $foreignKey->identifier('custom_fk_posts_author');

        $tableBuilder->build();

        $query = $tableBuilder->getQuery();

        expect($query)->toContain(
            'CONSTRAINT `custom_fk_posts_author` FOREIGN KEY (`author_id`) REFERENCES `users` (`id`) ON DELETE CASCADE'
        );
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

        expect($query)->toContain(
            '`id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY'
        );
    });

    it('creates primary key with custom name', function () {
        $tableBuilder = new TableBuilder(false);
        $tableBuilder->table('products');
        $tableBuilder->id('product_id');
        $tableBuilder->addColumn('name', 'varchar')->length(255)->notNull();
        $tableBuilder->build();

        $query = $tableBuilder->getQuery();

        expect($query)->toContain(
            '`product_id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY'
        );
    });

    it('creates primary key with custom type', function () {
        $tableBuilder = new TableBuilder(false);
        $tableBuilder->table('orders');
        $tableBuilder->id('order_id', 'bigint');
        $tableBuilder->addColumn('total', 'decimal')->length('10,2')->notNull();
        $tableBuilder->build();

        $query = $tableBuilder->getQuery();

        expect($query)->toContain(
            '`order_id` BIGINT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY'
        );
    });

    it('creates primary key with custom length', function () {
        $tableBuilder = new TableBuilder(false);
        $tableBuilder->table('items');
        $tableBuilder->id('item_id', 'int', 20);
        $tableBuilder->addColumn('description', 'text')->null();
        $tableBuilder->build();

        $query = $tableBuilder->getQuery();

        expect($query)->toContain(
            '`item_id` INT(20) NOT NULL AUTO_INCREMENT PRIMARY KEY'
        );
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
            ->not()
            ->toContain('AUTO_INCREMENT');
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

        expect($query)->toContain(
            'ADD `new_id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY'
        );
    });
});

describe('TableBuilder seo() Shorthand', function () {
    it('creates SEO columns with default names', function () {
        $tableBuilder = new TableBuilder(false);
        $tableBuilder->table('pages');
        $tableBuilder->id();
        $tableBuilder->addColumn('title', 'varchar')->length(255)->notNull();
        $tableBuilder->seo();
        $tableBuilder->build();

        $query = $tableBuilder->getQuery();

        expect($query)
            ->toContain('`seo_title` VARCHAR(255) NULL')
            ->toContain('`seo_description` VARCHAR(255) NULL')
            ->toContain('`seo_change_frequency` VARCHAR(255) NULL')
            ->toContain('`seo_photo` INT(11) NOT NULL')
            ->toContain('`seo_priority` DECIMAL(10) NULL')
            ->toContain(
                'CONSTRAINT `fk_pages_seo_photo_dry_media_file_id` FOREIGN KEY (`seo_photo`) REFERENCES `dry_media_file` (`id`)'
            );
    });

    it('creates SEO columns with custom names', function () {
        $tableBuilder = new TableBuilder(false);
        $tableBuilder->table('products');
        $tableBuilder->id();
        $tableBuilder->seo(
            'meta_title',
            'meta_desc',
            'sitemap_freq',
            'image_id',
            'sitemap_priority'
        );
        $tableBuilder->build();

        $query = $tableBuilder->getQuery();

        expect($query)
            ->toContain('`meta_title` VARCHAR(255) NULL')
            ->toContain('`meta_desc` VARCHAR(255) NULL')
            ->toContain('`sitemap_freq` VARCHAR(255) NULL')
            ->toContain('`image_id` INT(11) NOT NULL')
            ->toContain('`sitemap_priority` DECIMAL(10) NULL')
            ->toContain(
                'CONSTRAINT `fk_products_image_id_dry_media_file_id` FOREIGN KEY (`image_id`) REFERENCES `dry_media_file` (`id`)'
            );
    });

    it('skips columns set to null', function () {
        $tableBuilder = new TableBuilder(false);
        $tableBuilder->table('articles');
        $tableBuilder->id();
        $tableBuilder->seo(null, 'meta_description', null, 'photo_id', null);
        $tableBuilder->build();

        $query = $tableBuilder->getQuery();

        expect($query)
            ->not()
            ->toContain('`seo_title`')
            ->toContain('`meta_description` VARCHAR(255) NULL')
            ->not()
            ->toContain('`seo_change_frequency`')
            ->toContain('`photo_id` INT(11) NOT NULL')
            ->not()
            ->toContain('`seo_priority`')
            ->toContain(
                'CONSTRAINT `fk_articles_photo_id_dry_media_file_id` FOREIGN KEY (`photo_id`) REFERENCES `dry_media_file` (`id`)'
            );
    });

    it('skips columns set to empty string', function () {
        $tableBuilder = new TableBuilder(false);
        $tableBuilder->table('posts');
        $tableBuilder->id();
        $tableBuilder->seo('', 'description', '', 'image', '');
        $tableBuilder->build();

        $query = $tableBuilder->getQuery();

        expect($query)
            ->not()
            ->toContain('seo_title')
            ->toContain('`description` VARCHAR(255) NULL')
            ->not()
            ->toContain('seo_change_frequency')
            ->toContain('`image` INT(11) NOT NULL')
            ->not()
            ->toContain('seo_priority');
    });

    it('can skip all columns', function () {
        $tableBuilder = new TableBuilder(false);
        $tableBuilder->table('minimal');
        $tableBuilder->id();
        $tableBuilder->seo(null, null, null, null, null);
        $tableBuilder->build();

        $query = $tableBuilder->getQuery();

        expect($query)
            ->not()
            ->toContain('seo_title')
            ->not()
            ->toContain('seo_description')
            ->not()
            ->toContain('seo_change_frequency')
            ->not()
            ->toContain('seo_photo')
            ->not()
            ->toContain('seo_priority')
            ->not()
            ->toContain('FOREIGN KEY');
    });

    it('maintains fluent interface', function () {
        $tableBuilder = new TableBuilder(false);
        $tableBuilder->table('pages');

        $result = $tableBuilder->seo();

        expect($result)->toBe($tableBuilder);
    });

    it('works in alter table context', function () {
        $tableBuilder = new TableBuilder(true);
        $tableBuilder->table('existing_pages');
        $tableBuilder->seo();
        $tableBuilder->build();

        $query = $tableBuilder->getQuery();

        expect($query)
            ->toContain('ADD `seo_title` VARCHAR(255) NULL')
            ->toContain('ADD `seo_description` VARCHAR(255) NULL')
            ->toContain('ADD `seo_change_frequency` VARCHAR(255) NULL')
            ->toContain('ADD `seo_photo` INT(11) NOT NULL')
            ->toContain('ADD `seo_priority` DECIMAL(10) NULL')
            ->toContain(
                'ADD CONSTRAINT `fk_existing_pages_seo_photo_dry_media_file_id` FOREIGN KEY (`seo_photo`) REFERENCES `dry_media_file` (`id`)'
            );
    });

    it('can be chained with other methods', function () {
        $tableBuilder = new TableBuilder(false);
        $tableBuilder
            ->table('pages')
            ->id()
            ->seo()
            ->addColumn('content', 'text')
            ->notNull();
        $tableBuilder->build();

        $query = $tableBuilder->getQuery();

        expect($query)
            ->toContain('`id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY')
            ->toContain('`seo_title` VARCHAR(255) NULL')
            ->toContain('`seo_description` VARCHAR(255) NULL')
            ->toContain('`content` TEXT NOT NULL');
    });

    it('creates all five SEO columns correctly', function () {
        $tableBuilder = new TableBuilder(false);
        $tableBuilder->table('blog_posts');
        $tableBuilder->id();
        $tableBuilder->seo();
        $tableBuilder->build();

        $query = $tableBuilder->getQuery();

        $seoColumnsFound = 0;
        if (str_contains($query, '`seo_title`')) {
            $seoColumnsFound++;
        }
        if (str_contains($query, '`seo_description`')) {
            $seoColumnsFound++;
        }
        if (str_contains($query, '`seo_change_frequency`')) {
            $seoColumnsFound++;
        }
        if (str_contains($query, '`seo_photo`')) {
            $seoColumnsFound++;
        }
        if (str_contains($query, '`seo_priority`')) {
            $seoColumnsFound++;
        }

        expect($seoColumnsFound)->toBe(5);
    });
});

describe('TableBuilder dropSeo() Shorthand', function () {
    it('drops all SEO columns and foreign key with default names', function () {
        $tableBuilder = new TableBuilder(true);
        $tableBuilder->table('pages');
        $tableBuilder->dropSeo();
        $tableBuilder->build();

        $query = $tableBuilder->getQuery();

        expect($query)
            ->toContain('DROP COLUMN `seo_title`')
            ->toContain('DROP COLUMN `seo_description`')
            ->toContain('DROP COLUMN `seo_change_frequency`')
            ->toContain('DROP COLUMN `seo_photo`')
            ->toContain('DROP COLUMN `seo_priority`')
            ->toContain('DROP INDEX `fk_pages_seo_photo_dry_media_file_id`')
            ->toContain(
                'DROP FOREIGN KEY `fk_pages_seo_photo_dry_media_file_id`'
            );
    });

    it('drops SEO columns with custom names', function () {
        $tableBuilder = new TableBuilder(true);
        $tableBuilder->table('products');
        $tableBuilder->dropSeo(
            'meta_title',
            'meta_desc',
            'sitemap_freq',
            'image_id',
            'sitemap_priority'
        );
        $tableBuilder->build();

        $query = $tableBuilder->getQuery();

        expect($query)
            ->toContain('DROP COLUMN `meta_title`')
            ->toContain('DROP COLUMN `meta_desc`')
            ->toContain('DROP COLUMN `sitemap_freq`')
            ->toContain('DROP COLUMN `image_id`')
            ->toContain('DROP COLUMN `sitemap_priority`')
            ->toContain('DROP INDEX `fk_products_image_id_dry_media_file_id`')
            ->toContain(
                'DROP FOREIGN KEY `fk_products_image_id_dry_media_file_id`'
            );
    });

    it('skips columns set to null', function () {
        $tableBuilder = new TableBuilder(true);
        $tableBuilder->table('articles');
        $tableBuilder->dropSeo(
            null,
            'meta_description',
            null,
            'photo_id',
            null
        );
        $tableBuilder->build();

        $query = $tableBuilder->getQuery();

        expect($query)
            ->not()
            ->toContain('DROP COLUMN `seo_title`')
            ->toContain('DROP COLUMN `meta_description`')
            ->not()
            ->toContain('DROP COLUMN `seo_change_frequency`')
            ->toContain('DROP COLUMN `photo_id`')
            ->not()
            ->toContain('DROP COLUMN `seo_priority`')
            ->toContain(
                'DROP FOREIGN KEY `fk_articles_photo_id_dry_media_file_id`'
            );
    });

    it('skips columns set to empty string', function () {
        $tableBuilder = new TableBuilder(true);
        $tableBuilder->table('posts');
        $tableBuilder->dropSeo('', 'description', '', 'image', '');
        $tableBuilder->build();

        $query = $tableBuilder->getQuery();

        expect($query)
            ->not()
            ->toContain('DROP COLUMN `seo_title`')
            ->toContain('DROP COLUMN `description`')
            ->not()
            ->toContain('DROP COLUMN `seo_change_frequency`')
            ->toContain('DROP COLUMN `image`')
            ->not()
            ->toContain('DROP COLUMN `seo_priority`');
    });

    it('can skip all columns', function () {
        $tableBuilder = new TableBuilder(true);
        $tableBuilder->table('minimal');
        $tableBuilder->dropSeo(null, null, null, null, null);
        $tableBuilder->build();

        $query = $tableBuilder->getQuery();

        // Query should be essentially empty (just ALTER TABLE)
        expect($query)->not()->toContain('DROP COLUMN');
    });

    it('maintains fluent interface', function () {
        $tableBuilder = new TableBuilder(true);
        $tableBuilder->table('pages');

        $result = $tableBuilder->dropSeo();

        expect($result)->toBe($tableBuilder);
    });

    it('can be chained with other alter operations', function () {
        $tableBuilder = new TableBuilder(true);
        $tableBuilder->table('pages');
        $tableBuilder
            ->dropSeo()
            ->addColumn('new_field', 'varchar')
            ->length(100);
        $tableBuilder->build();

        $query = $tableBuilder->getQuery();

        expect($query)
            ->toContain('DROP COLUMN `seo_title`')
            ->toContain('DROP COLUMN `seo_photo`')
            ->toContain(
                'DROP FOREIGN KEY `fk_pages_seo_photo_dry_media_file_id`'
            )
            ->toContain('ADD `new_field` VARCHAR(100) NOT NULL');
    });

    it('drops all five SEO columns', function () {
        $tableBuilder = new TableBuilder(true);
        $tableBuilder->table('blog_posts');
        $tableBuilder->dropSeo();
        $tableBuilder->build();

        $query = $tableBuilder->getQuery();

        $droppedColumns = 0;
        if (str_contains($query, 'DROP COLUMN `seo_title`')) {
            $droppedColumns++;
        }
        if (str_contains($query, 'DROP COLUMN `seo_description`')) {
            $droppedColumns++;
        }
        if (str_contains($query, 'DROP COLUMN `seo_change_frequency`')) {
            $droppedColumns++;
        }
        if (str_contains($query, 'DROP COLUMN `seo_photo`')) {
            $droppedColumns++;
        }
        if (str_contains($query, 'DROP COLUMN `seo_priority`')) {
            $droppedColumns++;
        }

        expect($droppedColumns)->toBe(5);
    });
});

describe('TableBuilder Index Management', function () {
    it('creates single column index', function () {
        $tableBuilder = new TableBuilder(false);
        $tableBuilder->table('users');
        $tableBuilder->addColumn('id', 'int')->primaryKey();
        $tableBuilder->addColumn('email', 'varchar')->length(255)->notNull();
        $tableBuilder->addIndex('email');
        $tableBuilder->build();

        $query = $tableBuilder->getQuery();

        expect($query)->toContain('INDEX `idx_email` (`email`)');
    });

    it('creates composite index with multiple columns', function () {
        $tableBuilder = new TableBuilder(false);
        $tableBuilder->table('posts');
        $tableBuilder->addColumn('id', 'int')->primaryKey();
        $tableBuilder->addColumn('user_id', 'int')->notNull();
        $tableBuilder->addColumn('created', 'timestamp')->notNull();
        $tableBuilder->addIndex(['user_id', 'created']);
        $tableBuilder->build();

        $query = $tableBuilder->getQuery();

        expect($query)->toContain(
            'INDEX `idx_user_id_created` (`user_id`, `created`)'
        );
    });

    it('creates multiple indexes', function () {
        $tableBuilder = new TableBuilder(false);
        $tableBuilder->table('products');
        $tableBuilder->addColumn('id', 'int')->primaryKey();
        $tableBuilder->addColumn('sku', 'varchar')->length(50)->notNull();
        $tableBuilder->addColumn('category', 'varchar')->length(100)->notNull();
        $tableBuilder->addColumn('status', 'varchar')->length(20)->notNull();
        $tableBuilder->addIndex('sku');
        $tableBuilder->addIndex(['category', 'status']);
        $tableBuilder->build();

        $query = $tableBuilder->getQuery();

        expect($query)
            ->toContain('INDEX `idx_sku` (`sku`)')
            ->toContain('INDEX `idx_category_status` (`category`, `status`)');
    });

    it('creates index with custom identifier', function () {
        $tableBuilder = new TableBuilder(false);
        $tableBuilder->table('users');
        $tableBuilder->addColumn('id', 'int')->primaryKey();
        $tableBuilder->addColumn('email', 'varchar')->length(255)->notNull();

        $index = $tableBuilder->addIndex('email');
        $index->identifier('custom_email_index');

        $tableBuilder->build();

        $query = $tableBuilder->getQuery();

        expect($query)->toContain('INDEX `custom_email_index` (`email`)');
    });

    it('creates composite index with custom identifier', function () {
        $tableBuilder = new TableBuilder(false);
        $tableBuilder->table('orders');
        $tableBuilder->addColumn('id', 'int')->primaryKey();
        $tableBuilder->addColumn('user_id', 'int')->notNull();
        $tableBuilder->addColumn('status', 'varchar')->length(20)->notNull();

        $index = $tableBuilder->addIndex(['user_id', 'status']);
        $index->identifier('idx_user_orders');

        $tableBuilder->build();

        $query = $tableBuilder->getQuery();

        expect($query)->toContain(
            'INDEX `idx_user_orders` (`user_id`, `status`)'
        );
    });

    it('adds index in alter table', function () {
        $tableBuilder = new TableBuilder(true);
        $tableBuilder->table('users');
        $tableBuilder->addIndex('email');
        $tableBuilder->build();

        $query = $tableBuilder->getQuery();

        expect($query)->toContain('ADD INDEX `idx_email` (`email`)');
    });

    it('drops index in alter table', function () {
        $tableBuilder = new TableBuilder(true);
        $tableBuilder->table('users');
        $tableBuilder->dropIndex('email');
        $tableBuilder->build();

        $query = $tableBuilder->getQuery();

        expect($query)->toContain('DROP INDEX `idx_email`');
    });

    it('drops composite index in alter table', function () {
        $tableBuilder = new TableBuilder(true);
        $tableBuilder->table('posts');
        $tableBuilder->dropIndex(['user_id', 'created']);
        $tableBuilder->build();

        $query = $tableBuilder->getQuery();

        expect($query)->toContain('DROP INDEX `idx_user_id_created`');
    });

    it('drops index by identifier', function () {
        $tableBuilder = new TableBuilder(true);
        $tableBuilder->table('users');
        $tableBuilder->dropIndexByIdentifier('custom_email_index');
        $tableBuilder->build();

        $query = $tableBuilder->getQuery();

        expect($query)->toContain('DROP INDEX `custom_email_index`');
    });

    it('handles multiple index operations in alter table', function () {
        $tableBuilder = new TableBuilder(true);
        $tableBuilder->table('products');
        $tableBuilder->addIndex('sku');
        $tableBuilder->addIndex(['category', 'status']);
        $tableBuilder->dropIndex('old_index_column');
        $tableBuilder->dropIndexByIdentifier('old_custom_index');
        $tableBuilder->build();

        $query = $tableBuilder->getQuery();

        expect($query)
            ->toContain('DROP INDEX `idx_old_index_column`')
            ->toContain('DROP INDEX `old_custom_index`')
            ->toContain('ADD INDEX `idx_sku` (`sku`)')
            ->toContain(
                'ADD INDEX `idx_category_status` (`category`, `status`)'
            );
    });

    it('creates three column composite index', function () {
        $tableBuilder = new TableBuilder(false);
        $tableBuilder->table('logs');
        $tableBuilder->addColumn('id', 'int')->primaryKey();
        $tableBuilder->addColumn('user_id', 'int')->notNull();
        $tableBuilder->addColumn('action', 'varchar')->length(50)->notNull();
        $tableBuilder->addColumn('created', 'timestamp')->notNull();
        $tableBuilder->addIndex(['user_id', 'action', 'created']);
        $tableBuilder->build();

        $query = $tableBuilder->getQuery();

        expect($query)->toContain(
            'INDEX `idx_user_id_action_created` (`user_id`, `action`, `created`)'
        );
    });

    it('combines indexes with other constraints', function () {
        $tableBuilder = new TableBuilder(false);
        $tableBuilder->table('posts');
        $tableBuilder->addColumn('id', 'int')->primaryKey();
        $tableBuilder->addColumn('user_id', 'int')->notNull();
        $tableBuilder->addColumn('slug', 'varchar')->length(255)->notNull();
        $tableBuilder->addColumn('status', 'varchar')->length(20)->notNull();
        $tableBuilder->addForeignKey('user_id', 'users', 'id', 'CASCADE');
        $tableBuilder->addUnique('slug');
        $tableBuilder->addIndex('status');
        $tableBuilder->addIndex(['user_id', 'status']);
        $tableBuilder->build();

        $query = $tableBuilder->getQuery();

        expect($query)
            ->toContain(
                'CONSTRAINT `fk_posts_user_id_users_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE'
            )
            ->toContain('CONSTRAINT `uq_slug` UNIQUE (`slug`)')
            ->toContain('INDEX `idx_status` (`status`)')
            ->toContain('INDEX `idx_user_id_status` (`user_id`, `status`)');
    });
});

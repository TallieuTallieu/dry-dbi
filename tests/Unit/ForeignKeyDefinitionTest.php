<?php

use Tnt\Dbi\ForeignKeyDefinition;

describe('ForeignKeyDefinition', function () {
    it('creates basic foreign key definition', function () {
        $fk = new ForeignKeyDefinition(
            'posts',
            'user_id',
            'users',
            'id',
            'CASCADE',
            'CASCADE'
        );

        expect($fk->getColumn())
            ->toBe('user_id')
            ->and($fk->getForeignTable())
            ->toBe('users')
            ->and($fk->getForeignColumn())
            ->toBe('id')
            ->and($fk->getOnDelete())
            ->toBe('CASCADE')
            ->and($fk->getOnUpdate())
            ->toBe('CASCADE');
    });

    it('generates default identifier', function () {
        $fk = new ForeignKeyDefinition(
            'posts',
            'user_id',
            'users',
            'id',
            'CASCADE',
            'CASCADE'
        );

        expect($fk->getIdentifier())->toBe('fk_posts_user_id_users_id');
    });

    it('uses custom identifier when set', function () {
        $fk = new ForeignKeyDefinition(
            'posts',
            'author_id',
            'users',
            'id',
            'CASCADE',
            'CASCADE'
        );
        $fk->identifier('custom_fk_posts_author');

        expect($fk->getIdentifier())->toBe('custom_fk_posts_author');
    });

    it('handles SET NULL on delete', function () {
        $fk = new ForeignKeyDefinition(
            'posts',
            'category_id',
            'categories',
            'id',
            'SET NULL',
            'CASCADE'
        );

        expect($fk->getOnDelete())
            ->toBe('SET NULL')
            ->and($fk->getOnUpdate())
            ->toBe('CASCADE');
    });

    it('handles RESTRICT on update', function () {
        $fk = new ForeignKeyDefinition(
            'orders',
            'product_id',
            'products',
            'id',
            'CASCADE',
            'RESTRICT'
        );

        expect($fk->getOnDelete())
            ->toBe('CASCADE')
            ->and($fk->getOnUpdate())
            ->toBe('RESTRICT');
    });

    it('handles NO ACTION', function () {
        $fk = new ForeignKeyDefinition(
            'items',
            'parent_id',
            'items',
            'id',
            'NO ACTION',
            'NO ACTION'
        );

        expect($fk->getOnDelete())
            ->toBe('NO ACTION')
            ->and($fk->getOnUpdate())
            ->toBe('NO ACTION');
    });

    it('generates identifier with complex table names', function () {
        $fk = new ForeignKeyDefinition(
            'blog_posts',
            'author_user_id',
            'site_users',
            'user_id',
            'CASCADE',
            'CASCADE'
        );

        expect($fk->getIdentifier())->toBe(
            'fk_blog_posts_author_user_id_site_users_user_id'
        );
    });

    it('returns fluent interface from identifier method', function () {
        $fk = new ForeignKeyDefinition(
            'posts',
            'user_id',
            'users',
            'id',
            'CASCADE',
            'CASCADE'
        );
        $result = $fk->identifier('custom_fk');

        expect($result)->toBe($fk);
    });

    it('handles self-referencing foreign key', function () {
        $fk = new ForeignKeyDefinition(
            'categories',
            'parent_id',
            'categories',
            'id',
            'CASCADE',
            'CASCADE'
        );

        expect($fk->getColumn())
            ->toBe('parent_id')
            ->and($fk->getForeignTable())
            ->toBe('categories')
            ->and($fk->getForeignColumn())
            ->toBe('id')
            ->and($fk->getIdentifier())
            ->toBe('fk_categories_parent_id_categories_id');
    });
});

<?php

use Tnt\Dbi\ColumnDefinition;
use Tnt\Dbi\Raw;

describe('ColumnDefinition', function () {
    it('creates basic column definition', function () {
        $column = new ColumnDefinition('username');
        $column->type('varchar')->length(255)->notNull();

        expect($column->getString())->toBe('`username` VARCHAR(255) NOT NULL');
    });

    it('creates column with primary key', function () {
        $column = new ColumnDefinition('id');
        $column->type('int')->primaryKey();

        expect($column->getString())->toBe(
            '`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY'
        );
    });

    it('creates column with primary key without auto increment', function () {
        $column = new ColumnDefinition('id');
        $column->type('int')->primaryKey(false);

        expect($column->getString())->toBe('`id` INT NOT NULL PRIMARY KEY');
    });

    it('creates nullable column', function () {
        $column = new ColumnDefinition('description');
        $column->type('text')->null();

        expect($column->getString())->toBe('`description` TEXT NULL');
    });

    it('creates column with string default value', function () {
        $column = new ColumnDefinition('status');
        $column->type('varchar')->length(50)->notNull()->default('active');

        expect($column->getString())->toBe(
            "`status` VARCHAR(50) NOT NULL DEFAULT 'active'"
        );
    });

    it('creates column with numeric default value', function () {
        $column = new ColumnDefinition('count');
        $column->type('int')->notNull()->default(0);

        expect($column->getString())->toBe('`count` INT NOT NULL DEFAULT 0');
    });

    it('creates column with null default value', function () {
        $column = new ColumnDefinition('optional_field');
        $column->type('varchar')->length(100)->notNull()->default(null);

        expect($column->getString())->toBe(
            '`optional_field` VARCHAR(100) NOT NULL DEFAULT NULL'
        );
    });

    it('creates column with CURRENT_TIMESTAMP default', function () {
        $column = new ColumnDefinition('created_at');
        $column->type('timestamp')->notNull()->default('CURRENT_TIMESTAMP');

        expect($column->getString())->toBe(
            "`created_at` TIMESTAMP NOT NULL DEFAULT 'CURRENT_TIMESTAMP'"
        );
    });

    it('handles decimal column with precision', function () {
        $column = new ColumnDefinition('price');
        $column->type('decimal')->length('10,2')->notNull();

        expect($column->getString())->toBe('`price` DECIMAL(10,2) NOT NULL');
    });

    it('creates boolean column with default', function () {
        $column = new ColumnDefinition('is_active');
        $column->type('boolean')->notNull()->default(1);

        expect($column->getString())->toBe(
            '`is_active` BOOLEAN NOT NULL DEFAULT 1'
        );
    });

    it('handles column rename in alter mode', function () {
        $column = new ColumnDefinition('old_name', true);
        $column->rename('new_name', 'varchar', 255);

        expect($column->getString())->toBe(
            '`old_name` `new_name` VARCHAR(255) NOT NULL'
        );
    });

    it('handles column type change in alter mode', function () {
        $column = new ColumnDefinition('name', true);
        $column->type('varchar')->length(300);

        expect($column->getString())->toBe(
            '`name` `name` VARCHAR(300) NOT NULL'
        );
    });

    it('creates generated column', function () {
        $column = new ColumnDefinition('full_name');
        $column
            ->type('varchar')
            ->length(255)
            ->generate("CONCAT(first_name, ' ', last_name)");

        expect($column->getString())->toBe(
            "`full_name` VARCHAR(255) GENERATED ALWAYS as (CONCAT(first_name, ' ', last_name))"
        );
    });

    it('handles auto increment without primary key', function () {
        $column = new ColumnDefinition('sequence');
        $column->type('int')->notNull()->autoIncrement();

        expect($column->getString())->toBe(
            '`sequence` INT NOT NULL AUTO_INCREMENT'
        );
    });

    it('escapes single quotes in default string values', function () {
        $column = new ColumnDefinition('message');
        $column
            ->type('varchar')
            ->length(255)
            ->notNull()
            ->default("It's a test");

        expect($column->getString())->toBe(
            "`message` VARCHAR(255) NOT NULL DEFAULT 'It''s a test'"
        );
    });

    it('throws exception for empty column name', function () {
        expect(fn() => new ColumnDefinition(''))->toThrow(
            \InvalidArgumentException::class,
            'Column name must be a valid identifier'
        );
    });

    it('throws exception for invalid column name', function () {
        expect(fn() => new ColumnDefinition('123invalid'))->toThrow(
            \InvalidArgumentException::class,
            'Column name must be a valid identifier'
        );
    });

    it('throws exception for column name with spaces', function () {
        expect(fn() => new ColumnDefinition('invalid name'))->toThrow(
            \InvalidArgumentException::class,
            'Column name must be a valid identifier'
        );
    });

    it('throws exception for empty type', function () {
        $column = new ColumnDefinition('test');
        expect(fn() => $column->type(''))->toThrow(
            \InvalidArgumentException::class,
            'Column type cannot be empty'
        );
    });

    it('throws exception for empty rename name', function () {
        $column = new ColumnDefinition('old_name', true);
        expect(fn() => $column->rename('', 'varchar', 255))->toThrow(
            \InvalidArgumentException::class,
            'New column name must be a valid identifier'
        );
    });

    it('throws exception for invalid rename name', function () {
        $column = new ColumnDefinition('old_name', true);
        expect(fn() => $column->rename('123invalid', 'varchar', 255))->toThrow(
            \InvalidArgumentException::class,
            'New column name must be a valid identifier'
        );
    });

    it('throws exception for empty type in rename', function () {
        $column = new ColumnDefinition('old_name', true);
        expect(fn() => $column->rename('new_name', '', 255))->toThrow(
            \InvalidArgumentException::class,
            'Column type cannot be empty'
        );
    });

    it('accepts valid identifiers with underscores', function () {
        $column = new ColumnDefinition('user_id');
        $column->type('int')->notNull();

        expect($column->getString())->toBe('`user_id` INT NOT NULL');
    });

    it('accepts valid identifiers starting with underscore', function () {
        $column = new ColumnDefinition('_private');
        $column->type('varchar')->length(100)->notNull();

        expect($column->getString())->toBe('`_private` VARCHAR(100) NOT NULL');
    });

    it('handles text column without length', function () {
        $column = new ColumnDefinition('content');
        $column->type('text')->null();

        expect($column->getString())->toBe('`content` TEXT NULL');
    });

    it('handles column with only type specified', function () {
        $column = new ColumnDefinition('simple');
        $column->type('int');

        expect($column->getString())->toContain('`simple` INT');
    });

    it('creates JSON column with JSON_ARRAY() default expression', function () {
        $column = new ColumnDefinition('tags');
        $column->type('json')->notNull()->default(new Raw('JSON_ARRAY()'));

        expect($column->getString())->toBe(
            '`tags` JSON NOT NULL DEFAULT (JSON_ARRAY())'
        );
    });

    it(
        'creates JSON column with JSON_OBJECT() default expression',
        function () {
            $column = new ColumnDefinition('metadata');
            $column->type('json')->notNull()->default(new Raw('JSON_OBJECT()'));

            expect($column->getString())->toBe(
                '`metadata` JSON NOT NULL DEFAULT (JSON_OBJECT())'
            );
        }
    );

    it(
        'creates JSON column with complex JSON_OBJECT() expression',
        function () {
            $column = new ColumnDefinition('settings');
            $column
                ->type('json')
                ->notNull()
                ->default(
                    new Raw(
                        "JSON_OBJECT('theme', 'light', 'notifications', true)"
                    )
                );

            expect($column->getString())->toBe(
                "`settings` JSON NOT NULL DEFAULT (JSON_OBJECT('theme', 'light', 'notifications', true))"
            );
        }
    );

    it('creates TEXT column with expression default', function () {
        $column = new ColumnDefinition('content');
        $column->type('text')->notNull()->default(new Raw('(UUID())'));

        expect($column->getString())->toBe(
            '`content` TEXT NOT NULL DEFAULT ((UUID()))'
        );
    });

    it('creates BLOB column with expression default', function () {
        $column = new ColumnDefinition('data');
        $column->type('blob')->notNull()->default(new Raw('(UNHEX(""))'));

        expect($column->getString())->toBe(
            '`data` BLOB NOT NULL DEFAULT ((UNHEX("")))'
        );
    });

    it('handles nullable JSON column with JSON_ARRAY() default', function () {
        $column = new ColumnDefinition('optional_tags');
        $column->type('json')->null()->default(new Raw('JSON_ARRAY()'));

        expect($column->getString())->toBe(
            '`optional_tags` JSON NULL DEFAULT (JSON_ARRAY())'
        );
    });

    it('creates JSON column with nested JSON_ARRAY() expression', function () {
        $column = new ColumnDefinition('matrix');
        $column
            ->type('json')
            ->notNull()
            ->default(new Raw('JSON_ARRAY(JSON_ARRAY(), JSON_ARRAY())'));

        expect($column->getString())->toBe(
            '`matrix` JSON NOT NULL DEFAULT (JSON_ARRAY(JSON_ARRAY(), JSON_ARRAY()))'
        );
    });

    it('distinguishes between string and expression defaults', function () {
        $stringColumn = new ColumnDefinition('string_col');
        $stringColumn->type('varchar')->length(100)->notNull()->default('test');

        $expressionColumn = new ColumnDefinition('expr_col');
        $expressionColumn
            ->type('json')
            ->notNull()
            ->default(new Raw('JSON_ARRAY()'));

        expect($stringColumn->getString())->toBe(
            "`string_col` VARCHAR(100) NOT NULL DEFAULT 'test'"
        );
        expect($expressionColumn->getString())->toBe(
            '`expr_col` JSON NOT NULL DEFAULT (JSON_ARRAY())'
        );
    });
});

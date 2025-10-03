<?php

use Tnt\Dbi\Raw;

describe('Raw', function () {
    it('creates raw statement with value', function () {
        $raw = new Raw('COUNT(*)');

        expect($raw->getValue())
            ->toBe('COUNT(*)')
            ->and($raw->getBindings())
            ->toBe([]);
    });

    it('creates raw statement with bindings', function () {
        $raw = new Raw('users.id = ?', [123]);

        expect($raw->getValue())
            ->toBe('users.id = ?')
            ->and($raw->getBindings())
            ->toBe([123]);
    });

    it('creates raw statement with multiple bindings', function () {
        $raw = new Raw('CONCAT(?, " ", ?)', ['John', 'Doe']);

        expect($raw->getValue())
            ->toBe('CONCAT(?, " ", ?)')
            ->and($raw->getBindings())
            ->toBe(['John', 'Doe']);
    });

    it('handles empty bindings array', function () {
        $raw = new Raw('NOW()');

        expect($raw->getBindings())
            ->toBeArray()
            ->and($raw->getBindings())
            ->toBeEmpty();
    });

    it('handles complex SQL expressions', function () {
        $raw = new Raw('CASE WHEN status = ? THEN ? ELSE ? END', [
            'active',
            1,
            0,
        ]);

        expect($raw->getValue())
            ->toBe('CASE WHEN status = ? THEN ? ELSE ? END')
            ->and($raw->getBindings())
            ->toBe(['active', 1, 0]);
    });

    it('preserves exact value without modification', function () {
        $value = 'SELECT * FROM users WHERE id IN (?, ?, ?)';
        $bindings = [1, 2, 3];
        $raw = new Raw($value, $bindings);

        expect($raw->getValue())
            ->toBe($value)
            ->and($raw->getBindings())
            ->toBe($bindings);
    });

    it('handles SQL functions', function () {
        $raw = new Raw('MAX(price)');

        expect($raw->getValue())->toBe('MAX(price)');
    });

    it('handles table aliases', function () {
        $raw = new Raw('u.* AS user_data');

        expect($raw->getValue())->toBe('u.* AS user_data');
    });
});

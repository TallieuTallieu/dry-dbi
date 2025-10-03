<?php

use Tnt\Dbi\IndexDefinition;

describe('IndexDefinition', function () {
    it('creates single column index definition', function () {
        $index = new IndexDefinition('email');

        expect($index->getColumns())
            ->toBe(['email'])
            ->and($index->getIdentifier())
            ->toBe('idx_email')
            ->and($index->isComposite())
            ->toBeFalse();
    });

    it('creates composite index definition from array', function () {
        $index = new IndexDefinition(['user_id', 'created']);

        expect($index->getColumns())
            ->toBe(['user_id', 'created'])
            ->and($index->getIdentifier())
            ->toBe('idx_user_id_created')
            ->and($index->isComposite())
            ->toBeTrue();
    });

    it('generates identifier with column name', function () {
        $index = new IndexDefinition('username');

        expect($index->getIdentifier())->toBe('idx_username');
    });

    it('generates identifier for composite index', function () {
        $index = new IndexDefinition(['first_name', 'last_name']);

        expect($index->getIdentifier())->toBe('idx_first_name_last_name');
    });

    it('uses custom identifier when set', function () {
        $index = new IndexDefinition('email');
        $index->identifier('custom_email_idx');

        expect($index->getIdentifier())->toBe('custom_email_idx');
    });

    it('uses custom identifier for composite index', function () {
        $index = new IndexDefinition(['user_id', 'status']);
        $index->identifier('idx_user_status');

        expect($index->getIdentifier())->toBe('idx_user_status');
    });

    it('handles column names with underscores', function () {
        $index = new IndexDefinition('user_email');

        expect($index->getColumns())
            ->toBe(['user_email'])
            ->and($index->getIdentifier())
            ->toBe('idx_user_email');
    });

    it('handles complex column names', function () {
        $index = new IndexDefinition('account_verification_token');

        expect($index->getColumns())
            ->toBe(['account_verification_token'])
            ->and($index->getIdentifier())
            ->toBe('idx_account_verification_token');
    });

    it('handles three column composite index', function () {
        $index = new IndexDefinition(['user_id', 'post_id', 'created']);

        expect($index->getColumns())
            ->toBe(['user_id', 'post_id', 'created'])
            ->and($index->getIdentifier())
            ->toBe('idx_user_id_post_id_created')
            ->and($index->isComposite())
            ->toBeTrue();
    });

    it('returns fluent interface from identifier method', function () {
        $index = new IndexDefinition('email');
        $result = $index->identifier('custom_idx');

        expect($result)->toBe($index);
    });

    it(
        'correctly identifies single column index as not composite',
        function () {
            $index = new IndexDefinition('status');

            expect($index->isComposite())->toBeFalse();
        }
    );

    it('correctly identifies two column index as composite', function () {
        $index = new IndexDefinition(['category', 'status']);

        expect($index->isComposite())->toBeTrue();
    });

    it('handles single column passed as array', function () {
        $index = new IndexDefinition(['email']);

        expect($index->getColumns())
            ->toBe(['email'])
            ->and($index->isComposite())
            ->toBeFalse();
    });
});

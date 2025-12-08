<?php

use Tnt\Dbi\UniqueDefinition;

describe('UniqueDefinition', function () {
    it('creates single column unique constraint definition', function () {
        $unique = new UniqueDefinition('email');

        expect($unique->getColumns())
            ->toBe(['email'])
            ->and($unique->getColumn())
            ->toBe('email')
            ->and($unique->getIdentifier())
            ->toBe('uq_email')
            ->and($unique->isComposite())
            ->toBeFalse();
    });

    it(
        'creates composite unique constraint definition from array',
        function () {
            $unique = new UniqueDefinition(['user_id', 'email']);

            expect($unique->getColumns())
                ->toBe(['user_id', 'email'])
                ->and($unique->getIdentifier())
                ->toBe('uq_user_id_email')
                ->and($unique->isComposite())
                ->toBeTrue();
        }
    );

    it('generates identifier with column name', function () {
        $unique = new UniqueDefinition('username');

        expect($unique->getIdentifier())->toBe('uq_username');
    });

    it('generates identifier for composite unique constraint', function () {
        $unique = new UniqueDefinition(['first_name', 'last_name']);

        expect($unique->getIdentifier())->toBe('uq_first_name_last_name');
    });

    it('uses custom identifier when set', function () {
        $unique = new UniqueDefinition('email');
        $unique->identifier('custom_email_uq');

        expect($unique->getIdentifier())->toBe('custom_email_uq');
    });

    it('uses custom identifier for composite unique constraint', function () {
        $unique = new UniqueDefinition(['user_id', 'status']);
        $unique->identifier('uq_user_status');

        expect($unique->getIdentifier())->toBe('uq_user_status');
    });

    it('handles column names with underscores', function () {
        $unique = new UniqueDefinition('user_email');

        expect($unique->getColumns())
            ->toBe(['user_email'])
            ->and($unique->getColumn())
            ->toBe('user_email')
            ->and($unique->getIdentifier())
            ->toBe('uq_user_email');
    });

    it('handles complex column names', function () {
        $unique = new UniqueDefinition('account_verification_token');

        expect($unique->getColumns())
            ->toBe(['account_verification_token'])
            ->and($unique->getColumn())
            ->toBe('account_verification_token')
            ->and($unique->getIdentifier())
            ->toBe('uq_account_verification_token');
    });

    it('handles three column composite unique constraint', function () {
        $unique = new UniqueDefinition(['user_id', 'post_id', 'created']);

        expect($unique->getColumns())
            ->toBe(['user_id', 'post_id', 'created'])
            ->and($unique->getIdentifier())
            ->toBe('uq_user_id_post_id_created')
            ->and($unique->isComposite())
            ->toBeTrue();
    });

    it('returns fluent interface from identifier method', function () {
        $unique = new UniqueDefinition('email');
        $result = $unique->identifier('custom_uq');

        expect($result)->toBe($unique);
    });

    it(
        'correctly identifies single column unique as not composite',
        function () {
            $unique = new UniqueDefinition('status');

            expect($unique->isComposite())->toBeFalse();
        }
    );

    it('correctly identifies two column unique as composite', function () {
        $unique = new UniqueDefinition(['category', 'status']);

        expect($unique->isComposite())->toBeTrue();
    });

    it('handles single column passed as array', function () {
        $unique = new UniqueDefinition(['email']);

        expect($unique->getColumns())
            ->toBe(['email'])
            ->and($unique->isComposite())
            ->toBeFalse();
    });

    it(
        'getColumn returns first column for backwards compatibility',
        function () {
            $unique = new UniqueDefinition(['user_id', 'email']);

            expect($unique->getColumn())->toBe('user_id');
        }
    );
});

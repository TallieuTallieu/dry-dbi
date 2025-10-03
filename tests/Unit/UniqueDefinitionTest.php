<?php

use Tnt\Dbi\UniqueDefinition;

describe('UniqueDefinition', function () {
    it('creates unique constraint definition', function () {
        $unique = new UniqueDefinition('email');

        expect($unique->getColumn())
            ->toBe('email')
            ->and($unique->getIdentifier())
            ->toBe('uq_email');
    });

    it('generates identifier with column name', function () {
        $unique = new UniqueDefinition('username');

        expect($unique->getIdentifier())->toBe('uq_username');
    });

    it('handles column names with underscores', function () {
        $unique = new UniqueDefinition('user_email');

        expect($unique->getColumn())
            ->toBe('user_email')
            ->and($unique->getIdentifier())
            ->toBe('uq_user_email');
    });

    it('handles complex column names', function () {
        $unique = new UniqueDefinition('account_verification_token');

        expect($unique->getColumn())
            ->toBe('account_verification_token')
            ->and($unique->getIdentifier())
            ->toBe('uq_account_verification_token');
    });
});

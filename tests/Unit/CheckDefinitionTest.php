<?php

use Tnt\Dbi\CheckDefinition;

describe('CheckDefinition', function () {
    // Basic construction tests
    it('creates check constraint with column and expression', function () {
        $check = new CheckDefinition('status', '`status` IN (0, 1, 2, 3)');

        expect($check->getColumn())
            ->toBe('status')
            ->and($check->getExpression())
            ->toBe('`status` IN (0, 1, 2, 3)')
            ->and($check->getIdentifier())
            ->toBe('chk_status');
    });

    it('stores column name correctly', function () {
        $check = new CheckDefinition('age', '`age` >= 0');

        expect($check->getColumn())->toBe('age');
    });

    it('stores expression correctly', function () {
        $check = new CheckDefinition('price', '`price` > 0');

        expect($check->getExpression())->toBe('`price` > 0');
    });

    // Identifier generation tests
    it('generates identifier with chk_ prefix', function () {
        $check = new CheckDefinition('status', '`status` IN (0, 1)');

        expect($check->getIdentifier())->toBe('chk_status');
    });

    it('generates identifier based on column name', function () {
        $check = new CheckDefinition('age', '`age` >= 0');

        expect($check->getIdentifier())->toBe('chk_age');
    });

    it('uses custom identifier when set', function () {
        $check = new CheckDefinition('status', '`status` IN (0, 1, 2, 3)');
        $check->identifier('chk_subscriber_status');

        expect($check->getIdentifier())->toBe('chk_subscriber_status');
    });

    it('custom identifier overrides generated identifier', function () {
        $check = new CheckDefinition('email', '`email` LIKE \'%@%\'');
        $check->identifier('chk_email_format');

        expect($check->getIdentifier())->toBe('chk_email_format');
    });

    it('returns fluent interface from identifier method', function () {
        $check = new CheckDefinition('status', '`status` IN (0, 1, 2, 3)');
        $result = $check->identifier('custom_chk');

        expect($result)->toBe($check);
    });

    it('allows chaining identifier method', function () {
        $check = new CheckDefinition('status', '`status` IN (0, 1)');

        $result = $check->identifier('chk_custom')->getIdentifier();

        expect($result)->toBe('chk_custom');
    });

    // Column name edge cases
    it('handles column names with underscores', function () {
        $check = new CheckDefinition(
            'user_status',
            '`user_status` IN (\'active\', \'inactive\')'
        );

        expect($check->getColumn())
            ->toBe('user_status')
            ->and($check->getIdentifier())
            ->toBe('chk_user_status');
    });

    it('handles complex column names with multiple underscores', function () {
        $check = new CheckDefinition(
            'account_verification_status',
            '`account_verification_status` IN (0, 1, 2)'
        );

        expect($check->getColumn())
            ->toBe('account_verification_status')
            ->and($check->getIdentifier())
            ->toBe('chk_account_verification_status');
    });

    it('handles short column names', function () {
        $check = new CheckDefinition('id', '`id` > 0');

        expect($check->getColumn())
            ->toBe('id')
            ->and($check->getIdentifier())
            ->toBe('chk_id');
    });

    it('handles single character column names', function () {
        $check = new CheckDefinition('x', '`x` >= 0');

        expect($check->getColumn())
            ->toBe('x')
            ->and($check->getIdentifier())
            ->toBe('chk_x');
    });

    // Expression type tests - IN expressions
    it('handles IN expression with integers', function () {
        $check = new CheckDefinition('status', '`status` IN (0, 1, 2, 3)');

        expect($check->getExpression())->toBe('`status` IN (0, 1, 2, 3)');
    });

    it('handles IN expression with strings', function () {
        $check = new CheckDefinition(
            'status',
            '`status` IN (\'active\', \'inactive\', \'pending\')'
        );

        expect($check->getExpression())->toBe(
            '`status` IN (\'active\', \'inactive\', \'pending\')'
        );
    });

    it('handles NOT IN expression', function () {
        $check = new CheckDefinition(
            'status',
            '`status` NOT IN (\'deleted\', \'banned\')'
        );

        expect($check->getExpression())->toBe(
            '`status` NOT IN (\'deleted\', \'banned\')'
        );
    });

    // Expression type tests - Comparison operators
    it('handles greater than expression', function () {
        $check = new CheckDefinition('price', '`price` > 0');

        expect($check->getExpression())->toBe('`price` > 0');
    });

    it('handles greater than or equal expression', function () {
        $check = new CheckDefinition('age', '`age` >= 0');

        expect($check->getExpression())->toBe('`age` >= 0');
    });

    it('handles less than expression', function () {
        $check = new CheckDefinition('discount', '`discount` < 100');

        expect($check->getExpression())->toBe('`discount` < 100');
    });

    it('handles less than or equal expression', function () {
        $check = new CheckDefinition('quantity', '`quantity` <= 1000');

        expect($check->getExpression())->toBe('`quantity` <= 1000');
    });

    it('handles not equal expression', function () {
        $check = new CheckDefinition('status', '`status` <> 0');

        expect($check->getExpression())->toBe('`status` <> 0');
    });

    it('handles != not equal expression', function () {
        $check = new CheckDefinition('status', '`status` != 0');

        expect($check->getExpression())->toBe('`status` != 0');
    });

    // Expression type tests - Range expressions
    it('handles BETWEEN expression', function () {
        $check = new CheckDefinition('age', '`age` BETWEEN 0 AND 150');

        expect($check->getExpression())->toBe('`age` BETWEEN 0 AND 150');
    });

    it('handles range with AND expression', function () {
        $check = new CheckDefinition('age', '`age` >= 0 AND `age` <= 150');

        expect($check->getExpression())->toBe('`age` >= 0 AND `age` <= 150');
    });

    it('handles complex range expression', function () {
        $check = new CheckDefinition(
            'discount',
            '`discount` >= 0 AND `discount` <= 100'
        );

        expect($check->getExpression())->toBe(
            '`discount` >= 0 AND `discount` <= 100'
        );
    });

    // Expression type tests - REGEXP expressions
    it('handles simple REGEXP expression', function () {
        $check = new CheckDefinition('email', '`email` REGEXP \'^.+@.+$\'');

        expect($check->getExpression())->toBe('`email` REGEXP \'^.+@.+$\'');
    });

    it('handles complex REGEXP expression for email validation', function () {
        $check = new CheckDefinition(
            'email',
            '`email` REGEXP \'^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\\.[A-Za-z]{2,}$\''
        );

        expect($check->getColumn())
            ->toBe('email')
            ->and($check->getExpression())
            ->toBe(
                '`email` REGEXP \'^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\\.[A-Za-z]{2,}$\''
            )
            ->and($check->getIdentifier())
            ->toBe('chk_email');
    });

    it('handles REGEXP expression for phone number', function () {
        $check = new CheckDefinition(
            'phone',
            '`phone` REGEXP \'^\\+?[0-9]{10,15}$\''
        );

        expect($check->getExpression())->toBe(
            '`phone` REGEXP \'^\\+?[0-9]{10,15}$\''
        );
    });

    it('handles RLIKE expression (MySQL alias for REGEXP)', function () {
        $check = new CheckDefinition(
            'code',
            '`code` RLIKE \'^[A-Z]{3}[0-9]{3}$\''
        );

        expect($check->getExpression())->toBe(
            '`code` RLIKE \'^[A-Z]{3}[0-9]{3}$\''
        );
    });

    // Expression type tests - LIKE expressions
    it('handles LIKE expression', function () {
        $check = new CheckDefinition('email', '`email` LIKE \'%@%\'');

        expect($check->getExpression())->toBe('`email` LIKE \'%@%\'');
    });

    it('handles NOT LIKE expression', function () {
        $check = new CheckDefinition('name', '`name` NOT LIKE \'%test%\'');

        expect($check->getExpression())->toBe('`name` NOT LIKE \'%test%\'');
    });

    // Expression type tests - Boolean expressions
    it('handles boolean-like check with 0 and 1', function () {
        $check = new CheckDefinition('is_active', '`is_active` IN (0, 1)');

        expect($check->getColumn())
            ->toBe('is_active')
            ->and($check->getExpression())
            ->toBe('`is_active` IN (0, 1)')
            ->and($check->getIdentifier())
            ->toBe('chk_is_active');
    });

    it('handles boolean-like check with TRUE and FALSE', function () {
        $check = new CheckDefinition('enabled', '`enabled` IN (TRUE, FALSE)');

        expect($check->getExpression())->toBe('`enabled` IN (TRUE, FALSE)');
    });

    // Expression type tests - Complex/compound expressions
    it('handles OR expression', function () {
        $check = new CheckDefinition(
            'status',
            '`status` = \'active\' OR `status` = \'pending\''
        );

        expect($check->getExpression())->toBe(
            '`status` = \'active\' OR `status` = \'pending\''
        );
    });

    it('handles complex expression with multiple conditions', function () {
        $check = new CheckDefinition(
            'price',
            '(`price` > 0 AND `price` < 10000) OR `price` IS NULL'
        );

        expect($check->getExpression())->toBe(
            '(`price` > 0 AND `price` < 10000) OR `price` IS NULL'
        );
    });

    it('handles expression with parentheses', function () {
        $check = new CheckDefinition(
            'value',
            '(`value` >= 0 AND `value` <= 100)'
        );

        expect($check->getExpression())->toBe(
            '(`value` >= 0 AND `value` <= 100)'
        );
    });

    it('handles expression referencing multiple columns', function () {
        $check = new CheckDefinition('end_date', '`end_date` > `start_date`');

        expect($check->getColumn())
            ->toBe('end_date')
            ->and($check->getExpression())
            ->toBe('`end_date` > `start_date`');
    });

    it('handles expression with date comparison', function () {
        $check = new CheckDefinition(
            'birth_date',
            '`birth_date` <= CURRENT_DATE'
        );

        expect($check->getExpression())->toBe('`birth_date` <= CURRENT_DATE');
    });

    // Expression type tests - NULL checks
    it('handles IS NOT NULL expression', function () {
        $check = new CheckDefinition('email', '`email` IS NOT NULL');

        expect($check->getExpression())->toBe('`email` IS NOT NULL');
    });

    it('handles expression with COALESCE', function () {
        $check = new CheckDefinition('value', 'COALESCE(`value`, 0) >= 0');

        expect($check->getExpression())->toBe('COALESCE(`value`, 0) >= 0');
    });

    // Expression type tests - String length checks
    it('handles LENGTH expression', function () {
        $check = new CheckDefinition('password', 'LENGTH(`password`) >= 8');

        expect($check->getExpression())->toBe('LENGTH(`password`) >= 8');
    });

    it('handles CHAR_LENGTH expression', function () {
        $check = new CheckDefinition(
            'username',
            'CHAR_LENGTH(`username`) BETWEEN 3 AND 50'
        );

        expect($check->getExpression())->toBe(
            'CHAR_LENGTH(`username`) BETWEEN 3 AND 50'
        );
    });

    // Edge cases - Empty and special values
    it('handles empty expression string', function () {
        $check = new CheckDefinition('column', '');

        expect($check->getExpression())->toBe('');
    });

    it('handles expression with special characters', function () {
        $check = new CheckDefinition('data', '`data` NOT LIKE \'%<script>%\'');

        expect($check->getExpression())->toBe('`data` NOT LIKE \'%<script>%\'');
    });

    it('handles expression with escaped quotes', function () {
        $check = new CheckDefinition('name', '`name` NOT LIKE \'%\\\'%\'');

        expect($check->getExpression())->toBe('`name` NOT LIKE \'%\\\'%\'');
    });

    // Numeric precision tests
    it('handles decimal comparison', function () {
        $check = new CheckDefinition(
            'rate',
            '`rate` >= 0.00 AND `rate` <= 1.00'
        );

        expect($check->getExpression())->toBe(
            '`rate` >= 0.00 AND `rate` <= 1.00'
        );
    });

    it('handles negative number comparison', function () {
        $check = new CheckDefinition('temperature', '`temperature` >= -273.15');

        expect($check->getExpression())->toBe('`temperature` >= -273.15');
    });

    // Custom identifier edge cases
    it('handles custom identifier with special naming', function () {
        $check = new CheckDefinition('status', '`status` IN (0, 1)');
        $check->identifier('chk_subscriber_status_valid_values');

        expect($check->getIdentifier())->toBe(
            'chk_subscriber_status_valid_values'
        );
    });

    it('handles custom identifier without chk_ prefix', function () {
        $check = new CheckDefinition('status', '`status` IN (0, 1)');
        $check->identifier('status_constraint');

        expect($check->getIdentifier())->toBe('status_constraint');
    });

    it('handles custom identifier with numbers', function () {
        $check = new CheckDefinition('status', '`status` IN (0, 1)');
        $check->identifier('chk_status_v2');

        expect($check->getIdentifier())->toBe('chk_status_v2');
    });

    // Immutability tests
    it('does not modify original expression', function () {
        $expression = '`status` IN (0, 1, 2)';
        $check = new CheckDefinition('status', $expression);

        expect($check->getExpression())->toBe($expression);
    });

    it('does not modify original column name', function () {
        $column = 'user_status';
        $check = new CheckDefinition($column, '`user_status` IN (0, 1)');

        expect($check->getColumn())->toBe($column);
    });

    // Multiple calls tests
    it('allows multiple calls to getIdentifier', function () {
        $check = new CheckDefinition('status', '`status` IN (0, 1)');

        $id1 = $check->getIdentifier();
        $id2 = $check->getIdentifier();

        expect($id1)->toBe($id2)->toBe('chk_status');
    });

    it('allows multiple calls to getColumn', function () {
        $check = new CheckDefinition('status', '`status` IN (0, 1)');

        $col1 = $check->getColumn();
        $col2 = $check->getColumn();

        expect($col1)->toBe($col2)->toBe('status');
    });

    it('allows multiple calls to getExpression', function () {
        $check = new CheckDefinition('status', '`status` IN (0, 1)');

        $expr1 = $check->getExpression();
        $expr2 = $check->getExpression();

        expect($expr1)->toBe($expr2)->toBe('`status` IN (0, 1)');
    });

    it('allows overwriting custom identifier', function () {
        $check = new CheckDefinition('status', '`status` IN (0, 1)');
        $check->identifier('first_identifier');
        $check->identifier('second_identifier');

        expect($check->getIdentifier())->toBe('second_identifier');
    });
});

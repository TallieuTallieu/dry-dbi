<?php

use Tnt\Dbi\Criteria\Equals;
use Tnt\Dbi\Criteria\NotEquals;
use Tnt\Dbi\Criteria\GreaterThan;
use Tnt\Dbi\Criteria\GreaterThanOrEqual;
use Tnt\Dbi\Criteria\LessThan;
use Tnt\Dbi\Criteria\LessThanOrEqual;
use Tnt\Dbi\Criteria\In;
use Tnt\Dbi\Criteria\IsNull;
use Tnt\Dbi\Criteria\NotNull;
use Tnt\Dbi\Criteria\IsTrue;
use Tnt\Dbi\Criteria\IsFalse;
use Tnt\Dbi\Criteria\OrEquals;
use Tnt\Dbi\Criteria\OrderBy;
use Tnt\Dbi\Criteria\GroupBy;
use Tnt\Dbi\Criteria\LimitOffset;
use Tnt\Dbi\Contracts\CriteriaInterface;
use Tnt\Dbi\QueryBuilder;
use Tnt\Dbi\Raw;

describe('Equals Criteria', function () {
    it('applies equals condition with table prefix', function () {
        $criteria = new Equals('status', 'active');
        $queryBuilder = new QueryBuilder();
        $queryBuilder->table('users');
        $queryBuilder->selectAll();

        $criteria->apply($queryBuilder);
        $queryBuilder->build();

        $query = $queryBuilder->getQuery();
        expect($query)->toContain('WHERE `users`.`status` = ?');
        expect($queryBuilder->getParameters())->toBe(['active']);
    });

    it('handles numeric values', function () {
        $criteria = new Equals('id', 123);
        $queryBuilder = new QueryBuilder();
        $queryBuilder->table('users');
        $queryBuilder->selectAll();

        $criteria->apply($queryBuilder);
        $queryBuilder->build();

        expect($queryBuilder->getQuery())->toContain('WHERE `users`.`id` = ?');
        expect($queryBuilder->getParameters())->toBe([123]);
    });
});

describe('NotEquals Criteria', function () {
    it('applies not equals condition', function () {
        $criteria = new NotEquals('status', 'deleted');
        $queryBuilder = new QueryBuilder();
        $queryBuilder->table('users');
        $queryBuilder->selectAll();

        $criteria->apply($queryBuilder);
        $queryBuilder->build();

        expect($queryBuilder->getQuery())->toContain(
            'WHERE `users`.`status` != ?'
        );
        expect($queryBuilder->getParameters())->toBe(['deleted']);
    });
});

describe('GreaterThan Criteria', function () {
    it('applies greater than condition', function () {
        $criteria = new GreaterThan('age', 18);
        $queryBuilder = new QueryBuilder();
        $queryBuilder->table('users');
        $queryBuilder->selectAll();

        $criteria->apply($queryBuilder);
        $queryBuilder->build();

        expect($queryBuilder->getQuery())->toContain('WHERE `users`.`age` > ?');
        expect($queryBuilder->getParameters())->toBe([18]);
    });
});

describe('GreaterThanOrEqual Criteria', function () {
    it('applies greater than or equal condition', function () {
        $criteria = new GreaterThanOrEqual('price', 100);
        $queryBuilder = new QueryBuilder();
        $queryBuilder->table('products');
        $queryBuilder->selectAll();

        $criteria->apply($queryBuilder);
        $queryBuilder->build();

        expect($queryBuilder->getQuery())->toContain(
            'WHERE `products`.`price` >= ?'
        );
        expect($queryBuilder->getParameters())->toBe([100]);
    });
});

describe('LessThan Criteria', function () {
    it('applies less than condition', function () {
        $criteria = new LessThan('stock', 10);
        $queryBuilder = new QueryBuilder();
        $queryBuilder->table('products');
        $queryBuilder->selectAll();

        $criteria->apply($queryBuilder);
        $queryBuilder->build();

        expect($queryBuilder->getQuery())->toContain(
            'WHERE `products`.`stock` < ?'
        );
        expect($queryBuilder->getParameters())->toBe([10]);
    });
});

describe('LessThanOrEqual Criteria', function () {
    it('applies less than or equal condition', function () {
        $criteria = new LessThanOrEqual('age', 65);
        $queryBuilder = new QueryBuilder();
        $queryBuilder->table('users');
        $queryBuilder->selectAll();

        $criteria->apply($queryBuilder);
        $queryBuilder->build();

        expect($queryBuilder->getQuery())->toContain(
            'WHERE `users`.`age` <= ?'
        );
        expect($queryBuilder->getParameters())->toBe([65]);
    });
});

describe('In Criteria', function () {
    it('applies IN condition with array', function () {
        $criteria = new In('status', ['active', 'pending', 'approved']);
        $queryBuilder = new QueryBuilder();
        $queryBuilder->table('orders');
        $queryBuilder->selectAll();

        $criteria->apply($queryBuilder);
        $queryBuilder->build();

        expect($queryBuilder->getQuery())->toContain(
            'WHERE `orders`.`status` IN (?,?,?)'
        );
        expect($queryBuilder->getParameters())->toBe([
            'active',
            'pending',
            'approved',
        ]);
    });

    it('handles numeric array', function () {
        $criteria = new In('id', [1, 2, 3, 4, 5]);
        $queryBuilder = new QueryBuilder();
        $queryBuilder->table('users');
        $queryBuilder->selectAll();

        $criteria->apply($queryBuilder);
        $queryBuilder->build();

        expect($queryBuilder->getQuery())->toContain(
            'WHERE `users`.`id` IN (?,?,?,?,?)'
        );
        expect($queryBuilder->getParameters())->toBe([1, 2, 3, 4, 5]);
    });

    it('produces an always false condition for an empty array', function () {
        $criteria = new In('id', []);
        $queryBuilder = new QueryBuilder();
        $queryBuilder->table('users');
        $queryBuilder->selectAll();

        $criteria->apply($queryBuilder);
        $queryBuilder->build();

        expect($queryBuilder->getQuery())->toContain('WHERE 1 = 0');
        expect($queryBuilder->getParameters())->toBe([]);
    });
});

describe('IsNull Criteria', function () {
    it('applies IS NULL condition', function () {
        $criteria = new IsNull('deleted_at');
        $queryBuilder = new QueryBuilder();
        $queryBuilder->table('users');
        $queryBuilder->selectAll();

        $criteria->apply($queryBuilder);
        $queryBuilder->build();

        expect($queryBuilder->getQuery())->toContain(
            'WHERE `users`.`deleted_at` IS NULL'
        );
    });
});

describe('NotNull Criteria', function () {
    it('applies IS NOT NULL condition', function () {
        $criteria = new NotNull('email');
        $queryBuilder = new QueryBuilder();
        $queryBuilder->table('users');
        $queryBuilder->selectAll();

        $criteria->apply($queryBuilder);
        $queryBuilder->build();

        expect($queryBuilder->getQuery())->toContain(
            'WHERE `users`.`email` IS NOT NULL'
        );
    });
});

describe('IsTrue Criteria', function () {
    it('applies IS TRUE condition', function () {
        $criteria = new IsTrue('is_active');
        $queryBuilder = new QueryBuilder();
        $queryBuilder->table('users');
        $queryBuilder->selectAll();

        $criteria->apply($queryBuilder);
        $queryBuilder->build();

        expect($queryBuilder->getQuery())->toContain(
            'WHERE `users`.`is_active` IS TRUE'
        );
    });
});

describe('IsFalse Criteria', function () {
    it('applies IS FALSE condition', function () {
        $criteria = new IsFalse('is_verified');
        $queryBuilder = new QueryBuilder();
        $queryBuilder->table('users');
        $queryBuilder->selectAll();

        $criteria->apply($queryBuilder);
        $queryBuilder->build();

        expect($queryBuilder->getQuery())->toContain(
            'WHERE `users`.`is_verified` IS FALSE'
        );
    });
});

describe('OrEquals Criteria', function () {
    it('applies OR equals conditions', function () {
        $criteria = new OrEquals([['role', 'admin'], ['role', 'moderator']]);
        $queryBuilder = new QueryBuilder();
        $queryBuilder->table('users');
        $queryBuilder->selectAll();

        $criteria->apply($queryBuilder);
        $queryBuilder->build();

        $query = $queryBuilder->getQuery();
        expect($query)->toContain(
            'WHERE  ( `users`.`role` = ? OR `users`.`role` = ? )'
        );
        expect($queryBuilder->getParameters())->toBe(['admin', 'moderator']);
    });

    it('combines with other WHERE conditions', function () {
        $criteria = new OrEquals([['status', 'active'], ['status', 'pending']]);
        $queryBuilder = new QueryBuilder();
        $queryBuilder->table('orders');
        $queryBuilder->selectAll();
        $queryBuilder->where('user_id', '=', 123);

        $criteria->apply($queryBuilder);
        $queryBuilder->build();

        $query = $queryBuilder->getQuery();
        expect($query)->toContain('WHERE `orders`.`user_id` = ?');
        expect($query)->toContain(
            'AND  ( `orders`.`status` = ? OR `orders`.`status` = ? )'
        );
        expect($queryBuilder->getParameters())->toBe([
            123,
            'active',
            'pending',
        ]);
    });
});

describe('OrderBy Criteria', function () {
    it('applies ORDER BY ascending', function () {
        $criteria = new OrderBy('name', 'ASC');
        $queryBuilder = new QueryBuilder();
        $queryBuilder->table('users');
        $queryBuilder->selectAll();

        $criteria->apply($queryBuilder);
        $queryBuilder->build();

        expect($queryBuilder->getQuery())->toContain(
            'ORDER BY `users`.`name` ASC'
        );
    });

    it('applies ORDER BY descending', function () {
        $criteria = new OrderBy('created_at', 'DESC');
        $queryBuilder = new QueryBuilder();
        $queryBuilder->table('posts');
        $queryBuilder->selectAll();

        $criteria->apply($queryBuilder);
        $queryBuilder->build();

        expect($queryBuilder->getQuery())->toContain(
            'ORDER BY `posts`.`created_at` DESC'
        );
    });
});

describe('GroupBy Criteria', function () {
    it('applies GROUP BY', function () {
        $criteria = new GroupBy('category');
        $queryBuilder = new QueryBuilder();
        $queryBuilder->table('products');
        $queryBuilder->selectAll();

        $criteria->apply($queryBuilder);
        $queryBuilder->build();

        expect($queryBuilder->getQuery())->toContain(
            'GROUP BY `products`.`category`'
        );
    });
});

describe('Raw Column Criteria', function () {
    it('applies an unquoted raw column with bindings', function (
        CriteriaInterface $criteria,
        string $expectedQuery,
        array $expectedParameters
    ) {
        $queryBuilder = new QueryBuilder();
        $queryBuilder->table('records');
        $queryBuilder->selectAll();

        $criteria->apply($queryBuilder);
        $queryBuilder->build();

        expect($queryBuilder->getQuery())->toContain($expectedQuery);
        expect($queryBuilder->getParameters())->toBe($expectedParameters);
    })->with([
        'Equals' => [
            new Equals(new Raw('JSON_EXTRACT(`metadata`, ?)', ['$.score']), 10),
            'WHERE JSON_EXTRACT(`metadata`, ?) = ?',
            ['$.score', 10],
        ],
        'NotEquals' => [
            new NotEquals(
                new Raw('JSON_EXTRACT(`metadata`, ?)', ['$.score']),
                10
            ),
            'WHERE JSON_EXTRACT(`metadata`, ?) != ?',
            ['$.score', 10],
        ],
        'GreaterThan' => [
            new GreaterThan(
                new Raw('JSON_EXTRACT(`metadata`, ?)', ['$.score']),
                10
            ),
            'WHERE JSON_EXTRACT(`metadata`, ?) > ?',
            ['$.score', 10],
        ],
        'GreaterThanOrEqual' => [
            new GreaterThanOrEqual(
                new Raw('JSON_EXTRACT(`metadata`, ?)', ['$.score']),
                10
            ),
            'WHERE JSON_EXTRACT(`metadata`, ?) >= ?',
            ['$.score', 10],
        ],
        'LessThan' => [
            new LessThan(
                new Raw('JSON_EXTRACT(`metadata`, ?)', ['$.score']),
                10
            ),
            'WHERE JSON_EXTRACT(`metadata`, ?) < ?',
            ['$.score', 10],
        ],
        'LessThanOrEqual' => [
            new LessThanOrEqual(
                new Raw('JSON_EXTRACT(`metadata`, ?)', ['$.score']),
                10
            ),
            'WHERE JSON_EXTRACT(`metadata`, ?) <= ?',
            ['$.score', 10],
        ],
        'In' => [
            new In(new Raw('JSON_EXTRACT(`metadata`, ?)', ['$.score']), [
                10,
                20,
            ]),
            'WHERE JSON_EXTRACT(`metadata`, ?) IN (?,?)',
            ['$.score', 10, 20],
        ],
        'IsNull' => [
            new IsNull(new Raw('JSON_EXTRACT(`metadata`, ?)', ['$.score'])),
            'WHERE JSON_EXTRACT(`metadata`, ?) IS NULL',
            ['$.score'],
        ],
        'NotNull' => [
            new NotNull(new Raw('JSON_EXTRACT(`metadata`, ?)', ['$.score'])),
            'WHERE JSON_EXTRACT(`metadata`, ?) IS NOT NULL',
            ['$.score'],
        ],
        'IsTrue' => [
            new IsTrue(new Raw('JSON_EXTRACT(`metadata`, ?)', ['$.enabled'])),
            'WHERE JSON_EXTRACT(`metadata`, ?) IS TRUE',
            ['$.enabled'],
        ],
        'IsFalse' => [
            new IsFalse(new Raw('JSON_EXTRACT(`metadata`, ?)', ['$.enabled'])),
            'WHERE JSON_EXTRACT(`metadata`, ?) IS FALSE',
            ['$.enabled'],
        ],
        'OrderBy' => [
            new OrderBy(
                new Raw('FIELD(`status`, ?, ?)', ['active', 'pending'])
            ),
            'ORDER BY FIELD(`status`, ?, ?) ASC',
            ['active', 'pending'],
        ],
        'GroupBy' => [
            new GroupBy(new Raw('DATE_FORMAT(`created_at`, ?)', ['%Y-%m'])),
            'GROUP BY DATE_FORMAT(`created_at`, ?)',
            ['%Y-%m'],
        ],
    ]);
});

describe('LimitOffset Criteria', function () {
    it('applies LIMIT', function () {
        $criteria = new LimitOffset(10);
        $queryBuilder = new QueryBuilder();
        $queryBuilder->table('users');
        $queryBuilder->selectAll();

        $criteria->apply($queryBuilder);
        $queryBuilder->build();

        expect($queryBuilder->getQuery())->toContain('LIMIT ?');
        expect($queryBuilder->getParameters())->toBe([10]);
    });

    it('applies LIMIT with OFFSET', function () {
        $criteria = new LimitOffset(10, 20);
        $queryBuilder = new QueryBuilder();
        $queryBuilder->table('users');
        $queryBuilder->selectAll();

        $criteria->apply($queryBuilder);
        $queryBuilder->build();

        $query = $queryBuilder->getQuery();
        expect($query)->toContain('LIMIT ?');
        expect($query)->toContain('OFFSET ?');
        expect($queryBuilder->getParameters())->toBe([10, 20]);
    });
});

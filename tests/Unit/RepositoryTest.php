<?php

use Tnt\Dbi\Repository;
use Tnt\Dbi\BaseRepository;
use Tnt\Dbi\CriteriaCollection;
use Tnt\Dbi\Criteria\Equals;
use Tnt\Dbi\Criteria\GreaterThan;
use Tnt\Dbi\Criteria\OrderBy;
use Tnt\Dbi\Criteria\LimitOffset;
use Tnt\Dbi\QueryBuilder;

// Mock Model class for testing
class MockModel {
    const TABLE = 'users';
    
    public static $lastQuery = null;
    public static $lastParams = [];
    public static $queryResult = [];
    public static $queryRowResult = null;
    
    public static function query($query, ...$params) {
        self::$lastQuery = $query;
        self::$lastParams = $params;
        return self::$queryResult;
    }
    
    public static function query_row($query, ...$params) {
        self::$lastQuery = $query;
        self::$lastParams = $params;
        return self::$queryRowResult;
    }
    
    public static function reset() {
        self::$lastQuery = null;
        self::$lastParams = [];
        self::$queryResult = [];
        self::$queryRowResult = null;
    }
}

// Concrete Repository implementation for testing
class TestRepository extends Repository {
    protected $model = MockModel::class;
}

// Concrete BaseRepository implementation for testing
class TestBaseRepository extends BaseRepository {
    protected $model = MockModel::class;
}

describe('Repository', function () {
    
    beforeEach(function () {
        MockModel::reset();
    });

    it('creates repository with criteria collection', function () {
        $collection = new CriteriaCollection();
        $repo = new TestRepository($collection);
        
        expect($repo)->toBeInstanceOf(Repository::class);
    });

    it('creates repository using static create method', function () {
        $repo = TestRepository::create();
        
        expect($repo)->toBeInstanceOf(Repository::class);
    });

    it('executes get query with selectAll', function () {
        MockModel::$queryResult = [['id' => 1], ['id' => 2]];
        
        $repo = TestRepository::create();
        $result = $repo->get();
        
        expect(MockModel::$lastQuery)->toContain('SELECT `users`.* FROM `users`')
            ->and($result)->toBe([['id' => 1], ['id' => 2]]);
    });

    it('executes first query with limit 1', function () {
        MockModel::$queryRowResult = ['id' => 1, 'name' => 'John'];
        
        $repo = TestRepository::create();
        $result = $repo->first();
        
        expect(MockModel::$lastQuery)->toContain('SELECT `users`.* FROM `users`')
            ->and(MockModel::$lastQuery)->toContain('LIMIT ?')
            ->and(MockModel::$lastParams)->toBe([1])
            ->and($result)->toBe(['id' => 1, 'name' => 'John']);
    });

    it('applies criteria to query', function () {
        $collection = new CriteriaCollection();
        $collection->addCriteria(new Equals('status', 'active'));
        
        $repo = new TestRepository($collection);
        $repo->get();
        
        expect(MockModel::$lastQuery)->toContain('WHERE `users`.`status` = ?')
            ->and(MockModel::$lastParams)->toBe(['active']);
    });

    it('applies multiple criteria', function () {
        $collection = new CriteriaCollection();
        $collection->addCriteria(new Equals('status', 'active'));
        $collection->addCriteria(new GreaterThan('age', 18));
        
        $repo = new TestRepository($collection);
        $repo->get();
        
        expect(MockModel::$lastQuery)->toContain('WHERE `users`.`status` = ?')
            ->and(MockModel::$lastQuery)->toContain('AND `users`.`age` > ?')
            ->and(MockModel::$lastParams)->toBe(['active', 18]);
    });

    it('applies order by criteria', function () {
        $collection = new CriteriaCollection();
        $collection->addCriteria(new OrderBy('name', 'ASC'));
        
        $repo = new TestRepository($collection);
        $repo->get();
        
        expect(MockModel::$lastQuery)->toContain('ORDER BY `users`.`name` ASC');
    });

    it('applies limit offset criteria', function () {
        $collection = new CriteriaCollection();
        $collection->addCriteria(new LimitOffset(10, 20));
        
        $repo = new TestRepository($collection);
        $repo->get();
        
        expect(MockModel::$lastQuery)->toContain('LIMIT ?')
            ->and(MockModel::$lastQuery)->toContain('OFFSET ?')
            ->and(MockModel::$lastParams)->toBe([10, 20]);
    });

    it('uses query builder directly', function () {
        $repo = TestRepository::create();
        
        // Use reflection to access protected method
        $reflection = new ReflectionClass($repo);
        $method = $reflection->getMethod('useQueryBuilder');
        $method->setAccessible(true);
        
        $method->invoke($repo, function($qb) {
            $qb->where('custom_field', '=', 'custom_value');
        });
        
        $repo->get();
        
        expect(MockModel::$lastQuery)->toContain('WHERE `users`.`custom_field` = ?')
            ->and(MockModel::$lastParams)->toBe(['custom_value']);
    });

    it('combines criteria and query builder uses', function () {
        $collection = new CriteriaCollection();
        $collection->addCriteria(new Equals('status', 'active'));
        
        $repo = new TestRepository($collection);
        
        $reflection = new ReflectionClass($repo);
        $method = $reflection->getMethod('useQueryBuilder');
        $method->setAccessible(true);
        
        $method->invoke($repo, function($qb) {
            $qb->where('type', '=', 'premium');
        });
        
        $repo->get();
        
        expect(MockModel::$lastQuery)->toContain('WHERE `users`.`status` = ?')
            ->and(MockModel::$lastQuery)->toContain('AND `users`.`type` = ?')
            ->and(MockModel::$lastParams)->toBe(['active', 'premium']);
    });

});

describe('BaseRepository', function () {
    
    beforeEach(function () {
        MockModel::reset();
    });

    it('extends Repository class', function () {
        $repo = TestBaseRepository::create();
        
        expect($repo)->toBeInstanceOf(Repository::class)
            ->and($repo)->toBeInstanceOf(BaseRepository::class);
    });

    it('adds amount method for pagination', function () {
        $repo = TestBaseRepository::create();
        $result = $repo->amount(10, 20);
        
        expect($result)->toBe($repo); // Fluent interface
        
        $repo->get();
        
        expect(MockModel::$lastQuery)->toContain('LIMIT ?')
            ->and(MockModel::$lastQuery)->toContain('OFFSET ?')
            ->and(MockModel::$lastParams)->toBe([10, 20]);
    });

    it('adds amount with default offset', function () {
        $repo = TestBaseRepository::create();
        $repo->amount(15)->get();
        
        expect(MockModel::$lastQuery)->toContain('LIMIT ?')
            ->and(MockModel::$lastQuery)->not->toContain('OFFSET')
            ->and(MockModel::$lastParams)->toBe([15]);
    });

    it('adds amount with default values', function () {
        $repo = TestBaseRepository::create();
        $repo->amount()->get();
        
        expect(MockModel::$lastQuery)->toContain('LIMIT ?')
            ->and(MockModel::$lastQuery)->not->toContain('OFFSET')
            ->and(MockModel::$lastParams)->toBe([30]);
    });

    it('adds orderBy method', function () {
        $repo = TestBaseRepository::create();
        $result = $repo->orderBy('name', 'ASC');
        
        expect($result)->toBe($repo); // Fluent interface
        
        $repo->get();
        
        expect(MockModel::$lastQuery)->toContain('ORDER BY `users`.`name` ASC');
    });

    it('adds orderBy with default direction', function () {
        $repo = TestBaseRepository::create();
        $repo->orderBy('created_at')->get();
        
        expect(MockModel::$lastQuery)->toContain('ORDER BY `users`.`created_at` ASC');
    });

    it('chains amount and orderBy', function () {
        $repo = TestBaseRepository::create();
        $repo->amount(10)->orderBy('name', 'DESC')->get();
        
        expect(MockModel::$lastQuery)->toContain('ORDER BY `users`.`name` DESC')
            ->and(MockModel::$lastQuery)->toContain('LIMIT ?')
            ->and(MockModel::$lastParams)->toBe([10]);
    });

    it('supports complex query chains', function () {
        $collection = new CriteriaCollection();
        $collection->addCriteria(new Equals('status', 'active'));
        
        $repo = new TestBaseRepository($collection);
        $repo->orderBy('created_at', 'DESC')
             ->amount(5, 10)
             ->get();
        
        expect(MockModel::$lastQuery)->toContain('WHERE `users`.`status` = ?')
            ->and(MockModel::$lastQuery)->toContain('ORDER BY `users`.`created_at` DESC')
            ->and(MockModel::$lastQuery)->toContain('LIMIT ?')
            ->and(MockModel::$lastQuery)->toContain('OFFSET ?')
            ->and(MockModel::$lastParams)->toBe(['active', 5, 10]);
    });

    it('works with first method', function () {
        MockModel::$queryRowResult = ['id' => 1, 'name' => 'John'];
        
        $repo = TestBaseRepository::create();
        $repo->orderBy('name')->first();
        
        expect(MockModel::$lastQuery)->toContain('ORDER BY `users`.`name` ASC')
            ->and(MockModel::$lastQuery)->toContain('LIMIT ?');
    });

});

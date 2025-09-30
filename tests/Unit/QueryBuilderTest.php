<?php

use Tnt\Dbi\QueryBuilder;
use Tnt\Dbi\Raw;
use Tnt\Dbi\TableBuilder;

describe('QueryBuilder - Basic SELECT', function () {
    
    it('builds simple SELECT query', function () {
        $qb = new QueryBuilder();
        $qb->table('users');
        $qb->select('name');
        $qb->build();
        
        expect($qb->getQuery())->toBe('SELECT `users`.`name` FROM `users`');
    });

    it('builds SELECT with multiple columns', function () {
        $qb = new QueryBuilder();
        $qb->table('users');
        $qb->select('id');
        $qb->select('name');
        $qb->select('email');
        $qb->build();
        
        expect($qb->getQuery())->toBe('SELECT `users`.`id`, `users`.`name`, `users`.`email` FROM `users`');
    });

    it('builds SELECT * query', function () {
        $qb = new QueryBuilder();
        $qb->table('users');
        $qb->selectAll();
        $qb->build();
        
        expect($qb->getQuery())->toBe('SELECT `users`.* FROM `users`');
    });

    it('builds SELECT * with table parameter', function () {
        $qb = new QueryBuilder();
        $qb->table('users');
        $qb->selectAll('posts');
        $qb->build();
        
        expect($qb->getQuery())->toBe('SELECT `posts`.* FROM `users`');
    });

    it('builds SELECT with alias', function () {
        $qb = new QueryBuilder();
        $qb->table('users');
        $qb->selectAs('COUNT(*)', 'total');
        $qb->build();
        
        expect($qb->getQuery())->toBe('SELECT `users`.`COUNT(*)` AS total FROM `users`');
    });

    it('builds SELECT with Raw statement', function () {
        $qb = new QueryBuilder();
        $qb->table('users');
        $qb->select(new Raw('COUNT(*)'));
        $qb->build();
        
        expect($qb->getQuery())->toBe('SELECT COUNT(*) FROM `users`');
    });

});

describe('QueryBuilder - WHERE Clauses', function () {
    
    it('builds WHERE with single condition', function () {
        $qb = new QueryBuilder();
        $qb->table('users');
        $qb->selectAll();
        $qb->where('status', '=', 'active');
        $qb->build();
        
        expect($qb->getQuery())->toBe('SELECT `users`.* FROM `users` WHERE `users`.`status` = ?')
            ->and($qb->getParameters())->toBe(['active']);
    });

    it('builds WHERE with multiple AND conditions', function () {
        $qb = new QueryBuilder();
        $qb->table('users');
        $qb->selectAll();
        $qb->where('status', '=', 'active');
        $qb->where('age', '>', 18);
        $qb->build();
        
        expect($qb->getQuery())->toBe('SELECT `users`.* FROM `users` WHERE `users`.`status` = ? AND `users`.`age` > ?')
            ->and($qb->getParameters())->toBe(['active', 18]);
    });

    it('builds WHERE with OR condition', function () {
        $qb = new QueryBuilder();
        $qb->table('users');
        $qb->selectAll();
        $qb->where('role', '=', 'admin');
        $qb->where('role', '=', 'moderator', 'OR');
        $qb->build();
        
        expect($qb->getQuery())->toBe('SELECT `users`.* FROM `users` WHERE `users`.`role` = ? OR `users`.`role` = ?')
            ->and($qb->getParameters())->toBe(['admin', 'moderator']);
    });

    it('builds WHERE with grouped conditions', function () {
        $qb = new QueryBuilder();
        $qb->table('users');
        $qb->selectAll();
        $qb->where('status', '=', 'active');
        $qb->whereGroup(function($qb) {
            $qb->where('role', '=', 'admin');
            $qb->where('role', '=', 'moderator', 'OR');
        });
        $qb->build();
        
        expect($qb->getQuery())->toContain('WHERE `users`.`status` = ? AND  ( `users`.`role` = ? OR `users`.`role` = ? )');
    });

    it('builds WHERE with OR grouped conditions', function () {
        $qb = new QueryBuilder();
        $qb->table('users');
        $qb->selectAll();
        $qb->where('status', '=', 'active');
        $qb->whereGroup(function($qb) {
            $qb->where('age', '<', 18);
            $qb->where('age', '>', 65);
        }, 'OR');
        $qb->build();
        
        expect($qb->getQuery())->toContain('WHERE `users`.`status` = ? OR  ( `users`.`age` < ? AND `users`.`age` > ? )');
    });

    it('handles different operators', function () {
        $qb = new QueryBuilder();
        $qb->table('products');
        $qb->selectAll();
        $qb->where('price', '>=', 100);
        $qb->where('stock', '<=', 10);
        $qb->where('status', '!=', 'deleted');
        $qb->build();
        
        expect($qb->getQuery())->toContain('WHERE `products`.`price` >= ?')
            ->and($qb->getQuery())->toContain('AND `products`.`stock` <= ?')
            ->and($qb->getQuery())->toContain('AND `products`.`status` != ?')
            ->and($qb->getParameters())->toBe([100, 10, 'deleted']);
    });

});

describe('QueryBuilder - JOINS', function () {
    
    it('builds INNER JOIN', function () {
        $qb = new QueryBuilder();
        $qb->table('posts');
        $qb->selectAll();
        $qb->innerJoin('users')->on('posts.user_id', '=', 'users.id');
        $qb->build();
        
        expect($qb->getQuery())->toContain('SELECT `posts`.* FROM `posts` INNER JOIN `users` ON `posts`.`user_id` = `users`.`id`');
    });

    it('builds LEFT JOIN', function () {
        $qb = new QueryBuilder();
        $qb->table('posts');
        $qb->selectAll();
        $qb->leftJoin('categories')->on('posts.category_id', '=', 'categories.id');
        $qb->build();
        
        expect($qb->getQuery())->toContain('LEFT JOIN `categories` ON `posts`.`category_id` = `categories`.`id`');
    });

    it('builds RIGHT JOIN', function () {
        $qb = new QueryBuilder();
        $qb->table('posts');
        $qb->selectAll();
        $qb->rightJoin('users')->on('posts.user_id', '=', 'users.id');
        $qb->build();
        
        expect($qb->getQuery())->toContain('RIGHT JOIN `users` ON `posts`.`user_id` = `users`.`id`');
    });

    it('builds multiple JOINs', function () {
        $qb = new QueryBuilder();
        $qb->table('posts');
        $qb->selectAll();
        $qb->leftJoin('users')->on('posts.user_id', '=', 'users.id');
        $qb->leftJoin('categories')->on('posts.category_id', '=', 'categories.id');
        $qb->build();
        
        expect($qb->getQuery())->toContain('LEFT JOIN `users` ON `posts`.`user_id` = `users`.`id`')
            ->and($qb->getQuery())->toContain('LEFT JOIN `categories` ON `posts`.`category_id` = `categories`.`id`');
    });

    it('reuses existing join when called multiple times', function () {
        $qb = new QueryBuilder();
        $qb->table('posts');
        $qb->selectAll();
        $join1 = $qb->leftJoin('users');
        $join2 = $qb->leftJoin('users');
        
        expect($join1)->toBe($join2);
    });

});

describe('QueryBuilder - ORDER BY', function () {
    
    it('builds ORDER BY ASC', function () {
        $qb = new QueryBuilder();
        $qb->table('users');
        $qb->selectAll();
        $qb->orderBy('name', 'ASC');
        $qb->build();
        
        expect($qb->getQuery())->toBe('SELECT `users`.* FROM `users` ORDER BY `users`.`name` ASC');
    });

    it('builds ORDER BY DESC', function () {
        $qb = new QueryBuilder();
        $qb->table('posts');
        $qb->selectAll();
        $qb->orderBy('created_at', 'DESC');
        $qb->build();
        
        expect($qb->getQuery())->toBe('SELECT `posts`.* FROM `posts` ORDER BY `posts`.`created_at` DESC');
    });

    it('builds multiple ORDER BY clauses', function () {
        $qb = new QueryBuilder();
        $qb->table('users');
        $qb->selectAll();
        $qb->orderBy('status', 'ASC');
        $qb->orderBy('name', 'ASC');
        $qb->build();
        
        expect($qb->getQuery())->toBe('SELECT `users`.* FROM `users` ORDER BY `users`.`status` ASC, `users`.`name` ASC');
    });

    it('replaces duplicate ORDER BY on same column', function () {
        $qb = new QueryBuilder();
        $qb->table('users');
        $qb->selectAll();
        $qb->orderBy('name', 'ASC');
        $qb->orderBy('name', 'DESC');
        $qb->build();
        
        expect($qb->getQuery())->toBe('SELECT `users`.* FROM `users` ORDER BY `users`.`name` DESC');
    });

});

describe('QueryBuilder - GROUP BY', function () {
    
    it('builds GROUP BY', function () {
        $qb = new QueryBuilder();
        $qb->table('orders');
        $qb->select('user_id');
        $qb->selectAs('COUNT(*)', 'total');
        $qb->groupBy('user_id');
        $qb->build();
        
        expect($qb->getQuery())->toContain('GROUP BY `orders`.`user_id`');
    });

    it('builds multiple GROUP BY columns', function () {
        $qb = new QueryBuilder();
        $qb->table('sales');
        $qb->selectAll();
        $qb->groupBy('year');
        $qb->groupBy('month');
        $qb->build();
        
        expect($qb->getQuery())->toContain('GROUP BY `sales`.`year`, `sales`.`month`');
    });

});

describe('QueryBuilder - HAVING', function () {
    
    it('builds HAVING clause', function () {
        $qb = new QueryBuilder();
        $qb->table('orders');
        $qb->select('user_id');
        $qb->selectAs('COUNT(*)', 'total');
        $qb->groupBy('user_id');
        $qb->having('COUNT(*)', '>', 5);
        $qb->build();
        
        expect($qb->getQuery())->toContain('HAVING `orders`.`COUNT(*)` > ?')
            ->and($qb->getParameters())->toBe([5]);
    });

    it('builds multiple HAVING conditions', function () {
        $qb = new QueryBuilder();
        $qb->table('orders');
        $qb->selectAll();
        $qb->groupBy('user_id');
        $qb->having('COUNT(*)', '>', 5);
        $qb->having('SUM(total)', '>', 1000);
        $qb->build();
        
        expect($qb->getQuery())->toContain('HAVING `orders`.`COUNT(*)` > ? AND `orders`.`SUM(total)` > ?')
            ->and($qb->getParameters())->toBe([5, 1000]);
    });

    it('builds HAVING with grouped conditions', function () {
        $qb = new QueryBuilder();
        $qb->table('orders');
        $qb->selectAll();
        $qb->groupBy('user_id');
        $qb->havingGroup(function($qb) {
            $qb->having('COUNT(*)', '>', 5);
            $qb->having('COUNT(*)', '<', 10, 'OR');
        });
        $qb->build();
        
        expect($qb->getQuery())->toContain('HAVING  ( `orders`.`COUNT(*)` > ? OR `orders`.`COUNT(*)` < ? )');
    });

});

describe('QueryBuilder - LIMIT and OFFSET', function () {
    
    it('builds LIMIT', function () {
        $qb = new QueryBuilder();
        $qb->table('users');
        $qb->selectAll();
        $qb->limit(10);
        $qb->build();
        
        expect($qb->getQuery())->toBe('SELECT `users`.* FROM `users` LIMIT ?')
            ->and($qb->getParameters())->toBe([10]);
    });

    it('builds LIMIT with OFFSET', function () {
        $qb = new QueryBuilder();
        $qb->table('users');
        $qb->selectAll();
        $qb->limit(10);
        $qb->offset(20);
        $qb->build();
        
        expect($qb->getQuery())->toBe('SELECT `users`.* FROM `users` LIMIT ? OFFSET ?')
            ->and($qb->getParameters())->toBe([10, 20]);
    });

    it('ignores OFFSET without LIMIT', function () {
        $qb = new QueryBuilder();
        $qb->table('users');
        $qb->selectAll();
        $qb->offset(20);
        $qb->build();
        
        expect($qb->getQuery())->toBe('SELECT `users`.* FROM `users`');
    });

});

describe('QueryBuilder - Complex Queries', function () {
    
    it('builds complex SELECT with all clauses', function () {
        $qb = new QueryBuilder();
        $qb->table('posts');
        $qb->select('posts.id');
        $qb->select('posts.title');
        $qb->select('users.name');
        $qb->leftJoin('users')->on('posts.user_id', '=', 'users.id');
        $qb->where('posts.status', '=', 'published');
        $qb->where('posts.views', '>', 100);
        $qb->orderBy('posts.created_at', 'DESC');
        $qb->limit(10);
        $qb->build();
        
        $query = $qb->getQuery();
        expect($query)->toContain('SELECT `posts`.`id`, `posts`.`title`, `users`.`name` FROM `posts`')
            ->and($query)->toContain('LEFT JOIN `users` ON `posts`.`user_id` = `users`.`id`')
            ->and($query)->toContain('WHERE `posts`.`status` = ? AND `posts`.`views` > ?')
            ->and($query)->toContain('ORDER BY `posts`.`created_at` DESC')
            ->and($query)->toContain('LIMIT ?')
            ->and($qb->getParameters())->toBe(['published', 100, 10]);
    });

    it('builds query with GROUP BY and HAVING', function () {
        $qb = new QueryBuilder();
        $qb->table('orders');
        $qb->select('user_id');
        $qb->selectAs('COUNT(*)', 'order_count');
        $qb->selectAs('SUM(total)', 'total_spent');
        $qb->where('status', '=', 'completed');
        $qb->groupBy('user_id');
        $qb->having('COUNT(*)', '>', 5);
        $qb->orderBy('total_spent', 'DESC');
        $qb->limit(20);
        $qb->build();
        
        $query = $qb->getQuery();
        expect($query)->toContain('WHERE `orders`.`status` = ?')
            ->and($query)->toContain('GROUP BY `orders`.`user_id`')
            ->and($query)->toContain('HAVING `orders`.`COUNT(*)` > ?')
            ->and($query)->toContain('ORDER BY `orders`.`total_spent` DESC')
            ->and($query)->toContain('LIMIT ?')
            ->and($qb->getParameters())->toBe(['completed', 5, 20]);
    });

});

describe('QueryBuilder - CREATE TABLE', function () {
    
    it('builds CREATE TABLE query', function () {
        $qb = new QueryBuilder();
        $qb->table('users');
        $qb->create(function($table) {
            $table->addColumn('id', 'int')->primaryKey();
            $table->addColumn('name', 'varchar')->length(255)->notNull();
            $table->addColumn('email', 'varchar')->length(255)->notNull();
        });
        $qb->build();
        
        $query = $qb->getQuery();
        expect($query)->toContain('CREATE TABLE `users`')
            ->and($query)->toContain('`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY')
            ->and($query)->toContain('`name` VARCHAR(255) NOT NULL')
            ->and($query)->toContain('`email` VARCHAR(255) NOT NULL')
            ->and($query)->toContain("COLLATE 'utf8_unicode_ci'");
    });

    it('returns fluent interface from create', function () {
        $qb = new QueryBuilder();
        $qb->table('users');
        $result = $qb->create(function($table) {
            $table->addColumn('id', 'int')->primaryKey();
        });
        
        expect($result)->toBe($qb);
    });

});

describe('QueryBuilder - ALTER TABLE', function () {
    
    it('builds ALTER TABLE query', function () {
        $qb = new QueryBuilder();
        $qb->table('users');
        $qb->alter(function($table) {
            $table->addColumn('phone', 'varchar')->length(20);
            $table->changeColumn('name')->type('varchar')->length(300);
        });
        $qb->build();
        
        $query = $qb->getQuery();
        expect($query)->toContain('ALTER TABLE `users`')
            ->and($query)->toContain('ADD `phone` VARCHAR(20) NOT NULL')
            ->and($query)->toContain('CHANGE `name` `name` VARCHAR(300) NOT NULL');
    });

    it('returns fluent interface from alter', function () {
        $qb = new QueryBuilder();
        $qb->table('users');
        $result = $qb->alter(function($table) {
            $table->addColumn('phone', 'varchar')->length(20);
        });
        
        expect($result)->toBe($qb);
    });

});

describe('QueryBuilder - DROP TABLE', function () {
    
    it('builds DROP TABLE query', function () {
        $qb = new QueryBuilder();
        $qb->table('old_table');
        $qb->drop();
        $qb->build();
        
        expect($qb->getQuery())->toBe('DROP TABLE `old_table`');
    });

    it('returns fluent interface from drop', function () {
        $qb = new QueryBuilder();
        $qb->table('users');
        $result = $qb->drop();
        
        expect($result)->toBe($qb);
    });

});

describe('QueryBuilder - Fluent Interface', function () {
    
    it('chains all methods fluently', function () {
        $qb = new QueryBuilder();
        $result = $qb->table('users')
            ->selectAll()
            ->where('status', '=', 'active')
            ->orderBy('name', 'ASC')
            ->limit(10);
        
        expect($result)->toBe($qb);
    });

    it('allows method chaining for complex queries', function () {
        $qb = new QueryBuilder();
        $qb->table('posts')
            ->select('id')
            ->select('title');
        $qb->leftJoin('users')->on('posts.user_id', '=', 'users.id');
        $qb->where('status', '=', 'published')
            ->orderBy('created_at', 'DESC')
            ->limit(5)
            ->build();
        
        expect($qb->getQuery())->toContain('SELECT')
            ->and($qb->getQuery())->toContain('LEFT JOIN')
            ->and($qb->getQuery())->toContain('WHERE')
            ->and($qb->getQuery())->toContain('ORDER BY')
            ->and($qb->getQuery())->toContain('LIMIT');
    });

});
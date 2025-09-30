<?php

use Tnt\Dbi\JoinBuilder;

describe('JoinBuilder', function () {
    
    it('creates LEFT JOIN', function () {
        $join = new JoinBuilder();
        $join->table('users');
        $join->setType('left');
        $join->on('posts.user_id', '=', 'users.id');
        $join->build();
        
        $query = $join->getQuery();
        
        expect($query)->toContain('LEFT JOIN `users`')
            ->and($query)->toContain('ON `posts`.`user_id` = `users`.`id`');
    });

    it('creates RIGHT JOIN', function () {
        $join = new JoinBuilder();
        $join->table('categories');
        $join->setType('right');
        $join->on('posts.category_id', '=', 'categories.id');
        $join->build();
        
        $query = $join->getQuery();
        
        expect($query)->toContain('RIGHT JOIN `categories`')
            ->and($query)->toContain('ON `posts`.`category_id` = `categories`.`id`');
    });

    it('creates INNER JOIN', function () {
        $join = new JoinBuilder();
        $join->table('orders');
        $join->setType('inner');
        $join->on('order_items.order_id', '=', 'orders.id');
        $join->build();
        
        $query = $join->getQuery();
        
        expect($query)->toContain('INNER JOIN `orders`')
            ->and($query)->toContain('ON `order_items`.`order_id` = `orders`.`id`');
    });

    it('handles table alias', function () {
        $join = new JoinBuilder();
        $join->table('users');
        $join->as('u');
        $join->setType('left');
        $join->on('posts.user_id', '=', 'u.id');
        $join->build();
        
        $query = $join->getQuery();
        
        expect($query)->toContain('LEFT JOIN `users` AS `u`')
            ->and($query)->toContain('ON `posts`.`user_id` = `u`.`id`');
    });

    it('handles multiple ON conditions', function () {
        $join = new JoinBuilder();
        $join->table('users');
        $join->setType('left');
        $join->on('posts.user_id', '=', 'users.id');
        $join->on('posts.status', '=', 'users.status');
        $join->build();
        
        $query = $join->getQuery();
        
        expect($query)->toContain('LEFT JOIN `users`')
            ->and($query)->toContain('ON `posts`.`user_id` = `users`.`id` AND `posts`.`status` = `users`.`status`');
    });

    it('throws exception for invalid join type', function () {
        $join = new JoinBuilder();
        
        expect(fn() => $join->setType('invalid'))
            ->toThrow(\InvalidArgumentException::class, 'Unknown join type');
    });

    it('returns fluent interface from on method', function () {
        $join = new JoinBuilder();
        $join->table('users');
        $join->setType('left');
        $result = $join->on('posts.user_id', '=', 'users.id');
        
        expect($result)->toBeInstanceOf(Tnt\Dbi\BuildHandler::class);
    });

    it('returns fluent interface from as method', function () {
        $join = new JoinBuilder();
        $join->table('users');
        $result = $join->as('u');
        
        expect($result)->toBe($join);
    });

    it('handles join without ON conditions', function () {
        $join = new JoinBuilder();
        $join->table('settings');
        $join->setType('inner');
        $join->build();
        
        $query = $join->getQuery();
        
        expect($query)->toBe('INNER JOIN `settings`');
    });

    it('handles complex table names', function () {
        $join = new JoinBuilder();
        $join->table('user_profiles');
        $join->setType('left');
        $join->on('users.id', '=', 'user_profiles.user_id');
        $join->build();
        
        $query = $join->getQuery();
        
        expect($query)->toContain('LEFT JOIN `user_profiles`')
            ->and($query)->toContain('ON `users`.`id` = `user_profiles`.`user_id`');
    });

});